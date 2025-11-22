<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$class_sbsp_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-functions.php';
$class_sbsp_fb_capi = SBSP_PLUGIN_DIR . 'includes/fb/class-sbsp-fb-capi.php';
$class_sbsp_courier_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-courier-functions.php';

require_once $class_sbsp_license;
require_once $class_sbsp_functions;
require_once $class_sbsp_fb_capi;
require_once $class_sbsp_courier_functions;

add_action( 'template_redirect', 'sbsp_send_PageView_event' );

function sbsp_send_PageView_event() {

    if ( is_admin() || !SBSP_License::license_check() || !get_option('sbsp_enable_fb_capi') ) {
        return;
    }

    if (
            // is_admin() &&
            // wp_doing_ajax() &&
            // wp_doing_cron() &&
            // defined( 'REST_REQUEST' ) && 
            !(    
            // Singular content
            is_page() ||
            is_single() ||
            is_singular() ||
    
            // Archives
            is_category() ||
            is_tag() ||
            is_tax() ||
            is_archive() ||
    
            // Homepage / blog
            is_home() ||
            is_front_page() ||
    
            // Search and 404
            is_search() ||
    
            // Author/date/time archives
            is_author() ||
            is_date() ||
            is_year() ||
            is_month() ||
            is_day() ||
            is_time()
            ) || (
                SBSP_Functions::check_preview() ||
                SBSP_Functions::check_cron_ajax_rest()
            )
    ) {
        return;
    }

    static $has_run = false;
    
    if ( $has_run ) {
        return;
    }

    $has_run = true;

    $user_data = [];
    $custom_data = [];

    if (is_user_logged_in()) {
        $user_data = SBSP_Functions::get_hashed_user_data();
    }

    $user_data = SBSP_Functions::get_http_client_data($user_data);

    $result = SBSP_FB_CAPI::send_event( 'PageView', $custom_data, $user_data );

    SBSP_Functions::after_payload_post_data_response_exit_fb();

}


add_action( 'woocommerce_before_single_product', 'sbsp_send_ViewContent_event' );

function sbsp_send_ViewContent_event() {

    if ( is_admin() || !SBSP_License::license_check() || !get_option('sbsp_enable_fb_capi') || SBSP_Functions::check_preview() || SBSP_Functions::check_cron_ajax_rest() ) {
        return;
    }

    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }

    $product_id = get_the_ID();

    if ( $product_id == 0 ) {
        return;
    }

    $product = wc_get_product( $product_id );

    if ( ! $product ) {
        return;
    }

    static $has_run = false;
    
    if ( $has_run ) {
        return;
    }

    $has_run = true;

    $user_data = [];

    $custom_data = [
        'content_type' => 'product',
        'content_ids' => [ $product->get_id() ],
        'content_name' => $product->get_name(),
        'currency' => 'BDT',
        'value' => $product->get_price(),
        'contents' => [
            [
                'id' => $product->get_id(),
                'quantity' => 1,
            ]
        ]
    ];
    
    if (is_user_logged_in()) {
        $user_data = SBSP_Functions::get_hashed_user_data();
    }

    $user_data = SBSP_Functions::get_http_client_data($user_data);

    $result = SBSP_FB_CAPI::send_event( 'ViewContent', $custom_data, $user_data );

    SBSP_Functions::after_payload_post_data_response_exit_fb();

}

$sbsp_funnel_checkout_page = false;

add_action( 'woocommerce_add_to_cart', 'sbsp_send_AddToCart_event', 10, 1 );
add_action( 'woocommerce_ajax_added_to_cart', 'sbsp_send_AddToCart_event', 10, 1 );

function sbsp_send_AddToCart_event( $cart_item_key ) {

  if ( is_admin() || !SBSP_License::license_check() || !get_option('sbsp_enable_fb_capi') || SBSP_Functions::check_preview()) {
      return;
  }

  if(is_checkout() && !get_option('sbsp_enable_AddToCart_funnel_checkout_fb', false)){
    global $sbsp_funnel_checkout_page;
    $sbsp_funnel_checkout_page = true;
    return;
  }

  $cart = WC()->cart->get_cart();
  $product_id = $cart[$cart_item_key]['product_id'];

  $product = wc_get_product( $product_id );

  if ( ! $product ) {
    return;
  }

    static $has_run = false;
    
    if ( $has_run ) {
        return;
    }

    $has_run = true;

  $quantity = $cart[$cart_item_key]['quantity'];

  $user_data = [];

  $custom_data = [
      'content_type' => 'product',
      'content_ids' => [ $product->get_id() ],
      'content_name' => $product->get_name(),
      'currency' => 'BDT',
      'value' => $product->get_price() * $quantity,
      'contents' => [
          [
              'id' => $product->get_id(),
              'quantity' => $quantity,
          ]
      ]
  ];

  if (is_user_logged_in()) {
    $user_data = SBSP_Functions::get_hashed_user_data();
  }

  $user_data = SBSP_Functions::get_http_client_data($user_data);

  $result = SBSP_FB_CAPI::send_event( 'AddToCart', $custom_data, $user_data );

  SBSP_Functions::after_payload_post_data_response_exit_fb();
}


add_action( 'wp', 'sbsp_send_InitiateCheckout_event' );

function sbsp_send_InitiateCheckout_event() {

    if ( is_admin() || !SBSP_License::license_check() || !get_option('sbsp_enable_fb_capi') || SBSP_Functions::check_preview() || SBSP_Functions::check_cron_ajax_rest() ) {
        return;
    }

    global $sbsp_funnel_checkout_page;

    if($sbsp_funnel_checkout_page && !get_option('sbsp_enable_InitiateCheckout_funnel_checkout_fb', false)){
        return;
    }

    // Check if the current page is the checkout page
    if ( is_checkout() && ! is_order_received_page() ) {

        if ( ! function_exists( 'is_product' ) ) {
            return;
        }

        static $has_run = false;
    
        if ( $has_run ) {
            return;
        }

        $has_run = true;

        $user_data = [];

        $custom_data = [
            'currency' => 'BDT',
            'value' => 0,
            'contents' => [],
        ];

        $cart_items = WC()->cart->get_cart();
        $contents = [];
        $content_ids = [];
        $num_items = 0;

        foreach ( $cart_items as $cart_item ) {
            $product = $cart_item['data'];
            $contents[] = [
                'id' => $product->get_id(),
                'quantity' => $cart_item['quantity'],
                // 'price' => $product->get_price(),
            ];
            $content_ids[] = $product->get_id();
            $num_items += $cart_item['quantity'];
        }

        $custom_data['contents'] = $contents;
        $custom_data['content_ids'] = $content_ids;

        $custom_data['num_items'] = $num_items;
        $custom_data['value'] = WC()->cart->get_total('edit');

        if (is_user_logged_in()) {
            $user_data = SBSP_Functions::get_hashed_user_data();
        }

        $user_data = SBSP_Functions::get_http_client_data($user_data);

        $result = SBSP_FB_CAPI::send_event( 'InitiateCheckout', $custom_data, $user_data );

        SBSP_Functions::after_payload_post_data_response_exit_fb();
    }
}


add_action( 'woocommerce_order_status_processing', 'sbsp_send_Purchase_event', 10, 1 );

function sbsp_send_Purchase_event( $order_id ) {

  if ( !SBSP_License::license_check() || !get_option('sbsp_enable_fb_capi') ) {
      return;
  }

  if ( is_admin() ) {
      return;
  }

//   $fb_cookie = [];

//   if( is_admin() && !get_option('sbsp_enable_admin_processing_order_tracking_fb')){
//     return;
//   }elseif(is_admin() && get_option('sbsp_enable_admin_processing_order_tracking_fb')){
//     $fb_cookie = get_post_meta( $order_id, 'sfboas_fb_cookie', true );

//     if(empty($fb_cookie)){
//         $fb_cookie = [];
//         $event_results = get_post_meta( $order_id, 'sbsp_purchase_event_results', true );

//         if(!empty($event_results) && is_array($event_results)){
//             $fb_cookie['fbc'] = $event_results[0]['fbc'];
//             $fb_cookie['fbp'] = $event_results[0]['fbp'];
//         }
//     }
//   }

  $order = wc_get_order( $order_id );

  if ( ! $order ) {
      return;
  }

  $send_purchase_data_immediately = true;

  if(!get_option('sbsp_send_purchase_data_immediately_fb', false)){
    $send_purchase_data_immediately = false;
  }elseif( get_option('sbsp_send_purchase_data_immediately_fb', false) && get_option('sbsp_check_customer_courier_history_send_purchase_data_immediately_fb', false)){
    $customer_history_success_percent = (int) get_option('sbsp_customer_history_success_percent_send_purchase_data_immediately_fb', 0);

    $customer_history = SBSP_courier_functions::get_customer_courier_history_percent_from_db($order->get_billing_phone());

    if(!$customer_history){
        $send_purchase_data_immediately = false;
    }elseif($customer_history && !isset($customer_history['success_percent'])){
        $send_purchase_data_immediately = false;
    }else{
        $success_percent = $customer_history['success_percent'];
        if($success_percent >= $customer_history_success_percent){
            $send_purchase_data_immediately = true;
        }else{
            $send_purchase_data_immediately = false;
        }
    }
  }else{
    $send_purchase_data_immediately = true;
  }

  if(!$send_purchase_data_immediately){
    $fbc = '';
    $fbp = '';
    $microtime = microtime(true);
    $milliseconds = (int) round($microtime * 1000);

    if (isset($_GET['fbclid'])) {
        $fbclid = sanitize_text_field($_GET['fbclid']);
        $fbc = 'fb.1.' . $milliseconds . '.' . $fbclid;
        setcookie('_fbc', $fbc, time() + (90 * 24 * 60 * 60), "/");
        $_COOKIE['_fbc'] = $fbc;
    } elseif (!empty($_COOKIE['_fbc'])) {
        $fbc = sanitize_text_field($_COOKIE['_fbc']);
    }

    if (!empty($_COOKIE['_fbp'])) {
        $fbp = sanitize_text_field($_COOKIE['_fbp']);
    } else {
        $randomNumber = mt_rand(1000000000, 9999999999);
        $fbp = 'fb.1.' . $milliseconds . '.' . $randomNumber;
        setcookie('_fbp', $fbp, time() + (90 * 24 * 60 * 60), "/");
        $_COOKIE['_fbp'] = $fbp;
    }

    $event_results = [
        [
            'fbc' => $fbc,
            'fbp' => $fbp,
            'event_source_url' => $_SERVER['HTTP_REFERER'],
            'client_ip_address' => $_SERVER['REMOTE_ADDR'],
            'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
            // 'time' => time(),
        ]
    ];

    update_post_meta( $order_id, 'sbsp_purchase_event_results', $event_results );

    return;
  }

  static $has_run = false;
    
  if ( $has_run ) {
      return;
  }

  $has_run = true;

  $contents = [];
  $content_ids = [];
  $num_items = 0;

  foreach ( $order->get_items() as $item_id => $item ) {

    $num_items += $item->get_quantity();

    $product = $item->get_product();

    if ( ! $product ) {
        continue;
    }

    $contents[] = [
        'id' => $product->get_id(),
        'quantity' => $item->get_quantity(),
        // 'price' => $product->get_price(),
    ];

    $content_ids[] = $product->get_id();
  }

  $custom_data = [
      'content_type' => 'product',
      'currency' => 'BDT',
      'value' => $order->get_total(),
    //   'order_id' => $order_id,
      'contents' => $contents,
      'num_items' => $num_items,
      'content_ids' => $content_ids,
  ];

  $user_data = [];

  $customer_id = $order->get_customer_id();
  if($customer_id){
    $user_data = SBSP_Functions::get_hashed_user_data($customer_id, $user_data);
  }

  $user_data = SBSP_Functions::get_hashed_order_user_data($order_id, $user_data);

  $user_data = SBSP_Functions::get_http_client_data($user_data);

  $results = SBSP_FB_CAPI::send_event( 'Purchase', $custom_data, $user_data/*, $fb_cookie*/ );

  if (!empty($results) && is_array($results)) {

    foreach ($results as $index => $result) {
        $results[$index]['sent_count'] = 1;
    }

    update_post_meta( $order_id, 'sbsp_purchase_event_results', $results );
  }

  SBSP_Functions::after_payload_post_data_response_exit_fb();

}


function sbsp_process_and_send_order_data_for_order_flow_tracking($order_id, $event_name){

    $order = wc_get_order( $order_id );

    if ( ! $order ) {
        return;
    }

    $contents = [];
    $content_ids = [];
    $num_items = 0;

    foreach ( $order->get_items() as $item_id => $item ) {

        $num_items += $item->get_quantity();

        $product = $item->get_product();

        if ( ! $product ) {
            continue;
        }

        $contents[] = [
            'id' => $product->get_id(),
            'quantity' => $item->get_quantity(),
            // 'price' => $product->get_price(),
        ];

        $content_ids[] = $product->get_id();
    }

    $custom_data = [
        'content_type' => 'product',
        'currency' => 'BDT',
        'value' => $order->get_total(),
        // 'order_id' => $order_id,
        'contents' => $contents,
        'num_items' => $num_items,
        'content_ids' => $content_ids,
    ];

    // $user_data = [
    //     'fn' => hash('sha256', $order->get_billing_first_name()),
    //     // 'ph' => hash('sha256', $order->get_billing_phone()),
    //     // 'em' => hash('sha256', $order->get_billing_email()),
    // ];

    // if($order->get_billing_phone()){
    //     $user_data['ph'] = hash('sha256', $order->get_billing_phone());
    // }

    // if($order->get_billing_email()){
    //     $user_data['em'] = hash('sha256', $order->get_billing_email());
    // }

    // if (is_user_logged_in()) {
    //     $customer_id = $order->get_customer_id();
    //     if($customer_id){
    //         $user_data = SBSP_Functions::get_hashed_user_data($customer_id, $user_data);
    //     }
    // }

    $user_data = [];

    $customer_id = $order->get_customer_id();
    if($customer_id){
        $user_data = SBSP_Functions::get_hashed_user_data($customer_id, $user_data);
    }

    $user_data = SBSP_Functions::get_hashed_order_user_data($order_id, $user_data);

    return SBSP_FB_CAPI::send_order_flow_event( $event_name, $custom_data, $user_data, $order );

}

add_action('woocommerce_order_status_changed', 'sbsp_send_events_on_order_status_changed', 10, 4);
function sbsp_send_events_on_order_status_changed($order_id, $old_status, $new_status, $order) {
    
    if ( !SBSP_License::license_check() || !get_option('sbsp_enable_fb_capi') ) {
        return;
    }

    if(/*!is_admin() ||*/ !get_option('sbsp_enable_order_flow_tracking_fb')){
        return;
    }

    // static $has_run = false;
    
    // if ( $has_run ) {
    //     return;
    // }

    // $has_run = true;

    $status_event_map = [
        // 'processing' => 'Purchase',
        'sbsp-purchase' => 'Purchase',
        'sbsp-confirmed' => 'OrderConfirmed',
        'sbsp-shipping' => 'OrderShipping',
        'sbsp-returned' => 'OrderReturned',
        'sbsp-delivered' => 'OrderDelivered',
        'cancelled' => 'OrderCancelled',
    ];
    
    if (isset($status_event_map[$new_status])) {
        $results = sbsp_process_and_send_order_data_for_order_flow_tracking($order_id, $status_event_map[$new_status]);

        if($new_status === 'sbsp-purchase'){
            $existing_results = get_post_meta( $order_id, 'sbsp_purchase_event_results', true );
            $sent_count = 1;
            if(!empty($existing_results) && is_array($existing_results)){
                foreach ($existing_results as $index => $result) {
                    if(isset($result['sent_count'])){
                        $sent_count = $result['sent_count']+1;
                        break;
                    }
                }
            }

            if(!empty($results) && is_array($results)){
                foreach ($results as $index => $result) {
                    $results[$index]['sent_count'] = $sent_count;
                }

                update_post_meta( $order_id, 'sbsp_purchase_event_results', $results );
            }

        }

        SBSP_Functions::after_payload_post_data_response_exit_fb();
    }
    
}

// add_action('woocommerce_order_status_cancelled', 'sbsp_send_existing_Purchase_event', 10, 1);

function sbsp_send_existing_Purchase_event($order_id){
    if ( !SBSP_License::license_check() || !get_option('sbsp_enable_fb_capi') ) {
        return;
    }

    $order = wc_get_order( $order_id );

    if ( ! $order ) {
        return;
    }

    static $has_run = false;
    
    if ( $has_run ) {
        return;
    }

    $has_run = true;

    $contents = [];
    $content_ids = [];
    $num_items = 0;

    foreach ( $order->get_items() as $item_id => $item ) {

        $num_items += $item->get_quantity();

        $product = $item->get_product();

        if ( ! $product ) {
            continue;
        }

        $contents[] = [
            'id' => $product->get_id(),
            'quantity' => $item->get_quantity(),
            // 'price' => $product->get_price(),
        ];

        $content_ids[] = $product->get_id();
    }

    $custom_data = [
        'content_type' => 'product',
        'currency' => 'BDT',
        'value' => -$order->get_total(),
        'order_id' => $order_id,
        'contents' => $contents,
        'num_items' => $num_items,
        'content_ids' => $content_ids,
    ];

    // $user_data = [
    //     'fn' => hash('sha256', $order->get_billing_first_name()),
    //     // 'ph' => hash('sha256', $order->get_billing_phone()),
    //     // 'em' => hash('sha256', $order->get_billing_email()),
    // ];

    // if($order->get_billing_phone()){
    //     $user_data['ph'] = hash('sha256', $order->get_billing_phone());
    // }

    // if($order->get_billing_email()){
    //     $user_data['em'] = hash('sha256', $order->get_billing_email());
    // }

    // if (is_user_logged_in()) {
    //     $customer_id = $order->get_customer_id();
    //     if($customer_id){
    //         $user_data = SBSP_Functions::get_hashed_user_data($customer_id, $user_data);
    //     }
    // }

    $user_data = [];

    $customer_id = $order->get_customer_id();
    if($customer_id){
        $user_data = SBSP_Functions::get_hashed_user_data($customer_id, $user_data);
    }

    $user_data = SBSP_Functions::get_hashed_order_user_data($order_id, $user_data);

    $purchase_event_results = get_post_meta( $order->get_id(), 'sbsp_purchase_event_results', true );
    if(empty($purchase_event_results)){
        $purchase_event_results = get_post_meta( $order->get_id(), 'sapi_purchase_event_results', true );
    }

    $fb_cookie = get_post_meta( $order->get_id(), 'sbsp_fb_cookie', true );
    if(empty($fb_cookie)){
        $fb_cookie = get_post_meta( $order->get_id(), 'sfboas_fb_cookie', true );
    }

    // $purchase_event_results = get_post_meta( $order_id, 'sbsp_purchase_event_results', true );
    // $fb_cookie = get_post_meta( $order_id, 'sfboas_fb_cookie', true );

    if (!empty($purchase_event_results) && is_array($purchase_event_results)) {
        SBSP_FB_CAPI::send_existing_event( 'Purchase', $custom_data, $user_data, $purchase_event_results );
    }

    SBSP_Functions::after_payload_post_data_response_exit_fb();

}
