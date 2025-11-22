<?php
/*
Plugin Name: Business Scale Pro
Plugin URI: https://startup-bd.com/
Description: Business Scale Pro is a plugin to give power to your e-commerce business.
Version: 2.0.0
Author: Startup BD
Author URI: https://startup-bd.com/
License: Private
License description: এই প্লাগিনটির কোড পড়া, ব্যবহার করা, কপি করা, প্রকাশ করা বা অন্য যেকোনো উপায়ে ব্যবহার করা সম্পূর্ণ নিষিদ্ধ।
License URI: https://startup-bd.com/
Text Domain: sbsp
*/


if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// Define constants
define('SBSP_VERSION', '2.0.0');
define('SBSP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SBSP_PLUGIN_URL', plugin_dir_url(__FILE__));

//require_once('Helper.php');

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$sbsp_admin_menu = SBSP_PLUGIN_DIR . 'includes/sbsp-admin-menu.php';
$sbsp_woo_custom_order_status = SBSP_PLUGIN_DIR . 'includes/woocommerce/sbsp-woo-custom-order-status.php';
$sbsp_woo_order_table_columns = SBSP_PLUGIN_DIR . 'includes/woocommerce/sbsp-woo-order-table-columns.php';
$sbsp_fb_hooks = SBSP_PLUGIN_DIR . 'includes/fb/sbsp-fb-hooks.php';
$sbsp_link_scroll_click_event_fb = SBSP_PLUGIN_DIR . 'includes/ajax/sbsp-link-scroll-click-event-fb.php';
$sbsp_woo_order_table_bulk_actions = SBSP_PLUGIN_DIR . 'includes/woocommerce/sbsp-woo-order-table-bulk-actions.php';
$sbsp_old_new_order_status_handler = SBSP_PLUGIN_DIR . 'includes/update/sbsp-old-new-order-status-handler.php';
$sbsp_old_new_wp_option_name_handler = SBSP_PLUGIN_DIR . 'includes/update/sbsp-old-new-wp-option-name-handler.php';
$sbsp_order_autosave = SBSP_PLUGIN_DIR . 'includes/ajax/sbsp-order-autosave.php';
$sbsp_woo_checkout_process_hooks = SBSP_PLUGIN_DIR . 'includes/woocommerce/sbsp-woo-checkout-process-hooks.php';
$sbsp_set_customer_access = SBSP_PLUGIN_DIR . 'includes/ajax/sbsp-set-customer-access.php';
$sbsp_customer_courier_history_check = SBSP_PLUGIN_DIR . 'includes/ajax/sbsp-customer-courier-history-check.php';

require_once $class_sbsp_license;

register_activation_hook(__FILE__, 'sbsp_plugin_activation');
function sbsp_plugin_activation() {

  global $wpdb;
  $table_name = $wpdb->prefix . 'sbsp_customers_data';

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_type VARCHAR(255) NOT NULL,
    data_value VARCHAR(255) NOT NULL UNIQUE,
    data_courier_history VARCHAR(255) NULL,
    data_courier_history_last_update_time VARCHAR(255) NULL,
    data_access VARCHAR(255) NOT NULL DEFAULT 'allowed'
) $charset_collate;";

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  dbDelta($sql);

}

register_deactivation_hook( __FILE__, 'sbsp_plugin_deactivation' );

function sbsp_plugin_deactivation() {
    
}

add_action('plugins_loaded', 'sbsp_init');
function sbsp_init(){

    // Check if WooCommerce is active
    // if (!class_exists('WooCommerce')) {
    //     return;
    // }

}

add_action('admin_enqueue_scripts', 'sbsp_admin_enqueue_scripts');
function sbsp_admin_enqueue_scripts() {
    wp_enqueue_style(
        'sbsp-admin-style', // Handle
        SBSP_PLUGIN_URL . 'assets/css/sbsp-admin-style.css', // Path to your CSS file
        array(), // Dependencies
        SBSP_VERSION, // Version
        'all' // Media
    );

    wp_enqueue_script(
      'sbsp-set-customer-access', // Handle
      SBSP_PLUGIN_URL . 'assets/js/sbsp-set-customer-access.js', // Path to your JS file
      array('jquery'), // Dependencies, e.g. jQuery
      SBSP_VERSION, // Version
      true // Load in footer (recommended)
    );

    wp_localize_script('sbsp-set-customer-access', 'sbsp_set_customer_access_object', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce( 'sbsp_set_customer_access_secure_nonce' )
    ));

    wp_enqueue_script(
      'sbsp-customer-courier-history-check', // Handle
      SBSP_PLUGIN_URL . 'assets/js/sbsp-customer-courier-history-check.js', // Path to your JS file
      array('jquery'), // Dependencies, e.g. jQuery
      SBSP_VERSION, // Version
      true // Load in footer (recommended)
    );

    wp_localize_script('sbsp-customer-courier-history-check', 'sbsp_customer_courier_history_check_object', array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce( 'sbsp_customer_courier_history_check_secure_nonce' )
    ));
}

require_once $sbsp_admin_menu;

if(SBSP_License::license_check()){
  require_once $sbsp_woo_order_table_columns;
  require_once $sbsp_fb_hooks;
  require_once $sbsp_link_scroll_click_event_fb;
  require_once $sbsp_woo_order_table_bulk_actions;
  require_once $sbsp_woo_custom_order_status;
  require_once $sbsp_order_autosave;
  require_once $sbsp_woo_checkout_process_hooks;
  require_once $sbsp_set_customer_access;
  require_once $sbsp_customer_courier_history_check;

  if(!get_option('sbsp_1.1.0_database_update_complete', false)){
    require_once $sbsp_old_new_order_status_handler;
    require_once $sbsp_old_new_wp_option_name_handler;
    update_option('sbsp_1.1.0_database_update_complete', true);
  }
}
