<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$class_sbsp_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-functions.php';
$class_sbsp_order_autosave_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-order-autosave-functions.php';

require_once $class_sbsp_license;
require_once $class_sbsp_functions;
require_once $class_sbsp_order_autosave_functions;

if (get_option('sbsp_enable_order_autosave') && SBSP_License::license_check()) {
    function sbsp_enqueue_autosave_scripts() {
        wp_enqueue_script(
            'sbsp-autosave',
            SBSP_PLUGIN_URL . 'assets/js/sbsp-order-autosave.js',
            array('jquery'),
            filemtime(SBSP_PLUGIN_DIR . 'assets/js/sbsp-order-autosave.js'),
            true
        );
    
        // Localize the script with AJAX URL
        wp_localize_script('sbsp-autosave', 'sbsp_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce( 'sbsp_autosave_secure_nonce' )
        ));
    }
    add_action('wp_enqueue_scripts', 'sbsp_enqueue_autosave_scripts');
}

if (get_option('sbsp_enable_order_autosave') && SBSP_License::license_check()) {
    add_action('wp_ajax_autosave_order', 'sbsp_order_autosave');
    add_action('wp_ajax_nopriv_autosave_order', 'sbsp_order_autosave');
}

function sbsp_order_autosave() {
    
    // check_ajax_referer('sbsp_autosave_secure_nonce');
    
    // Check the referrer for security
    // if (!isset($_SERVER['HTTP_REFERER']) || parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== 'startup-bd.com') {
    //     wp_send_json_error('Unauthorized request');
    //     wp_die();
    // }

    if ( !SBSP_License::referer_license_check() || !get_option('sbsp_enable_order_autosave', 0)) {
        wp_die();
    }

    // Check and sanitize inputs
    if (empty($_POST['billing_phone']) || strlen($_POST['billing_phone']) < 11) {
        wp_send_json_error('Missing required field(s)');
        wp_die();
    }

    $billing_phone = isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '';
    $billing_email = isset($_POST['billing_email']) ? sanitize_text_field($_POST['billing_email']) : '';
    $ip_address = WC_Geolocation::get_ip_address();
    $session_time_limit = 60 * (get_option('sbsp_autosave_session_duration') ? (int)get_option('sbsp_autosave_session_duration') : 5);
    $is_new_order = false;

    if($billing_phone && get_option('sbsp_block_phone_numbers')){
        $phone = SBSP_Functions::sanitize_phone_number($billing_phone);
        $phone_data = SBSP_Functions::get_customer_data_from_db('phone_number', $phone);
        if($phone_data && $phone_data->data_access == 'blocked'){
            wp_send_json_error('Phone is blocked');
            wp_die();
        }
    }

    if($billing_email && get_option('sbsp_block_email_addresses')){
        $email_data = SBSP_Functions::get_customer_data_from_db('email_address', $billing_email);
        if($email_data && $email_data->data_access == 'blocked'){
            wp_send_json_error('Email is blocked');
            wp_die();
        }
    }

    if($ip_address && get_option('sbsp_block_ip_addresses')){
        $ip_address_data = SBSP_Functions::get_customer_data_from_db('ip_address', $ip_address);
        if($ip_address_data && $ip_address_data->data_access == 'blocked'){
            wp_send_json_error('IP address is blocked');
            wp_die();
        }
    }

    $excluded_statuses = array('wc-sbsp-autosave');
    $allowed_statuses = array_diff(array_keys(wc_get_order_statuses()), $excluded_statuses);

    if(isset($_COOKIE['sbsp_customer_created_order_id']) && !empty($_COOKIE['sbsp_customer_created_order_id'])){

        $args = SBSP_order_autosave_functions::get_args_for_order_check(null, null, $allowed_statuses, (int)$_COOKIE['sbsp_customer_created_order_id']);
        $orders = wc_get_orders($args);

        if($orders && $orders->total > 0){
            wp_send_json_error('Order already made by user and autosave is disabled temporarily.');
            wp_die();
        }
        else{
            SBSP_order_autosave_functions::processed_order_check_by_phone_email_ip();
        }

    }
    else{
        SBSP_order_autosave_functions::processed_order_check_by_phone_email_ip();
    }

    $order = null;
    $is_new_order = false;

    if(isset($_COOKIE['sbsp_autosave_order_id']) && !empty($_COOKIE['sbsp_autosave_order_id'])){
        $autosave_order_id = (int)$_COOKIE['sbsp_autosave_order_id'];

        $args = SBSP_order_autosave_functions::get_args_for_order_check(null, null, 'wc-sbsp-autosave', $autosave_order_id);
        
        $orders = wc_get_orders($args);
        
        if($orders && $orders->total > 0){
            $order = $orders->orders[0];
            // if($order->get_status() == 'sbsp-autosave'){
            //     $order = $order;
            // }else{
            //     wp_send_json_error('Order already processed by admin.');
            //     wp_die();
            // }
        }
        else{
            $order = SBSP_order_autosave_functions::customer_autosave_order_check_by_email_phone_ip();
        }
    }
    else{
        $order = SBSP_order_autosave_functions::customer_autosave_order_check_by_email_phone_ip();
    }

    if(!$order){
        $order = wc_create_order();
        $is_new_order = true;
    }

    setcookie('sbsp_autosave_order_id', $order->get_id(), time() + $session_time_limit);
    setcookie('sbsp_customer_created_order_id', null, time() - 3600);

    // Sanitize and update order data
    // $first_name = isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : '';
    // $address_1 = isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : '';
    // $phone = isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '';
    // $city = isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : '';
    // $state = isset($_POST['billing_state']) ? sanitize_text_field($_POST['billing_state']) : '';
    // $country = isset($_POST['billing_country']) ? sanitize_text_field($_POST['billing_country']) : '';
    // $email = isset($_POST['billing_email']) ? sanitize_text_field($_POST['billing_email']) : '';

    $fields = [
        'billing_first_name',
        'billing_address_1',
        'billing_phone',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_email'
    ];

    $data = [];
    foreach ( $fields as $field ) {
        $data[ $field ] = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
    }

    // If user is logged in, override with user meta if data exists
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();

        foreach ( $fields as $field ) {
            $user_value = get_user_meta( $user_id, $field, true );
            if ( ! empty( $user_value ) && empty($data[ $field ]) ) {
                $data[ $field ] = $user_value;
            }
        }
    }

    // Access sanitized and merged billing data
    $first_name = $data['billing_first_name'];
    $address_1  = $data['billing_address_1'];
    $phone      = $data['billing_phone'];
    $city       = $data['billing_city'];
    $state      = $data['billing_state'];
    $country    = $data['billing_country'];
    $email      = $data['billing_email'];


    // Update the order data
    $order->set_billing_first_name($first_name);
    $order->set_billing_address_1($address_1);
    $order->set_billing_phone($phone);
    $order->set_billing_city($city);
    $order->set_billing_state($state);
    $order->set_billing_country($country);
    $order->set_billing_email($email);

    // Clear existing items and add cart items
    $order->remove_order_items();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $order->add_product($cart_item['data'], $cart_item['quantity']);
    }

    // Set shipping cost based on selected shipping method from cart
    $shipping_methods = WC()->session->get('chosen_shipping_methods');
    if (!empty($shipping_methods) && is_array($shipping_methods)) {
        $shipping_method_id = $shipping_methods[0];
        $shipping_total = WC()->cart->get_shipping_total();

        if ($shipping_total) {
            $rate = new WC_Shipping_Rate($shipping_method_id, __('Shipping', 'woocommerce'), $shipping_total, [], $shipping_method_id);
            $order->add_shipping($rate);
        }
    }

    // Calculate order total based on cart contents, fees, taxes, and shipping
    $order_total = WC()->cart->get_total('edit'); // Retrieves the cart total amount
    $order->set_total($order_total); // Set the order total
	
    $order->set_status('sbsp-autosave'); // Set order status to Autosave

    $order->save();

    if (!empty($_COOKIE['_fbc'])) {
        $fbc = sanitize_text_field($_COOKIE['_fbc']);
    }else{
        $fbc = '';
    }

    if (!empty($_COOKIE['_fbp'])) {
        $fbp = sanitize_text_field($_COOKIE['_fbp']);
    }else{
        $fbp = '';
    }

    // if(!empty($fbc) && !empty($fbp)){
        $fb_cookie = [
            [
                'fbc' => $fbc,
                'fbp' => $fbp,
                'event_source_url' => $_SERVER['HTTP_REFERER'],
                'client_ip_address' => $_SERVER['REMOTE_ADDR'],
                'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                // 'time' => time(),
            ]
        ];
        update_post_meta( $order->get_id(), 'sbsp_purchase_event_results', $fb_cookie );
    // }

    // Respond with message
    wp_send_json_success(['message' => $is_new_order ? 'Autosave order created' : 'Autosave order updated', 'order_id' => $order->get_id()]);
    wp_die();
}

// Hook into the WooCommerce checkout process to delete autosave order
add_action('woocommerce_checkout_order_processed', 'sbsp_delete_autosave_order_on_confirm', 10, 1);

function sbsp_delete_autosave_order_on_confirm($order_id) {

    $session_time_limit = 60 * (get_option('sbsp_order_autosave_session_time') ? (int)get_option('sbsp_order_autosave_session_time') : 5);
    setcookie('sbsp_customer_created_order_id', $order_id, time() + $session_time_limit);
    
    $order = wc_get_order($order_id);
    $billing_phone = $order->get_billing_phone();
    $billing_email = $order->get_billing_email();
    $ip_address = $order->get_customer_ip_address();
    $autosave_order_id = isset($_COOKIE['sbsp_autosave_order_id']) ? (int)$_COOKIE['sbsp_autosave_order_id'] : null;
    setcookie('sbsp_autosave_order_id', null, time() - 3600);

    if($autosave_order_id){
        $args = SBSP_order_autosave_functions::get_args_for_order_check(null, null, 'wc-sbsp-autosave', $autosave_order_id);
        $orders = wc_get_orders($args);
        foreach($orders->orders as $order){
            $order->delete( true );
        }
    }

    if($billing_phone){
        $args = SBSP_order_autosave_functions::get_args_for_order_check('billing_phone', $billing_phone, 'wc-sbsp-autosave');
        $orders = wc_get_orders($args);
        foreach($orders->orders as $order){
            $order->delete( true );
        }
    }
    if($billing_email){
        $args = SBSP_order_autosave_functions::get_args_for_order_check('billing_email', $billing_email, 'wc-sbsp-autosave');
        $orders = wc_get_orders($args);
        foreach($orders->orders as $order){
            $order->delete( false );
        }
    }
    // if($ip_address){
    //     $args = SBSP_order_autosave_functions::get_args_for_order_check('ip_address', $ip_address, 'wc-sbsp-autosave');
    //     $orders = wc_get_orders($args);
    //     foreach($orders->orders as $order){
    //         $order->delete( false );
    //     }
    // }

}