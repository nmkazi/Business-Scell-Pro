<?php

global $wpdb;

$old_new_statuses = [
    'wc-purchase'         => 'wc-sbsp-purchase',
    'wc-confirmed'        => 'wc-sbsp-confirmed',
    'wc-shipping'         => 'wc-sbsp-shipping',
    'wc-returned'         => 'wc-sbsp-returned',
    'wc-delivered'        => 'wc-sbsp-delivered',
    'wc-sapi-purchase'    => 'wc-sbsp-purchase',
    'wc-sapi-confirmed'   => 'wc-sbsp-confirmed',
    'wc-sapi-shipping'    => 'wc-sbsp-shipping',
    'wc-sapi-returned'    => 'wc-sbsp-returned',
    'wc-sapi-delivered'   => 'wc-sbsp-delivered',
    'wc-autosave'    => 'wc-sbsp-autosave',
    'wc-sfboas-autosave'  => 'wc-sbsp-autosave',
];

// Dynamic table names
$orders_table       = $wpdb->prefix . 'wc_orders';
$order_stats_table  = $wpdb->prefix . 'wc_order_stats';
$order_meta_table   = $wpdb->prefix . 'wc_orders_meta';

foreach ($old_new_statuses as $old_status => $new_status) {

    // Update in wp_wc_orders.status
    $wpdb->update(
        $orders_table,
        ['status' => $new_status],
        ['status' => $old_status]
    );

    // Update in wp_wc_order_stats.status
    $wpdb->update(
        $order_stats_table,
        ['status' => $new_status],
        ['status' => $old_status]
    );

    // Update in wp_wc_orders_meta.meta_value
    $wpdb->update(
        $order_meta_table,
        ['meta_value' => $new_status],
        [
            'meta_key'   => '_wp_trash_meta_status',
            'meta_value' => $old_status
        ]
    );
    
}
