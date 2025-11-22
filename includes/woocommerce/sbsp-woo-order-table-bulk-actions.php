<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';

require_once $class_sbsp_license;

add_filter( 'bulk_actions-edit-shop_order', 'sbsp_add_custom_bulk_actions_to_woo_order_table' );
add_filter( 'bulk_actions-woocommerce_page_wc-orders', 'sbsp_add_custom_bulk_actions_to_woo_order_table' );
function sbsp_add_custom_bulk_actions_to_woo_order_table( $bulk_actions ) {

    if ( ! SBSP_License::license_check() ) {
        return $bulk_actions;
    }

    $statuses = [
        'sbsp-purchase'   => 'Purchase',
        'sbsp-confirmed'  => 'Confirmed',
        'sbsp-shipping'   => 'Shipping',
        'sbsp-returned'   => 'Returned',
        'sbsp-delivered'  => 'Delivered'
    ];

    foreach ( $statuses as $key => $label ) {
        $bulk_actions[ 'sbsp_change_status_to_' . $key ] = __( 'Change Status to ' . $label, 'sbsp' );
    }

    return $bulk_actions;
}

add_filter( 'handle_bulk_actions-edit-shop_order', 'sbsp_handle_change_status_to_sbsp_custom_status_bulk_action', 10, 3 );
add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', 'sbsp_handle_change_status_to_sbsp_custom_status_bulk_action', 10, 3 );
function sbsp_handle_change_status_to_sbsp_custom_status_bulk_action( $redirect_to, $action, $post_ids ) {

    if ( ! SBSP_License::license_check() ) {
        return $redirect_to;
    }

    $custom_status = null;

    $custom_statuses = [
        'sbsp-purchase'   => 'Purchase',
        'sbsp-confirmed'  => 'Confirmed',
        'sbsp-shipping'   => 'Shipping',
        'sbsp-returned'   => 'Returned',
        'sbsp-delivered'  => 'Delivered'
    ];

    foreach ( $custom_statuses as $key => $label ) {
        if ( $action === 'sbsp_change_status_to_' . $key ) {
            $custom_status = $key;
            break;
        }
    }

    if(!$custom_status){
        return $redirect_to;
    }

    foreach ( $post_ids as $post_id ) {
        $order = wc_get_order( $post_id );
        if ( $order ) {
            $order->update_status( $custom_status, 'Bulk action: Order marked as ' . $custom_statuses[$custom_status] . '.' );
        }
    }

    $redirect_to = add_query_arg( array(
        'bulk_action' => 'marked_' . $custom_status,
        'changed'            => count( $post_ids )
    ), $redirect_to );

    return $redirect_to;
}
