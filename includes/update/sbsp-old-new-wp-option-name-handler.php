<?php

$old_new_wp_option_names = [
    'sapi_fb_pixel_capi_groups' => 'sbsp_fb_pixel_capi_groups',
    'sapi_enable_order_flow_tracking_fb' => 'sbsp_enable_order_flow_tracking_fb',
    'sapi_enable_test_event_code_fb' => 'sbsp_enable_test_event_code_fb',
    'sapi_enable_fb_capi' => 'sbsp_enable_fb_capi',
    'sapi_send_purchase_data_immediately_fb' => 'sbsp_send_purchase_data_immediately_fb',
    'sapi_fb_link_events' => 'sbsp_fb_link_events',
    'sapi_fb_scroll_events' => 'sbsp_fb_scroll_events',
    'sapi_fb_click_events' => 'sbsp_fb_click_events',
    'sapi_use_original_order_creation_time_fb' => 'sbsp_use_original_order_creation_time_fb',
    'sapi_show_payload_fb' => 'sbsp_show_payload_fb',
    'sapi_show_post_data_fb' => 'sbsp_show_post_data_fb',
    'sapi_show_response_fb' => 'sbsp_show_response_fb',
    'sapi_after_payload_post_data_response_exit_fb' => 'sbsp_after_payload_post_data_response_exit_fb',
    // 'sapi_license_key' => 'sbsp_license_key',
];

foreach ( $old_new_wp_option_names as $old_option => $new_option ) {
    $value = get_option( $old_option, null );

    if ( $value !== null ) {
        update_option( $new_option, $value );

        delete_option( $old_option );

    }
}

delete_option('sapi_license_key');
delete_option('sfboas_license_key');