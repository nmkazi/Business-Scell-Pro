<?php

class SBSP_order_autosave_functions {
    public static function get_args_for_order_check($customer_data_type = null, $customer_data_value = null, $order_status = null, $order_id = null){
        $session_time_limit = 60 * (get_option('sbsp_order_autosave_session_time') ? (int)get_option('sbsp_order_autosave_session_time') : 5);

        $args = array(
            'date_created'  => '>=' . (time() - $session_time_limit),
            'limit'         => -1,              // Only get 1 order
            'orderby'       => 'date',         // Order by date
            'order'         => 'DESC',         // Most recent first
            'paginate'      => true,          // No pagination needed
        );

        if($customer_data_type && $customer_data_value){
            $args[$customer_data_type] = $customer_data_value;
        }
        if($order_status){
            $args['status'] = $order_status; //can be 'processing' or 'wc-processing'
        }
        if($order_id){
            $args['id'] = $order_id;
        }

        return $args;
    }

    public static function processed_order_check_by_phone_email_ip(){

        $billing_phone = isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '';
        $billing_email = isset($_POST['billing_email']) ? sanitize_text_field($_POST['billing_email']) : '';
        $ip_address = WC_Geolocation::get_ip_address();

        $excluded_statuses = array('wc-sbsp-autosave');
        $allowed_statuses = array_diff(array_keys(wc_get_order_statuses()), $excluded_statuses);

        $args = self::get_args_for_order_check('billing_phone', $billing_phone, $allowed_statuses);

        if($billing_phone){
            $args = self::get_args_for_order_check('billing_phone', $billing_phone, $allowed_statuses);

            $orders = wc_get_orders($args);
            if($orders && $orders->total > 0){
                wp_send_json_error('Order already made and autosave is disabled temporarily.');
                wp_die();
            }
        }

        if($billing_email){
            $args = self::get_args_for_order_check('billing_email', $billing_email, $allowed_statuses);
            $orders = wc_get_orders($args);
            if($orders && $orders->total > 0){
                wp_send_json_error('Order already made and autosave is disabled temporarily.');
                wp_die();
            }
        }

        // if($ip_address){
        //     $args = self::get_args_for_order_check('ip_address', $ip_address, $allowed_statuses);
        //     $orders = wc_get_orders($args);
        //     if($orders && $orders->total > 0){
        //         wp_send_json_error('Order already made and autosave is disabled temporarily.');
        //         wp_die();
        //     }
        // }
    }

    public static function customer_autosave_order_check_by_email_phone_ip(){
        $billing_phone = isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '';
        $billing_email = isset($_POST['billing_email']) ? sanitize_text_field($_POST['billing_email']) : '';
        $ip_address = WC_Geolocation::get_ip_address();

        if($billing_phone){
            $args = self::get_args_for_order_check('billing_phone', $billing_phone, 'wc-sbsp-autosave');
            $orders = wc_get_orders($args);
            if($orders && $orders->total > 0){
                $order = $orders->orders[0];
                return $order;
            }
        }

        if($billing_email){
            $args = self::get_args_for_order_check('billing_email', $billing_email, 'wc-sbsp-autosave');
            $orders = wc_get_orders($args);
            if($orders && $orders->total > 0){
                $order = $orders->orders[0];
                return $order;
            }
        }

        // if($ip_address){
        //     $args = self::get_args_for_order_check('ip_address', $ip_address, 'wc-sbsp-autosave');
        //     $orders = wc_get_orders($args);
        //     if($orders && $orders->total > 0){
        //         $order = $orders->orders[0];
        //         return $order;
        //     }
        // }

        return null;
    }

}