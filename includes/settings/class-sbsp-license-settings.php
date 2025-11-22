<?php

if (!defined('ABSPATH')) {
    exit;
}

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$sbsp_promotional_header = SBSP_PLUGIN_DIR . 'includes/templates/headers/sbsp-promotional-header.php';

require_once $class_sbsp_license;

class SBSP_License_Settings {

    public static function submit_license_settings() {
        if(isset($_POST['license_key']) && !empty($_POST['license_key'])){
            update_option('sbsp_license_key', isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '');
        }

        echo '<div class="updated notice"><p><strong>Settings saved successfully.</strong></p></div>';
    }

    public static function render_license_settings() {
        
        if (isset($_POST['sbsp_save_license_settings'])) {
            if (check_admin_referer('sbsp_license_settings_form_nonce')) {
                self::submit_license_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }

        ?>

        <div class="wrap">
            <?php 
              global $sbsp_promotional_header;
              require_once $sbsp_promotional_header;
            ?>
            
            <h1>License Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_license_settings_form_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">License Key</th>
                        <td>
                            <input style="width: 100%;height: 45px;" type="text" name="license_key" value="" placeholder="<?= SBSP_License::license_check() ? 'Active' : 'Not Active' ?>">
                        </td>
                    </tr>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_license_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <?php
    }
}
