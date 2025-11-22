<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$class_sbsp_fb_settings = SBSP_PLUGIN_DIR . 'includes/settings/class-sbsp-fb-settings.php';
$class_sbsp_license_settings = SBSP_PLUGIN_DIR . 'includes/settings/class-sbsp-license-settings.php';
$class_sbsp_general_settings = SBSP_PLUGIN_DIR . 'includes/settings/class-sbsp-general-settings.php';

require_once $class_sbsp_license;
require_once $class_sbsp_fb_settings;
require_once $class_sbsp_license_settings;
require_once $class_sbsp_general_settings;

add_action('admin_menu', 'sbsp_admin_menu');
function sbsp_admin_menu() {
    add_menu_page(
        'Startup BD Business Scale Pro',               // Page title
        'Business Scale Pro',                         // Menu title (shows in sidebar)
        'manage_options',               // Capability
        'sbsp-dashboard',               // Menu slug
        'sbsp_dashboard',               // Callback function to display page content
        'dashicons-admin-generic',      // Icon (you can change it)
        5                               // Position (optional)
    );

    if(SBSP_License::license_check()){

        add_submenu_page(
            'sbsp-dashboard',                     // Parent slug (must match main menu slug)
            'General Settings',              // Page title
            'General Settings',                        // Submenu title
            'manage_options',                     // Capability
            'sbsp-general-settings',         // Menu slug for submenu
            'sbsp_general_settings'          // Callback function
          );
  
        add_submenu_page(
          'sbsp-dashboard',                     // Parent slug (must match main menu slug)
          'FB Settings',              // Page title
          'FB Settings',                        // Submenu title
          'manage_options',                     // Capability
          'sbsp-fb-settings',         // Menu slug for submenu
          'sbsp_fb_settings'          // Callback function
        );
  
      }

    add_submenu_page(
      'sbsp-dashboard',                     // Parent slug (must match main menu slug)
      'SBSP License',              // Page title
      'License',                        // Submenu title
      'manage_options',                     // Capability
      'sbsp-license-settings',         // Menu slug for submenu
      'sbsp_license_settings'          // Callback function
    );

}

function sbsp_dashboard(){
    if(!SBSP_License::license_check()){
        wp_redirect(admin_url('admin.php?page=sbsp-license-settings'));
        exit;
    }
    
    wp_redirect(admin_url('admin.php?page=sbsp-general-settings'));
    
}

function sbsp_general_settings(){
    SBSP_General_Settings::render_settings();
}

function sbsp_fb_settings(){
    SBSP_FB_Settings::render_settings();
}

function sbsp_license_settings(){
    SBSP_License_Settings::render_license_settings();
}
