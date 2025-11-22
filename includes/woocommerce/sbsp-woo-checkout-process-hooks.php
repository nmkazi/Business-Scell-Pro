<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$class_sbsp_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-functions.php';
$class_sbsp_woo_checkout_process_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-woo-checkout-process-functions.php';

require_once $class_sbsp_license;
require_once $class_sbsp_functions;
require_once $class_sbsp_woo_checkout_process_functions;

add_action('woocommerce_checkout_process', 'sbsp_check_11_digits_phone');

function sbsp_check_11_digits_phone() {
    
    if (!get_option('sbsp_check_11_digits_phone_number') || !SBSP_License::license_check()) {
        return;
    }

    $phone = isset($_POST['billing_phone']) ? $_POST['billing_phone'] : '';

    $sanitized_phone = preg_replace('/^\+?88/', '', $phone); // Remove +88 or 88
    $sanitized_phone = preg_replace('/^\+/', '', $sanitized_phone);    // Remove + if still at start
    $sanitized_phone = preg_replace('/[^0-9]/', '', $sanitized_phone); // Remove all except numbers

    $checkout_process_error_message = 'দয়া করে ১১ সংখ্যার ফোন নাম্বার দিন। +, +88, 88 এগুলো দিবেন না।';
    if(!empty($checkout_process_error_contact_phone_number)){
        $checkout_process_error_message .= ' যেকোনো প্রয়োজনে যোগাযোগ করুন ' . $checkout_process_error_contact_phone_number;
    }

    if ( strlen( $phone ) !== 11 || strlen( $sanitized_phone ) !== 11) {
        wc_add_notice( $checkout_process_error_message, 'error' );
        return;
    }

}

add_action('woocommerce_checkout_process', 'sbsp_check_blocked_phone_email_ip');

function sbsp_check_blocked_phone_email_ip() {

    if (!SBSP_License::license_check()) {
        return;
    }

    $checkout_process_error_contact_phone_number = get_option('sbsp_checkout_process_error_contact_phone_number');
    $checkout_process_error_message = 'পরে আবার চেষ্টা করুন।';
    if(!empty($checkout_process_error_contact_phone_number)){
        $checkout_process_error_message .= ' যেকোনো প্রয়োজনে যোগাযোগ করুন ' . $checkout_process_error_contact_phone_number;
    }

    $phone = isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '';
    $phone = SBSP_Functions::sanitize_phone_number($phone);

    if ( !empty($phone) && get_option('sbsp_block_phone_numbers')) {
        $phone_data = SBSP_Functions::get_customer_data_from_db('phone_number', $phone);
        if($phone_data && $phone_data->data_access == 'blocked'){
            wc_add_notice($checkout_process_error_message, 'error');
            return;
        }
    }

    $email = isset($_POST['billing_email']) ? sanitize_text_field($_POST['billing_email']) : '';

    if ( !empty($email) && get_option('sbsp_block_email_addresses')) {
        $email_data = SBSP_Functions::get_customer_data_from_db('email_address', $email);
        if($email_data && $email_data->data_access == 'blocked'){
            wc_add_notice($checkout_process_error_message, 'error');
            return;
        }
    }

    $ip_address = WC_Geolocation::get_ip_address();

    if ( !empty($ip_address) && get_option('sbsp_block_ip_addresses')) {
        $ip_address_data = SBSP_Functions::get_customer_data_from_db('ip_address', $ip_address);
        if($ip_address_data && $ip_address_data->data_access == 'blocked'){
            wc_add_notice($checkout_process_error_message, 'error');
            return;
        }
    }

}


add_action('woocommerce_checkout_process', 'sbsp_restrict_multiple_orders_from_same_phone_ip_email');

function sbsp_restrict_multiple_orders_from_same_phone_ip_email() {

    if (!SBSP_License::license_check()) {
        return;
    }

    $order_limit  = (int) get_option('sbsp_restrict_multiple_orders_limit', 1);

    $billing_phone = WC()->checkout()->get_value('billing_phone');
    $billing_email = WC()->checkout()->get_value('billing_email');

    $checkout_process_error_contact_phone_number = get_option('sbsp_checkout_process_error_contact_phone_number');
    $checkout_process_error_message = 'আপনার অর্ডারটি করা হয়ে গিয়েছে। আমাদের প্রতিনিধি শীঘ্রই আপনাকে কল করবেন।';
    if(!empty($checkout_process_error_contact_phone_number)){
        $checkout_process_error_message .= ' যেকোনো প্রয়োজনে যোগাযোগ করুন ' . $checkout_process_error_contact_phone_number;
    }

    if(!empty($billing_phone) && get_option('sbsp_restrict_multiple_orders_same_phone_number')){
        $args_for_phone = SBSP_woo_checkout_process_functions::get_args_for_multiple_orders_check('billing_phone', $billing_phone);
        
        $orders_in_the_interval_with_same_phone = wc_get_orders($args_for_phone);
        if ($orders_in_the_interval_with_same_phone->total >= $order_limit) {
            wc_add_notice($checkout_process_error_message, 'error');
            return;
        }
    }

    if(!empty($billing_email) && get_option('sbsp_restrict_multiple_orders_same_email_address')){
        $args_for_email = SBSP_woo_checkout_process_functions::get_args_for_multiple_orders_check('billing_email', $billing_email);

        $orders_in_the_interval_with_same_email = wc_get_orders($args_for_email);
        if ($orders_in_the_interval_with_same_email->total >= $order_limit) {
            wc_add_notice($checkout_process_error_message, 'error');
            return;
        }
    }

    $ip_address = WC_Geolocation::get_ip_address();

    if( !empty($ip_address) && get_option('sbsp_restrict_multiple_orders_same_ip_address')){
        $args_for_ip = SBSP_woo_checkout_process_functions::get_args_for_multiple_orders_check('customer_ip_address', WC_Geolocation::get_ip_address());

        $orders_in_the_interval_with_same_ip = wc_get_orders($args_for_ip);
        if($orders_in_the_interval_with_same_ip->total >= $order_limit) { 
            wc_add_notice($checkout_process_error_message, 'error');
            return;
        }
    }

}