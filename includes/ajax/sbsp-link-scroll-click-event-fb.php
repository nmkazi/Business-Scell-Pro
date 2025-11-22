<?php

$class_sbsp_fb_capi = SBSP_PLUGIN_DIR . 'includes/fb/class-sbsp-fb-capi.php';
$class_sbsp_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-functions.php';
$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';

require_once $class_sbsp_fb_capi;
require_once $class_sbsp_functions;
require_once $class_sbsp_license;

add_action('wp_footer', 'sbsp_link_scroll_click_event_fb_script');
function sbsp_link_scroll_click_event_fb_script() {

    if(!SBSP_License::license_check()){
        return;
    }

    $link_events = get_option('sbsp_fb_link_events', []);
    $scroll_events = get_option('sbsp_fb_scroll_events', []);
    $click_events = get_option('sbsp_fb_click_events', []);
    $time_events = get_option('sbsp_fb_time_events', []);

    $sbsp_link_scroll_click_event_track_fb_nonce = wp_create_nonce('sbsp_link_scroll_click_event_track_fb_nonce');

    ?>
    <!-- Include jQuery from CDN if not loaded -->
    <script>
    if (typeof jQuery === 'undefined') {
        var jq = document.createElement('script');
        jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js";
        jq.onload = initSBSPLinkScrollClickScript;
        document.head.appendChild(jq);
    } else {
        initSBSPLinkScrollClickScript();
    }

    function initSBSPLinkScrollClickScript() {
        jQuery(function($) {
            const sbspLinkEvents = <?= json_encode($link_events) ?>;
            const sbspScrollEvents = <?= json_encode($scroll_events) ?>;
            const sbspClickEvents = <?= json_encode($click_events) ?>;
            const sbspTimeEvents = <?= json_encode($time_events) ?>;

            function sbspSendEvent(eventName) {
                $.ajax({
                    url: "<?= admin_url('admin-ajax.php') ?>",
                    type: 'POST',
                    data: {
                        action: 'sbsp_link_scroll_click_event_track_fb',
                        event_name: eventName,
                        _wpnonce: "<?= $sbsp_link_scroll_click_event_track_fb_nonce ?>"
                    },
                    success: function(response) {
                        // console.log(response);
                    }
                });
            }


            // Link events
            sbspLinkEvents.forEach(e => {
                if (e.selector && e.event_name && $(e.selector).length) {
                    sbspSendEvent(e.event_name);
                }
            });

            // Click events
            sbspClickEvents.forEach(e => {
                if (e.selector && e.event_name) {
                    $(e.selector).one('click', function() {
                        sbspSendEvent(e.event_name);
                    });
                }
            });

            // Scroll events with IntersectionObserver
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
                        const $el = $(entry.target);
                        const eventName = $el.data('sbsp-scroll-event-fb');
                        if (eventName && !$el.data('sent')) {
                            sbspSendEvent(eventName);
                            $el.data('sent', true);
                        }
                    }
                });
            }, { threshold: [0.5] });

            sbspScrollEvents.forEach(e => {
                if (e.selector && e.event_name) {
                    $(e.selector).each(function() {
                        const el = this;
                        $(el).attr('data-sbsp-scroll-event-fb', e.event_name);
                        observer.observe(el);
                    });
                }
            });

            sbspTimeEvents.forEach(e => {
                if (e.selector && e.event_name && e.seconds) {
                    const $target = $(e.selector);
                    if ($target.length) {
                        setTimeout(() => {
                            if ($(e.selector).length) {
                                sbspSendEvent(e.event_name);
                            }
                        }, e.seconds * 1000);
                    }
                }
            });
        });
    }
    </script>
    <?php
}

// Handle AJAX
add_action('wp_ajax_sbsp_link_scroll_click_event_track_fb', 'sbsp_link_scroll_click_event_track_fb');
add_action('wp_ajax_nopriv_sbsp_link_scroll_click_event_track_fb', 'sbsp_link_scroll_click_event_track_fb');
function sbsp_link_scroll_click_event_track_fb() {

    if(!SBSP_License::license_check()){
        wp_send_json_error('License check failed.');
        wp_die();
    }

    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'sbsp_link_scroll_click_event_track_fb_nonce')) {
        // wp_send_json_error('Security check failed.');
        // wp_die();
    }

    $event_name = sanitize_text_field($_POST['event_name'] ?? '');

    $user_data = [];
    $custom_data = [];

    if (is_user_logged_in()) {
        $user_data = SBSP_Functions::get_hashed_user_data();
    }

    $user_data = SBSP_Functions::get_http_client_data($user_data);

    if ($event_name) {
        $return = SBSP_FB_CAPI::send_event($event_name, $custom_data, $user_data);
    }
    wp_send_json_success($event_name . " Event sent to Facebook.");
    wp_die();
}
