<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$class_sbsp_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-functions.php';

require_once $class_sbsp_license;
require_once $class_sbsp_functions;

if (SBSP_License::license_check()) {
    add_action('wp_ajax_sbsp_set_customer_access', 'sbsp_set_customer_access');
    add_action('wp_ajax_nopriv_sbsp_set_customer_access', 'sbsp_set_customer_access');
}

function sbsp_set_customer_access() {
    
    check_ajax_referer('sbsp_set_customer_access_secure_nonce');

    $type  = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';

    if(!$type || !$value){
        wp_send_json_error('Invalid data type or value');
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'sbsp_customers_data';

    $existing_row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE data_type = %s AND data_value = %s",
            $type,
            $value
        )
    );
    $access = '';
    // If exists: update
    if ($existing_row) {
        if($existing_row->data_access == 'allowed'){
            $access = 'blocked';
        }else{
            $access = 'allowed';
        }

        $result = $wpdb->update(
            $table_name,
            array('data_access' => $access),
            array('data_type' => $type, 'data_value' => $value)
        );
    } else {
        $access = 'blocked';
        // If not exists: insert
        $result = $wpdb->insert(
            $table_name,
            array(
                'data_type' => $type,
                'data_value' => $value,
                'data_access' => $access
            )
        );
    }

    wp_send_json_success($access);
}