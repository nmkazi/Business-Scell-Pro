<?php

$sbsp_pathao_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-pathao-functions.php';
require_once $sbsp_pathao_functions;

class SBSP_pathao_functions {

    public static function pathao_get_customer_history($phone_number) {

        $phone_number = SBSP_Functions::sanitize_phone_number($phone_number);

        if(!$phone_number || strlen($phone_number) != 11){
            return ['error' => 'Invalid phone number'];
        }

        // Step 1: Get access token from saved session or login
        $session = get_option('sbsp_pathao_session');
        $access_token = $session['access_token'] ?? null;
        $token_time = $session['last_login'] ?? 0;

        // Optional: token expires after 6 hours (21600 seconds)
        if (!$access_token || (time() - $token_time > 21600)) {
            $login = self::pathao_login_and_store_session();
            if (isset($login['error'])) {
                return ['error' => 'Login failed: ' . $login['error']];
            }

            $session = get_option('sbsp_pathao_session');
            $access_token = $session['access_token'] ?? null;
        }

        if (!$access_token) {
            return ['error' => 'Access token missing after login'];
        }

        // Step 2: Get customer delivery data
        $response = wp_remote_post('https://merchant.pathao.com/api/v1/user/success', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'body' => json_encode([
                'phone' => $phone_number,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return ['error' => 'Request failed: ' . $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['data']) || !isset($data['data']['customer']) || !isset($data['data']['customer']['successful_delivery']) || !isset($data['data']['customer']['total_delivery'])) {
            return ['success' => 0, 'cancel' => 0, 'total' => 0];
        }

        $customer = $data['data']['customer'];

        $success = $customer['successful_delivery'] ?? 0;
        $total   = $customer['total_delivery'] ?? 0;
        $cancel  = $total - $success;

        return [
            'success' => $success,
            'cancel'  => $cancel,
            'total'   => $total,
        ];
    }

    public static function pathao_login_and_store_session() {
        $email_address = get_option('sbsp_pathao_email_address');
        $password = get_option('sbsp_pathao_password');

        if (!$email_address || !$password) {
            return ['error' => 'Pathao email address or password not set in options'];
        }

        $login_response = wp_remote_post('https://merchant.pathao.com/api/v1/login', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'username' => $email_address,
                'password' => $password,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($login_response)) {
            return ['error' => 'Login request failed: ' . $login_response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($login_response);
        $data = json_decode($body, true);

        if (!isset($data['access_token'])) {
            return ['error' => 'Access token not received from Pathao'];
        }

        // Save token and timestamp in options
        update_option('sbsp_pathao_session', [
            'access_token' => trim($data['access_token']),
            'last_login'   => time(),
        ]);

        return ['success' => 'Login successful and session saved'];
    }
}
