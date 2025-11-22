<?php

if (!defined('ABSPATH')) {
    exit;
}

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$sbsp_container_start = SBSP_PLUGIN_DIR . 'includes/templates/partials/sbsp-container-start.php';
$sbsp_navbar = SBSP_PLUGIN_DIR . 'includes/templates/partials/sbsp-navbar.php';
$sbsp_container_end = SBSP_PLUGIN_DIR . 'includes/templates/partials/sbsp-container-end.php';

require_once $class_sbsp_license;

class SBSP_FB_Settings {

    public static function render_settings(){
        global $sbsp_container_start, $sbsp_navbar, $sbsp_container_end;
        require_once $sbsp_container_start;
        
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        $base_url = admin_url('admin.php?page=sbsp-fb-settings');

        require_once $sbsp_navbar;

        ?>

        <nav class="sbsp-tabs">
            <a href="<?php echo $base_url . '&tab=general'; ?>" class="<?php echo $current_tab == 'general' ? 'active' : ''; ?>">General</a>
            <a href="<?php echo $base_url . '&tab=custom-events'; ?>" class="<?php echo $current_tab == 'custom-events' ? 'active' : ''; ?>">Custom Events</a>
            <a href="<?php echo $base_url . '&tab=developer-section'; ?>" class="<?php echo $current_tab == 'developer-section' ? 'active' : ''; ?>">Developer Section</a>
        </nav>

        <?php

        $tabs = [
            'general' => 'render_fb_general_settings',
            'custom-events' => 'render_fb_custom_events_settings',
            'developer-section' => 'render_fb_developer_section_settings',
        ];

        $method = $tabs[$current_tab] ?? 'render_fb_general_settings';

        if (method_exists(__CLASS__, $method)) {
            self::$method();
        }
        
        require_once $sbsp_container_end;
    }

    public static function submit_fb_general_settings() {

        if (SBSP_License::license_check()) {

            $fb_pixel_capi_groups = array();
        
            if(isset($_POST['sbsp_fb_pixel_capi_groups']) && is_array($_POST['sbsp_fb_pixel_capi_groups'])){
                foreach ($_POST['sbsp_fb_pixel_capi_groups'] as $fb_pixel_capi_group) {
                    $pixel_id = isset($fb_pixel_capi_group['pixel_id']) ? sanitize_text_field($fb_pixel_capi_group['pixel_id']) : '';
                    $access_token = isset($fb_pixel_capi_group['access_token']) ? sanitize_textarea_field($fb_pixel_capi_group['access_token']) : '';
                    $test_event_code = isset($fb_pixel_capi_group['test_event_code']) ? sanitize_text_field($fb_pixel_capi_group['test_event_code']) : '';
            
                    if (!empty($pixel_id) && !empty($access_token)) {
                        $fb_pixel_capi_groups[] = array(
                            'pixel_id' => $pixel_id,
                            'access_token' => $access_token,
                            'test_event_code' => $test_event_code,
                        );
                    }
                }
            }
            update_option('sbsp_fb_pixel_capi_groups', $fb_pixel_capi_groups);
            update_option('sbsp_enable_order_flow_tracking_fb', isset($_POST['sbsp_enable_order_flow_tracking_fb']) ? 1 : 0);
            update_option('sbsp_enable_test_event_code_fb', isset($_POST['sbsp_enable_test_event_code_fb']) ? 1 : 0);
            update_option('sbsp_enable_fb_capi', isset($_POST['sbsp_enable_fb_capi']) ? 1 : 0);
            update_option('sbsp_send_purchase_data_immediately_fb', isset($_POST['sbsp_send_purchase_data_immediately_fb']) ? 1 : 0);
            update_option('sbsp_check_customer_courier_history_send_purchase_data_immediately_fb', isset($_POST['sbsp_check_customer_courier_history_send_purchase_data_immediately_fb']) ? 1 : 0);
            update_option('sbsp_enable_AddToCart_funnel_checkout_fb', isset($_POST['sbsp_enable_AddToCart_funnel_checkout_fb']) ? 1 : 0);
            update_option('sbsp_enable_InitiateCheckout_funnel_checkout_fb', isset($_POST['sbsp_enable_InitiateCheckout_funnel_checkout_fb']) ? 1 : 0);


            $customer_history_success_percent_send_purchase_data_immediately_fb = isset($_POST['sbsp_customer_history_success_percent_send_purchase_data_immediately_fb']) ? $_POST['sbsp_customer_history_success_percent_send_purchase_data_immediately_fb'] : 0;
            if($customer_history_success_percent_send_purchase_data_immediately_fb < 0 ){
                $customer_history_success_percent_send_purchase_data_immediately_fb = 0;
            }elseif($customer_history_success_percent_send_purchase_data_immediately_fb > 100){
                $customer_history_success_percent_send_purchase_data_immediately_fb = 100;
            }
            update_option('sbsp_customer_history_success_percent_send_purchase_data_immediately_fb', $customer_history_success_percent_send_purchase_data_immediately_fb);

        }
        
    }

    public static function render_fb_general_settings() {
        if (isset($_POST['sbsp_save_fb_general_settings'])) {
            if (check_admin_referer('sbsp_fb_general_settings_form_nonce')) {
                self::submit_fb_general_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }
        
        $enable_order_flow_tracking_fb = get_option('sbsp_enable_order_flow_tracking_fb');
        $enable_test_event_code_fb = get_option('sbsp_enable_test_event_code_fb');
        $enable_fb_capi = get_option('sbsp_enable_fb_capi');
        $send_purchase_data_immediately_fb = get_option('sbsp_send_purchase_data_immediately_fb');
        $customer_history_success_percent_send_purchase_data_immediately_fb = get_option('sbsp_customer_history_success_percent_send_purchase_data_immediately_fb');
        $check_customer_courier_history_send_purchase_data_immediately_fb = get_option('sbsp_check_customer_courier_history_send_purchase_data_immediately_fb');
        $enable_AddToCart_funnel_checkout_fb = get_option('sbsp_enable_AddToCart_funnel_checkout_fb');
        $enable_InitiateCheckout_funnel_checkout_fb = get_option('sbsp_enable_InitiateCheckout_funnel_checkout_fb');

        ?>

        <div class="wrap">

            <h1>Facebook Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_fb_general_settings_form_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">Enable FB CAPI</th>
                        <td>
                            <input type="checkbox" name="sbsp_enable_fb_capi" value="1" <?= $enable_fb_capi ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Send Purchase Data Immediately to Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_send_purchase_data_immediately_fb" value="1" <?= $send_purchase_data_immediately_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Check Customer History before Send Purchase Data Immediately to Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_check_customer_courier_history_send_purchase_data_immediately_fb" value="1" <?= $check_customer_courier_history_send_purchase_data_immediately_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Customer History Success Percent for Send Purchase Data Immediately to Facebook</th>
                        <td>
                            <input type="number" name="sbsp_customer_history_success_percent_send_purchase_data_immediately_fb" value="<?= $customer_history_success_percent_send_purchase_data_immediately_fb ?>" min="0" max="100">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Order Flow Tracking for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_enable_order_flow_tracking_fb" value="1" <?= $enable_order_flow_tracking_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Test Event Code for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_enable_test_event_code_fb" value="1" <?= $enable_test_event_code_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable AddToCart Event on Funnel Checkout Page for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_enable_AddToCart_funnel_checkout_fb" value="1" <?= $enable_AddToCart_funnel_checkout_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable InitiateCheckout Event on Funnel Checkout Page for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_enable_InitiateCheckout_funnel_checkout_fb" value="1" <?= $enable_InitiateCheckout_funnel_checkout_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Facebook Pixel CAPI Groups</th>
                        <td>
                            <div id="pixel-capi-groups">
                                <?php
                                $fb_pixel_capi_groups = get_option('sbsp_fb_pixel_capi_groups');
                                if (!empty($fb_pixel_capi_groups) && is_array($fb_pixel_capi_groups)) {
                                    foreach ($fb_pixel_capi_groups as $index => $fb_pixel_capi_group) {
                                        ?>
                                        <div class="pixel-capi-group">
                                            <input type="text" name="sbsp_fb_pixel_capi_groups[<?= $index ?>][pixel_id]" placeholder="Pixel ID" value="<?= esc_attr($fb_pixel_capi_group['pixel_id']) ?>">
                                            <textarea name="sbsp_fb_pixel_capi_groups[<?= $index ?>][access_token]" placeholder="Access Token"><?= esc_textarea($fb_pixel_capi_group['access_token']) ?></textarea>
                                            <input type="text" name="sbsp_fb_pixel_capi_groups[<?= $index ?>][test_event_code]" placeholder="Test Event Code" value="<?= esc_attr($fb_pixel_capi_group['test_event_code']) ?>">
                                            <button type="button" class="remove-group">Delete Group</button>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <div class="pixel-capi-group">
                                        <input type="text" name="sbsp_fb_pixel_capi_groups[0][pixel_id]" placeholder="Pixel ID" value="">
                                        <textarea name="sbsp_fb_pixel_capi_groups[0][access_token]" placeholder="Access Token"></textarea>
                                        <input type="text" name="sbsp_fb_pixel_capi_groups[0][test_event_code]" placeholder="Test Event Code" value="">
                                        <button type="button" class="remove-group">Delete Group</button>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <button type="button" class="button button-primary" id="add-new-group">Add New Group</button>
                        </td>
                    </tr>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_fb_general_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <script>
            jQuery(document).ready(function($) {
                let groupIndex = <?php echo !empty($fb_pixel_capi_groups) ? count($fb_pixel_capi_groups) : 0; ?>;

                $('#add-new-group').on('click', function() {
                    const groupHtml = `
                        <div class="pixel-capi-group">
                            <input type="text" name="sbsp_fb_pixel_capi_groups[${groupIndex}][pixel_id]" placeholder="Pixel ID" value="">
                            <textarea name="sbsp_fb_pixel_capi_groups[${groupIndex}][access_token]" placeholder="Access Token"></textarea>
                            <input type="text" name="sbsp_fb_pixel_capi_groups[${groupIndex}][test_event_code]" placeholder="Test Event Code" value="">
                            <button type="button" class="remove-group">Delete Group</button>
                        </div>
                    `;
                    $('#pixel-capi-groups').append(groupHtml);
                });

                $(document).on('click', '.remove-group', function() {
                    $(this).closest('.pixel-capi-group').remove();
                });

            });
        </script>

        <style>
            .d-none {
                display: none;
            }
            #pixel-capi-groups {
                margin-top: 15px;
            }

            .pixel-capi-group {
                margin-bottom: 20px;
                padding: 15px;
                border: 1px solid #ccd0d4;
                border-radius: 8px;
                background-color: #f9f9f9;
            }

            .pixel-capi-group input[type="text"],
            .pixel-capi-group textarea {
                width: 100%;
                padding: 8px 10px;
                margin-bottom: 10px;
                border: 1px solid #ccd0d4;
                border-radius: 5px;
                background: #fff;
            }

            #add-new-group {
                margin-top: 10px;
            }

            .remove-group {
                background-color: #dc3232;
                color: #fff;
                border: none;
                padding: 6px 12px;
                border-radius: 5px;
                cursor: pointer;
            }

            .remove-group:hover {
                background-color: #a00;
            }

            .button-primary {
                background-color: #0073aa;
                border-color: #006799;
                box-shadow: none;
                color: #fff;
                padding: 8px 14px;
                border-radius: 5px;
            }

            .button-primary:hover {
                background-color: #006799;
                border-color: #005177;
            }
        </style>

        <?php
    }

    public static function submit_fb_custom_events_settings() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'sbsp_fb_custom_events_settings_form_nonce')) {
            return;
        }
    
        $link_selectors = isset($_POST['link_selectors']) ? $_POST['link_selectors'] : [];
        $link_event_names = isset($_POST['link_event_names']) ? $_POST['link_event_names'] : [];
        $link_data = [];
    
        foreach ($link_selectors as $index => $selector) {
            $event_name = isset($link_event_names[$index]) ? sanitize_text_field($link_event_names[$index]) : '';
            if (!empty($selector) && !empty($event_name)) {
                $link_data[] = [
                    'selector'   => sanitize_text_field($selector),
                    'event_name' => $event_name,
                ];
            }
        }
    
        $scroll_selectors = isset($_POST['scroll_selectors']) ? $_POST['scroll_selectors'] : [];
        $scroll_event_names = isset($_POST['scroll_event_names']) ? $_POST['scroll_event_names'] : [];
        $scroll_data = [];
    
        foreach ($scroll_selectors as $index => $selector) {
            $event_name = isset($scroll_event_names[$index]) ? sanitize_text_field($scroll_event_names[$index]) : '';
            if (!empty($selector) && !empty($event_name)) {
                $scroll_data[] = [
                    'selector' => sanitize_text_field($selector),
                    'event_name'    => $event_name,
                ];
            }
        }
    
        $click_selectors = isset($_POST['click_selectors']) ? $_POST['click_selectors'] : [];
        $click_event_names = isset($_POST['click_event_names']) ? $_POST['click_event_names'] : [];
        $click_data = [];
    
        foreach ($click_selectors as $index => $selector) {
            $event_name = isset($click_event_names[$index]) ? sanitize_text_field($click_event_names[$index]) : '';
            if (!empty($selector) && !empty($event_name)) {
                $click_data[] = [
                    'selector' => sanitize_text_field($selector),
                    'event_name'    => $event_name,
                ];
            }
        }

        $time_selectors = isset($_POST['time_selectors']) ? $_POST['time_selectors'] : [];
        $time_seconds = isset($_POST['time_seconds']) ? $_POST['time_seconds'] : [];
        $time_event_names = isset($_POST['time_event_names']) ? $_POST['time_event_names'] : [];
        $time_data = [];

        foreach ($time_selectors as $index => $selector) {
            $seconds = isset($time_seconds[$index]) ? sanitize_text_field($time_seconds[$index]) : '';
            $event_name = isset($time_event_names[$index]) ? sanitize_text_field($time_event_names[$index]) : '';
            if (!empty($selector) && !empty($seconds) && !empty($event_name)) {
                $time_data[] = [
                    'selector' => sanitize_text_field($selector),
                    'seconds' => $seconds,
                    'event_name' => $event_name,
                ];
            }
        }
    
        if (SBSP_License::license_check()) {
            // Save all data to options
            update_option('sbsp_fb_link_events', $link_data);
            update_option('sbsp_fb_scroll_events', $scroll_data);
            update_option('sbsp_fb_click_events', $click_data);
            update_option('sbsp_fb_time_events', $time_data);
        }

    }

    public static function render_fb_custom_events_settings() {

        if (isset($_POST['sbsp_save_fb_custom_events_settings'])) {
            if (check_admin_referer('sbsp_fb_custom_events_settings_form_nonce')) {
                self::submit_fb_custom_events_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }
    
        $link_events = get_option('sbsp_fb_link_events', []);
        $scroll_events = get_option('sbsp_fb_scroll_events', []);
        $click_events = get_option('sbsp_fb_click_events', []);
        $time_events = get_option('sbsp_fb_time_events', []);
        ?>
        
        <style>
            .event-section {
                background: #fff;
                border: 1px solid #ccc;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 8px;
            }
    
            .event-section h4 {
                margin-top: 0;
            }
    
            .event-row {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 10px;
            }
    
            .event-row input {
                padding: 6px;
                flex: 1 1 150px;
                min-width: 120px;
            }
    
            .event-row button {
                background: #d63638;
                color: white;
                border: none;
                padding: 6px 10px;
                cursor: pointer;
                flex-shrink: 0;
            }
    
            .add-btn {
                background: #28a745;
                border: none;
                color: #fff;
                padding: 6px 12px;
                border-radius: 4px;
                margin-top: 10px;
            }
        </style>
    
        <form method="post">
            <?php wp_nonce_field('sbsp_fb_custom_events_settings_form_nonce'); ?>
    
            <!-- Link Events -->
            <div class="event-section" id="link-events">
                <h4>Link Events (Class/ID)</h4>
                <div class="event-container">
                    <?php foreach ($link_events as $event): ?>
                        <div class="event-row">
                            <input type="text" name="link_selectors[]" value="<?= esc_attr($event['selector']) ?>" placeholder=".class / #id">
                            <input type="text" name="link_event_names[]" value="<?= esc_attr($event['event_name']) ?>" placeholder="Event Name">
                            <button type="button" onclick="this.parentNode.remove()">Delete</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-btn" onclick="addEventRow('link')">+ Add Link</button>
            </div>
    
            <!-- Scroll Events -->
            <div class="event-section" id="scroll-events">
                <h4>Scroll Events</h4>
                <div class="event-container">
                    <?php foreach ($scroll_events as $event): ?>
                        <div class="event-row">
                            <input type="text" name="scroll_selectors[]" value="<?= esc_attr($event['selector']) ?>" placeholder=".class or #id">
                            <input type="text" name="scroll_event_names[]" value="<?= esc_attr($event['event_name']) ?>" placeholder="Event Name">
                            <button type="button" onclick="this.parentNode.remove()">Delete</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-btn" onclick="addEventRow('scroll')">+ Add Scroll</button>
            </div>
    
            <!-- Click Events -->
            <div class="event-section" id="click-events">
                <h4>Click Events</h4>
                <div class="event-container">
                    <?php foreach ($click_events as $event): ?>
                        <div class="event-row">
                            <input type="text" name="click_selectors[]" value="<?= esc_attr($event['selector']) ?>" placeholder=".class or #id">
                            <input type="text" name="click_event_names[]" value="<?= esc_attr($event['event_name']) ?>" placeholder="Event Name">
                            <button type="button" onclick="this.parentNode.remove()">Delete</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-btn" onclick="addEventRow('click')">+ Add Click</button>
            </div>
    
            <!-- Time Events -->
            <div class="event-section" id="time-events">
                <h4>Time Events</h4>
                <div class="event-container">
                    <?php foreach ($time_events as $event): ?>
                        <div class="event-row">
                            <input type="text" name="time_selectors[]" value="<?= esc_attr($event['selector']) ?>" placeholder=".class or #id">
                            <input type="text" name="time_seconds[]" value="<?= esc_attr($event['seconds']) ?>" placeholder="Time in seconds">
                            <input type="text" name="time_event_names[]" value="<?= esc_attr($event['event_name']) ?>" placeholder="Event Name">
                            <button type="button" onclick="this.parentNode.remove()">Delete</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-btn" onclick="addEventRow('time')">+ Add Time</button>
            </div>
    
            <p>
                <button type="submit" class="button button-primary" name="sbsp_save_fb_custom_events_settings">Save Settings</button>
            </p>
        </form>
    
        <script>
            function addEventRow(type) {
                let container = document.querySelector(`#${type}-events .event-container`);
                let row = document.createElement('div');
                row.className = 'event-row';
    
                let input1 = document.createElement('input');
                let input2 = document.createElement('input');
                let input3 = null;
    
                input1.type = input2.type = 'text';
    
                if (type === 'link') {
                    input1.name = 'link_selectors[]';
                    input1.placeholder = '.class / #id';
    
                    input2.name = 'link_event_names[]';
                    input2.placeholder = 'Event Name';
                } else if (type === 'time') {
                    input1.name = 'time_selectors[]';
                    input1.placeholder = '.class or #id';
    
                    input2.name = 'time_seconds[]';
                    input2.placeholder = 'Time in seconds';
    
                    input3 = document.createElement('input');
                    input3.type = 'text';
                    input3.name = 'time_event_names[]';
                    input3.placeholder = 'Event Name';
                } else {
                    input1.name = `${type}_selectors[]`;
                    input1.placeholder = '.class or #id';
    
                    input2.name = `${type}_event_names[]`;
                    input2.placeholder = 'Event Name';
                }
    
                let delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.innerText = 'Delete';
                delBtn.onclick = () => row.remove();
    
                row.appendChild(input1);
                row.appendChild(input2);
                if (input3) row.appendChild(input3);
                row.appendChild(delBtn);
    
                container.appendChild(row);
            }
        </script>
    
    <?php
    }    

    public static function submit_fb_developer_section_settings() {

        if (SBSP_License::license_check()) {

            update_option('sbsp_use_original_order_creation_time_fb', isset($_POST['sbsp_use_original_order_creation_time_fb']) ? 1 : 0);
            update_option('sbsp_show_payload_fb', isset($_POST['sbsp_show_payload_fb']) ? 1 : 0);
            update_option('sbsp_show_post_data_fb', isset($_POST['sbsp_show_post_data_fb']) ? 1 : 0);
            update_option('sbsp_show_response_fb', isset($_POST['sbsp_show_response_fb']) ? 1 : 0);
            update_option('sbsp_after_payload_post_data_response_exit_fb', isset($_POST['sbsp_after_payload_post_data_response_exit_fb']) ? 1 : 0);

        }
        
    }

    public static function render_fb_developer_section_settings() {
        if (isset($_POST['sbsp_save_fb_developer_section_settings'])) {
            if (check_admin_referer('sbsp_fb_developer_section_settings_form_nonce')) {
                self::submit_fb_developer_section_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }

        $use_original_order_creation_time_fb = get_option('sbsp_use_original_order_creation_time_fb');
        $show_payload_fb = get_option('sbsp_show_payload_fb');
        $show_post_data_fb = get_option('sbsp_show_post_data_fb');
        $show_response_fb = get_option('sbsp_show_response_fb');
        $after_payload_post_data_response_exit_fb = get_option('sbsp_after_payload_post_data_response_exit_fb');

        ?>

        <div class="wrap">

            <h1>Facebook Developer Section Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_fb_developer_section_settings_form_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">Use Original Order Creation Time for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_use_original_order_creation_time_fb" value="1" <?= $use_original_order_creation_time_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Show Payload for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_show_payload_fb" value="1" <?= $show_payload_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Show Post Data for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_show_post_data_fb" value="1" <?= $show_post_data_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Show Response for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_show_response_fb" value="1" <?= $show_response_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Exit after showing Payload, Post Data, and Response for Facebook</th>
                        <td>
                            <input type="checkbox" name="sbsp_after_payload_post_data_response_exit_fb" value="1" <?= $after_payload_post_data_response_exit_fb ? 'checked' : '' ?>>
                        </td>
                    </tr>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_fb_developer_section_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <?php
    }

}
