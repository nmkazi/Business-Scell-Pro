<?php

class SBSP_redx_functions {

    public static function redx_get_customer_history($phone_number) {

        $phone_number = SBSP_Functions::sanitize_phone_number($phone_number);

        if(!$phone_number || strlen($phone_number) != 11){
            return ['error' => 'Invalid phone number'];
        }

        $access_token = get_option('sbsp_redx_access_token');

        if (!$access_token) {
            return ['error' => 'REDX access token not found in options'];
        }

        $url = 'https://redx.com.bd/api/redx_se/admin/parcel/customer-success-return-rate?phoneNumber=88' . urlencode($phone_number);

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'User-Agent'    => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept'        => 'application/json, text/plain, */*',
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return ['error' => 'Request failed: ' . $response->get_error_message()];
        }

        $status = wp_remote_retrieve_response_code($response);

        if ($status === 401) {
            return ['error' => 'Access token expired or invalid', 'status' => 401];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['data']) || !isset($data['data']['deliveredParcels']) || !isset($data['data']['totalParcels'])) {
            return ['error' => 'Invalid or unexpected response format'];
        }

        $delivered = (int)($data['data']['deliveredParcels'] ?? 0);
        $total     = (int)($data['data']['totalParcels'] ?? 0);
        $cancel    = $total - $delivered;

        return [
            'success' => $delivered,
            'cancel'  => $cancel,
            'total'   => $total,
        ];
    }
}
