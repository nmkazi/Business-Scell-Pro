<?php

if (!defined('ABSPATH')) {
    exit;
}

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$sbsp_container_start = SBSP_PLUGIN_DIR . 'includes/templates/partials/sbsp-container-start.php';
$sbsp_navbar = SBSP_PLUGIN_DIR . 'includes/templates/partials/sbsp-navbar.php';
$sbsp_container_end = SBSP_PLUGIN_DIR . 'includes/templates/partials/sbsp-container-end.php';

class SBSP_General_Settings{
    public static function render_settings(){
        global $sbsp_container_start, $sbsp_navbar, $sbsp_container_end;
        require_once $sbsp_container_start;

        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        $base_url = admin_url('admin.php?page=sbsp-general-settings');

        require_once $sbsp_navbar;

        ?>

        <nav class="sbsp-tabs">
            <a href="<?php echo $base_url . '&tab=general'; ?>" class="<?php echo $current_tab == 'general' ? 'active' : ''; ?>">General</a>
            <a href="<?php echo $base_url . '&tab=order-autosave'; ?>" class="<?php echo $current_tab == 'order-autosave' ? 'active' : ''; ?>">Order Autosave</a>
            <a href="<?php echo $base_url . '&tab=courier'; ?>" class="<?php echo $current_tab == 'courier' ? 'active' : ''; ?>">Courier</a>
            <a href="<?php echo $base_url . '&tab=fraud-customer-block'; ?>" class="<?php echo $current_tab == 'fraud-customer-block' ? 'active' : ''; ?>">Fraud Block</a>
            <a href="<?php echo $base_url . '&tab=order-table-columns'; ?>" class="<?php echo $current_tab == 'order-table-columns' ? 'active' : ''; ?>">Columns</a>
        </nav>

        <?php

        $tabs = [
            'general' => 'render_general_settings',
            'order-autosave' => 'render_order_autosave_settings',
            'courier' => 'render_courier_settings',
            'fraud-customer-block' => 'render_fraud_customer_block_settings',
            'order-table-columns' => 'render_order_table_columns_settings',
        ];

        $method = $tabs[$current_tab] ?? 'render_general_settings';

        if (method_exists(__CLASS__, $method)) {
            self::$method();
        }
        
        require_once $sbsp_container_end;
    }

    public static function submit_general_settings() {

        if (SBSP_License::license_check()) {
            update_option('sbsp_enable', isset($_POST['sbsp_enable']) ? sanitize_text_field($_POST['sbsp_enable']) : '');
        }
        
    }

    public static function render_general_settings() {
        if (isset($_POST['sbsp_save_general_settings'])) {
            if (check_admin_referer('sbsp_general_settings_form_nonce')) {
                self::submit_general_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }

        $is_enabled = get_option('sbsp_enable');

        ?>

        <div class="wrap">

            <h1>General Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_general_settings_form_nonce'); ?>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">Enable Business Scale Pro</th>
                            <td>
                                <input type="checkbox" name="sbsp_enable" value="1" <?= $is_enabled ? 'checked' : '' ?>>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_general_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <?php
    }

    public static function submit_courier_settings() {

        if (SBSP_License::license_check()) {
            update_option('sbsp_steadfast_email_address', isset($_POST['sbsp_steadfast_email_address']) ? sanitize_text_field($_POST['sbsp_steadfast_email_address']) : '');
            update_option('sbsp_steadfast_password', isset($_POST['sbsp_steadfast_password']) ? sanitize_text_field($_POST['sbsp_steadfast_password']) : '');
            update_option('sbsp_steadfast_api_key', isset($_POST['sbsp_steadfast_api_key']) ? sanitize_text_field($_POST['sbsp_steadfast_api_key']) : '');
            update_option('sbsp_steadfast_secret_key', isset($_POST['sbsp_steadfast_secret_key']) ? sanitize_text_field($_POST['sbsp_steadfast_secret_key']) : '');

            update_option('sbsp_pathao_email_address', isset($_POST['sbsp_pathao_email_address']) ? sanitize_text_field($_POST['sbsp_pathao_email_address']) : '');
            update_option('sbsp_pathao_password', isset($_POST['sbsp_pathao_password']) ? sanitize_text_field($_POST['sbsp_pathao_password']) : '');
            update_option('sbsp_pathao_store_id', isset($_POST['sbsp_pathao_store_id']) ? sanitize_text_field($_POST['sbsp_pathao_store_id']) : '');
            update_option('sbsp_pathao_client_id', isset($_POST['sbsp_pathao_client_id']) ? sanitize_text_field($_POST['sbsp_pathao_client_id']) : '');
            update_option('sbsp_pathao_client_secret', isset($_POST['sbsp_pathao_client_secret']) ? sanitize_text_field($_POST['sbsp_pathao_client_secret']) : '');

            update_option('sbsp_redx_access_token', isset($_POST['sbsp_redx_access_token']) ? sanitize_text_field($_POST['sbsp_redx_access_token']) : '');
        }
        
    }

    public static function render_courier_settings() {
        if (isset($_POST['sbsp_save_courier_settings'])) {
            if (check_admin_referer('sbsp_courier_settings_form_nonce')) {
                self::submit_courier_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }

        $steadfast_email_address = get_option('sbsp_steadfast_email_address');
        $steadfast_password = get_option('sbsp_steadfast_password');
        $steadfast_api_key = get_option('sbsp_steadfast_api_key');
        $steadfast_secret_key = get_option('sbsp_steadfast_secret_key');

        $pathao_email_address = get_option('sbsp_pathao_email_address');
        $pathao_password = get_option('sbsp_pathao_password');
        $pathao_store_id = get_option('sbsp_pathao_store_id');
        $pathao_client_id = get_option('sbsp_pathao_client_id');
        $pathao_client_secret = get_option('sbsp_pathao_client_secret');

        $redx_access_token = get_option('sbsp_redx_access_token');

        ?>

        <div class="wrap">

            <h1>Courier API Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_courier_settings_form_nonce'); ?>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th colspan="2"><strong style="font-size: 22px;">Steadfast</strong></th>
                        </tr>
                        <tr>
                            <th scope="row">Email Address</th>
                            <td>
                                <input type="text" name="sbsp_steadfast_email_address" value="<?= esc_attr($steadfast_email_address) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Password</th>
                            <td>
                                <input type="password" name="sbsp_steadfast_password" value="<?= esc_attr($steadfast_password) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">API Key</th>
                            <td>
                                <input type="password" name="sbsp_steadfast_api_key" value="<?= esc_attr($steadfast_api_key) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Secret Key</th>
                            <td>
                                <input type="password" name="sbsp_steadfast_secret_key" value="<?= esc_attr($steadfast_secret_key) ?>">
                            </td>
                        </tr>
                    </tbody>

                    <tbody>
                        <tr>
                            <th colspan="2"><strong style="font-size: 22px;">Pathao</strong></th>
                        </tr>
                        <tr>
                            <th scope="row">Email Address</th>
                            <td>
                                <input type="text" name="sbsp_pathao_email_address" value="<?= esc_attr($pathao_email_address) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Password</th>
                            <td>
                                <input type="password" name="sbsp_pathao_password" value="<?= esc_attr($pathao_password) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Store ID</th>
                            <td>
                                <input type="text" name="sbsp_pathao_store_id" value="<?= esc_attr($pathao_store_id) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Client ID</th>
                            <td>
                                <input type="text" name="sbsp_pathao_client_id" value="<?= esc_attr($pathao_client_id) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Client Secret</th>
                            <td>
                                <input type="password" name="sbsp_pathao_client_secret" value="<?= esc_attr($pathao_client_secret) ?>">
                            </td>
                        </tr>
                    </tbody>

                    <tbody>
                        <tr>
                            <th colspan="2"><strong style="font-size: 22px;">Redx</strong></th>
                        </tr>
                        <tr>
                            <th scope="row">Access Token</th>
                            <td>
                                <input type="password" name="sbsp_redx_access_token" value="<?= esc_attr($redx_access_token) ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_courier_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <?php
    }

    public static function submit_order_autosave_settings() {

        if (SBSP_License::license_check()) {
            update_option('sbsp_enable_order_autosave', isset($_POST['sbsp_enable_order_autosave']) ? sanitize_text_field($_POST['sbsp_enable_order_autosave']) : '');
            update_option('sbsp_order_autosave_session_time', isset($_POST['sbsp_order_autosave_session_time']) ? sanitize_text_field($_POST['sbsp_order_autosave_session_time']) : '');
        }
        
    }

    public static function render_order_autosave_settings() {
        if (isset($_POST['sbsp_save_order_autosave_settings'])) {
            if (check_admin_referer('sbsp_order_autosave_settings_form_nonce')) {
                self::submit_order_autosave_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }

        $enable_order_autosave = get_option('sbsp_enable_order_autosave');
        $order_autosave_session_time = get_option('sbsp_order_autosave_session_time');

        ?>

        <div class="wrap">

            <h1>Order Autosave Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_order_autosave_settings_form_nonce'); ?>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">Enable Order Autosave</th>
                            <td>
                                <input type="checkbox" name="sbsp_enable_order_autosave" value="1" <?= $enable_order_autosave ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Autosave Session Time Duration (minutes)</th>
                            <td>
                                <input type="number" name="sbsp_order_autosave_session_time" value="<?= !empty($order_autosave_session_time) ? $order_autosave_session_time : 5 ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_order_autosave_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <?php
    }

    public static function submit_fraud_customer_block_settings() {

        if (SBSP_License::license_check()) {
            update_option('sbsp_check_11_digits_phone_number', isset($_POST['sbsp_check_11_digits_phone_number']) ? sanitize_text_field($_POST['sbsp_check_11_digits_phone_number']) : '');
            update_option('sbsp_block_phone_numbers', isset($_POST['sbsp_block_phone_numbers']) ? sanitize_text_field($_POST['sbsp_block_phone_numbers']) : '');
            update_option('sbsp_block_ip_addresses', isset($_POST['sbsp_block_ip_addresses']) ? sanitize_text_field($_POST['sbsp_block_ip_addresses']) : '');
            update_option('sbsp_block_email_addresses', isset($_POST['sbsp_block_email_addresses']) ? sanitize_text_field($_POST['sbsp_block_email_addresses']) : '');
            update_option('sbsp_restrict_multiple_orders_same_phone_number', isset($_POST['sbsp_restrict_multiple_orders_same_phone_number']) ? sanitize_text_field($_POST['sbsp_restrict_multiple_orders_same_phone_number']) : '');
            update_option('sbsp_restrict_multiple_orders_same_ip_address', isset($_POST['sbsp_restrict_multiple_orders_same_ip_address']) ? sanitize_text_field($_POST['sbsp_restrict_multiple_orders_same_ip_address']) : '');
            update_option('sbsp_restrict_multiple_orders_same_email_address', isset($_POST['sbsp_restrict_multiple_orders_same_email_address']) ? sanitize_text_field($_POST['sbsp_restrict_multiple_orders_same_email_address']) : '');
            update_option('sbsp_restrict_multiple_orders_time', isset($_POST['sbsp_restrict_multiple_orders_time']) ? sanitize_text_field($_POST['sbsp_restrict_multiple_orders_time']) : '');
            update_option('sbsp_restrict_multiple_orders_limit', isset($_POST['sbsp_restrict_multiple_orders_limit']) ? sanitize_text_field($_POST['sbsp_restrict_multiple_orders_limit']) : '');
            update_option('sbsp_checkout_process_error_contact_phone_number', isset($_POST['sbsp_checkout_process_error_contact_phone_number']) ? sanitize_text_field($_POST['sbsp_checkout_process_error_contact_phone_number']) : '');
        }
        
    }

    public static function render_fraud_customer_block_settings() {
        if (isset($_POST['sbsp_save_fraud_customer_block_settings'])) {
            if (check_admin_referer('sbsp_fraud_customer_block_settings_form_nonce')) {
                self::submit_fraud_customer_block_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }

        $check_11_digits_phone_number = get_option('sbsp_check_11_digits_phone_number');
        $block_phone_numbers = get_option('sbsp_block_phone_numbers');
        $block_ip_addresses = get_option('sbsp_block_ip_addresses');
        $block_email_addresses = get_option('sbsp_block_email_addresses');
        $restrict_multiple_orders_same_phone_number = get_option('sbsp_restrict_multiple_orders_same_phone_number');
        $restrict_multiple_orders_same_ip_address = get_option('sbsp_restrict_multiple_orders_same_ip_address');
        $restrict_multiple_orders_same_email_address = get_option('sbsp_restrict_multiple_orders_same_email_address');
        $restrict_multiple_orders_time = get_option('sbsp_restrict_multiple_orders_time');
        $restrict_multiple_orders_limit = get_option('sbsp_restrict_multiple_orders_limit');
        $checkout_process_error_contact_phone_number = get_option('sbsp_checkout_process_error_contact_phone_number');
        ?>

        <div class="wrap">

            <h1>Fraud Customer Block Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_fraud_customer_block_settings_form_nonce'); ?>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">Check 11 digits of phone number</th>
                            <td>
                                <input type="checkbox" name="sbsp_check_11_digits_phone_number" value="1" <?= $check_11_digits_phone_number ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Block Phone Numbers</th>
                            <td>
                                <input type="checkbox" name="sbsp_block_phone_numbers" value="1" <?= $block_phone_numbers ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Block Email Addresses</th>
                            <td>
                                <input type="checkbox" name="sbsp_block_email_addresses" value="1" <?= $block_email_addresses ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Block IP Addresses</th>
                            <td>
                                <input type="checkbox" name="sbsp_block_ip_addresses" value="1" <?= $block_ip_addresses ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Restrict multiple orders from same Phone Number</th>
                            <td>
                                <input type="checkbox" name="sbsp_restrict_multiple_orders_same_phone_number" value="1" <?= $restrict_multiple_orders_same_phone_number ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Restrict multiple orders from same IP Address</th>
                            <td>
                                <input type="checkbox" name="sbsp_restrict_multiple_orders_same_ip_address" value="1" <?= $restrict_multiple_orders_same_ip_address ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Restrict multiple orders from same Email Address</th>
                            <td>
                                <input type="checkbox" name="sbsp_restrict_multiple_orders_same_email_address" value="1" <?= $restrict_multiple_orders_same_email_address ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Restrict multiple orders Time (Hours)</th>
                            <td>
                                <input type="number" name="sbsp_restrict_multiple_orders_time" value="<?= !empty($restrict_multiple_orders_time) ? $restrict_multiple_orders_time : 24 ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Restrict multiple orders limit</th>
                            <td>
                                <input type="number" name="sbsp_restrict_multiple_orders_limit" value="<?= !empty($restrict_multiple_orders_limit) ? $restrict_multiple_orders_limit : 1 ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Checkout Process Error Contact Phone Number</th>
                            <td>
                                <input type="text" name="sbsp_checkout_process_error_contact_phone_number" value="<?= !empty($checkout_process_error_contact_phone_number) ? $checkout_process_error_contact_phone_number : '' ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_fraud_customer_block_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <?php
    }

    public static function submit_order_table_columns_settings() {

        if (SBSP_License::license_check()) {
            update_option('sbsp_enable_customer_courier_history_column', isset($_POST['sbsp_enable_customer_courier_history_column']) ? sanitize_text_field($_POST['sbsp_enable_customer_courier_history_column']) : '');
            update_option('sbsp_enable_customer_access_column', isset($_POST['sbsp_enable_customer_access_column']) ? sanitize_text_field($_POST['sbsp_enable_customer_access_column']) : '');
        }
        
    }

    public static function render_order_table_columns_settings() {
        if (isset($_POST['sbsp_save_order_table_columns_settings'])) {
            if (check_admin_referer('sbsp_order_table_columns_settings_form_nonce')) {
                self::submit_order_table_columns_settings();
            } else {
                echo '<div class="error notice"><p><strong>Security check failed. Please try again.</strong></p></div>';
            }
        }

        $enable_customer_courier_history_column = get_option('sbsp_enable_customer_courier_history_column');
        $enable_customer_access_column = get_option('sbsp_enable_customer_access_column');

        ?>

        <div class="wrap">

            <h1>General Settings</h1>
            <form method="post">
                <?php wp_nonce_field('sbsp_order_table_columns_settings_form_nonce'); ?>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">Customer Courier History</th>
                            <td>
                                <input type="checkbox" name="sbsp_enable_customer_courier_history_column" value="1" <?= $enable_customer_courier_history_column ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Customer Access</th>
                            <td>
                                <input type="checkbox" name="sbsp_enable_customer_access_column" value="1" <?= $enable_customer_access_column ? 'checked' : '' ?>>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    <input type="submit" class="button button-primary" name="sbsp_save_order_table_columns_settings" value="Save Settings">
                </p>
            </form>
        </div>

        <?php
    }
}