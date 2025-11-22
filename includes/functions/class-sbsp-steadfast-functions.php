<?php

$sbsp_steadfast_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-steadfast-functions.php';
require_once $sbsp_steadfast_functions;

class SBSP_steadfast_functions {

    public static function steadfast_get_customer_history($phone_number) {

        $phone_number = SBSP_Functions::sanitize_phone_number($phone_number);

        if(!$phone_number || strlen($phone_number) != 11){
            return ['error' => 'Invalid phone number'];
        }

        $url = "https://portal.packzy.com/api/v1/fraud_check/" . $phone_number;

		$args = array(
			'method'  => 'GET'
		);

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			return 'API Request Error: ' . $response->get_error_message();
		}

		$body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        return [
            'success' => $data['total_delivered'],
            'cancel' => $data['total_cancelled'],
            'total'  => $data['total_delivered'] + $data['total_cancelled'],
        ];
    }
    
}