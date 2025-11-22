<?php

class SBSP_woo_checkout_process_functions {
    public static function get_args_for_multiple_orders_check($customer_data_type, $customer_data_value){

        $time_limit   = (int) get_option('sbsp_restrict_multiple_orders_time', 24);

        $excluded_statuses = array('wc-pending', 'wc-sbsp-autosave');
        $allowed_statuses = array_diff(array_keys(wc_get_order_statuses()), $excluded_statuses);

        $args = array(
            'date_created' => '>=' . (time() - ($time_limit*3600)),
            'status'       => $allowed_statuses,
            $customer_data_type => $customer_data_value,
            'limit'          => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'paginate'     => true,
        );

        return $args;
        
    }
}