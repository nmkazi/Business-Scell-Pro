<?php

require_once SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';

if(SBSP_License::license_check()){
    add_action('init', 'sbsp_register_custom_order_statuses');
    function sbsp_register_custom_order_statuses() {

        $statuses = [
            'sbsp-purchase' => 'Purchase',
            'sbsp-confirmed' => 'Confirmed',
            'sbsp-shipping'  => 'Shipping',
            'sbsp-returned'  => 'Returned',
            'sbsp-delivered' => 'Delivered',
            'sbsp-autosave' => 'Autosave',
        ];

        foreach ($statuses as $slug => $label) {
            register_post_status('wc-' . $slug, array(
                'label'                     => $label,
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop("$label (%s)", "$label (%s)")
            ));
        }
    }

    // 2. Add them to the WooCommerce order status list
    add_filter('wc_order_statuses', 'sbsp_add_custom_order_statuses');
    function sbsp_add_custom_order_statuses($order_statuses) {
        $custom_statuses = [
            'wc-sbsp-purchase' => 'Purchase',
            'wc-sbsp-confirmed' => 'Confirmed',
            'wc-sbsp-shipping'  => 'Shipping',
            'wc-sbsp-returned'  => 'Returned',
            'wc-sbsp-delivered' => 'Delivered',
            'wc-sbsp-autosave' => 'Autosave',
        ];

        $new_statuses = [];

        foreach ($order_statuses as $key => $label) {
            $new_statuses[$key] = $label;

            if ('wc-processing' === $key) {
                foreach ($custom_statuses as $custom_key => $custom_label) {
                    $new_statuses[$custom_key] = $custom_label;
                }
            }
        }

        return $new_statuses;
    }

    add_action('admin_head', 'sbsp_custom_order_status_colors');
    function sbsp_custom_order_status_colors() {
        echo '<style>
            .order-status.status-sbsp-purchase  { background: #28a745; color: #fff; }
            .order-status.status-sbsp-confirmed { background: #90be6d; color: #fff; }
            .order-status.status-sbsp-shipping  { background: #577590; color: #fff; }
            .order-status.status-sbsp-returned  { background: #f94144; color: #fff; }
            .order-status.status-sbsp-delivered { background: #43aa8b; color: #fff; }
            .order-status.status-sbsp-autosave  { background: #a29bfe; color: #fff; }
            .order-status.status-cancelled { background: #dc3545; color: #fff; }
        </style>';
    }

}