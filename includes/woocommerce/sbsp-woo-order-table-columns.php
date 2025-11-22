<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$class_sbsp_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-functions.php';
$class_sbsp_courier_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-courier-functions.php';

require_once $class_sbsp_license;
require_once $class_sbsp_functions;
require_once $class_sbsp_courier_functions;

if(SBSP_License::license_check()){
    function sbsp_add_fb_status_column_header( $columns ) {
        $new_columns = [];
    
        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;
    
            if ( $key === 'order_number' ) {
                $new_columns['sbsp_fb_status'] = __( 'FB Status', 'woocommerce' );
            }

            if ( $key === 'order_total' && get_option('sbsp_enable_customer_courier_history_column', false) ) {
                $new_columns['sbsp_customer_courier_history'] = __( 'History', 'woocommerce' );
            }

            if ( $key === 'order_total' && get_option('sbsp_enable_customer_access_column', false) ) {
                $new_columns['sbsp_customer_access'] = __( 'Access', 'woocommerce' );
            }
        }
    
        return $new_columns;
    }
    add_filter( 'manage_edit-shop_order_columns', 'sbsp_add_fb_status_column_header', 20 );
    add_filter( 'manage_woocommerce_page_wc-orders_columns', 'sbsp_add_fb_status_column_header', 20 );
    
    function sbsp_add_fb_status_column_content( $column, $order_or_post_id ) {

        $order = is_a( $order_or_post_id, 'WC_Order' ) ? $order_or_post_id : wc_get_order( $order_or_post_id );

        if(!$order){
            return;
        }

        if ( $column === 'sbsp_fb_status' ) {
            $results = get_post_meta( $order->get_id(), 'sbsp_purchase_event_results', true );

            if(!$results){
                $results = get_post_meta( $order->get_id(), 'sapi_purchase_event_results', true );
            }

            $sent_count = 1;
    
            if ( is_array( $results ) && isset( $results[0]['event_name'] ) && !empty( $results[0]['event_name'] ) && $results[0]['event_name'] === 'Purchase' ) {
                if(isset($results[0]['sent_count'])){
                    $sent_count = $results[0]['sent_count'];
                }
                echo '<span style="
                    display: inline-block;
                    background-color: #d4edda;
                    color: #155724;
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-size: 11px;
                ">Purchase * '.$sent_count.'</span>';
            }
        }

        if ( $column === 'sbsp_customer_access' ) {
            $phone = $order->get_billing_phone();
            $phone = SBSP_Functions::sanitize_phone_number($phone);
            $email = $order->get_billing_email();
            $ip_address = $order->get_customer_ip_address();

            $phone_data = SBSP_Functions::get_customer_data_from_db('phone_number', $phone);
            $email_data = SBSP_Functions::get_customer_data_from_db('email_address', $email);
            $ip_address_data = SBSP_Functions::get_customer_data_from_db('ip_address', $ip_address);

            if($phone){
                echo '<span data-type="phone_number" data-value="'.$phone.'" class="sbsp-customer-access-btn '.SBSP_Functions::get_customer_access_class($phone_data).'">Phone</span>';
            }

            if($email){
                echo '<span data-type="email_address" data-value="'.$email.'" class="sbsp-customer-access-btn '.SBSP_Functions::get_customer_access_class($email_data).'">Email</span>';
            }

            if($ip_address){
                echo '<span data-type="ip_address" data-value="'.$ip_address.'" class="sbsp-customer-access-btn '.SBSP_Functions::get_customer_access_class($ip_address_data).'">IP Address</span>';
            }
        }

        if($column == 'sbsp_customer_courier_history'){
            $phone = $order->get_billing_phone();
            $phone = SBSP_Functions::sanitize_phone_number($phone);

            $class = 'sbsp-customer-courier-history';

            if(!$phone || strlen($phone) != 11){
                echo '<span>Invalid Phone</span>';
                $class = '';
            }
            
            // $courier_history_percentage = SBSP_courier_functions::get_customer_courier_history_percent_from_db($phone);

            // $success_percent = 0;
            // $cancel_percent = 0;
            // $total_success = 0;
            // $total_cancel = 0;
            // $total_order = 0;

            // if(!$courier_history_percentage){

            // }else{

            //     $total_success = $courier_history_percentage['total_success'];
            //     $total_cancel = $courier_history_percentage['total_cancel'];
            //     $total_order = $courier_history_percentage['total_order'];

            //     $success_percent = $courier_history_percentage['success_percent'];
            //     $cancel_percent = $courier_history_percentage['cancel_percent'];

            // }

            ?>

            <div class="<?= $class ?>" data-phone="<?= $phone ?>" style="width: 100%; max-width: 500px; font-family: Arial, sans-serif;">
                <div style="width: 100%; display: flex; height: 10px; border: 1px solid #ccc; border-radius: 6px; overflow: hidden;">
                    <div class="sbsp-customer-success" style="background-color: #08be08;"></div>
                    <div class="sbsp-customer-cancel" style="background-color: #ff2929;"></div>
                </div>
                <div class="sbsp-customer-courier-history-text" style="font-size: 12px; line-height: 18px; margin-top: 5px;"></div>
            </div>

            <?php

            // if($courier_history_percentage){
            //     echo '<span>Total: '.$total_order.'</span> ';
            //     echo '<span style="color: green;">Success: '.$total_success.'</span> ';
            //     echo '<span style="color: red;">Cancel: '.$total_cancel.'</span>';
            // }

        }
    }
    add_action( 'manage_shop_order_posts_custom_column', 'sbsp_add_fb_status_column_content', 20, 2 );
    add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'sbsp_add_fb_status_column_content', 20, 2 );
}