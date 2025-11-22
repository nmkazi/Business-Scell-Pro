<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$class_sbsp_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-functions.php';
$class_sbsp_courier_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-courier-functions.php';

require_once $class_sbsp_license;
require_once $class_sbsp_functions;
require_once $class_sbsp_courier_functions;

if (SBSP_License::license_check()) {
    add_action('wp_ajax_sbsp_customer_courier_history_check', 'sbsp_customer_courier_history_check');
    add_action('wp_ajax_nopriv_sbsp_customer_courier_history_check', 'sbsp_customer_courier_history_check');
}

function sbsp_customer_courier_history_check() {
    check_ajax_referer('sbsp_customer_courier_history_check_secure_nonce');

    $phone_number = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

    $phone_number = SBSP_Functions::sanitize_phone_number($phone_number);

    if(!$phone_number || strlen($phone_number) != 11){
        wp_send_json_error('Invalid phone number');
    }

    $courier_history_percent = SBSP_courier_functions::get_customer_courier_history_percent_from_db($phone_number);

    wp_send_json_success($courier_history_percent);
}