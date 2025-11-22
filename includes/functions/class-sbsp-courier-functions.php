<?php

$class_sbsp_license = SBSP_PLUGIN_DIR . 'includes/license/class-sbsp-license.php';
$sbsp_steadfast_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-steadfast-functions.php';
$sbsp_pathao_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-pathao-functions.php';
$sbsp_redx_functions = SBSP_PLUGIN_DIR . 'includes/functions/class-sbsp-redx-functions.php';

require_once $class_sbsp_license;
require_once $sbsp_steadfast_functions;
require_once $sbsp_pathao_functions;
require_once $sbsp_redx_functions;

class SBSP_courier_functions {
    public static function get_customer_courier_history_from_all_couriers($phone_number) {

        $phone_number = SBSP_Functions::sanitize_phone_number($phone_number);

        if(!$phone_number || strlen($phone_number) != 11){
            return null;
        }

        $courier_history = [];
        $steadfast_history = SBSP_steadfast_functions::steadfast_get_customer_history($phone_number);
        $pathao_history = SBSP_pathao_functions::pathao_get_customer_history($phone_number);
        $redx_history = SBSP_redx_functions::redx_get_customer_history($phone_number);

        if(is_array($steadfast_history) && !isset($steadfast_history['error'])){
            $courier_history['steadfast'] = $steadfast_history;
        }

        if(is_array($pathao_history) && !isset($pathao_history['error'])){
            $courier_history['pathao'] = $pathao_history;
        }

        if(is_array($redx_history) && !isset($redx_history['error'])){
            $courier_history['redx'] = $redx_history;
        }

        return $courier_history;
    }

    public static function get_customer_courier_history_from_db($phone_number) {

        $phone_number = SBSP_Functions::sanitize_phone_number($phone_number);

        if(!$phone_number || strlen($phone_number) != 11){
            return null;
        }

        $customer_data = SBSP_Functions::get_customer_data_from_db('phone_number', $phone_number);
        $courier_history = null;
        $courier_history_last_update_time = null;
        
        if($customer_data){
            $courier_history = json_decode($customer_data->data_courier_history, true);
            $courier_history_last_update_time = $customer_data->data_courier_history_last_update_time;
        }

        $update_courier_history = false;
        $update_courier_history_partially = false;
        
        if(!$customer_data){
            $courier_history = self::get_customer_courier_history_from_all_couriers($phone_number);
            if($courier_history){
                global $wpdb;

                $table_name = $wpdb->prefix . 'sbsp_customers_data';

                $wpdb->insert(
                    $table_name,
                    [
                        'data_type'  => 'phone_number',
                        'data_value' => $phone_number,
                        'data_courier_history' => json_encode($courier_history),
                        'data_courier_history_last_update_time' => current_time('mysql'),
                    ],
                    [
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                    ]
                );
            }
        }elseif($customer_data && !$courier_history){
            $update_courier_history = true;
        }else{
            $last_update_timestamp = strtotime($courier_history_last_update_time);
            $current_timestamp = current_time('timestamp'); // WP function returns Unix timestamp in WP timezone

            if (($current_timestamp - $last_update_timestamp) < 24 * 60 * 60 ) {
                if(!isset($courier_history['steadfast']) || !isset($courier_history['pathao']) || !isset($courier_history['redx'])){
                    if(($current_timestamp - $last_update_timestamp) > 5 * 60){
                        $update_courier_history = true;
                        $update_courier_history_partially = true;
                    }
                }
            } else {
                $update_courier_history = true;
            }

        }

        if($update_courier_history){

            if(!is_array($courier_history)){
                $courier_history = [];
            }

            if(!$update_courier_history_partially){
                $courier_history = self::get_customer_courier_history_from_all_couriers($phone_number);
            }else{
                if(!isset($courier_history['steadfast'])){
                    $steadfast_history = SBSP_steadfast_functions::steadfast_get_customer_history($phone_number);
                    if(is_array($steadfast_history) && !isset($steadfast_history['error'])){
                        $courier_history['steadfast'] = $steadfast_history;
                    }
                }elseif(!isset($courier_history['pathao'])){
                    $pathao_history = SBSP_pathao_functions::pathao_get_customer_history($phone_number);
                    if(is_array($pathao_history) && !isset($pathao_history['error'])){
                        $courier_history['pathao'] = $pathao_history;
                    }
                }elseif(!isset($courier_history['redx'])){
                    $redx_history = SBSP_redx_functions::redx_get_customer_history($phone_number);
                    if(is_array($redx_history) && !isset($redx_history['error'])){
                        $courier_history['redx'] = $redx_history;
                    }
                }
            }

            if ($courier_history) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'sbsp_customers_data';

                $wpdb->update(
                    $table_name,
                    [
                        'data_courier_history' => json_encode($courier_history),
                        'data_courier_history_last_update_time' => current_time('mysql'),
                    ],
                    [
                        'data_type' => 'phone_number',
                        'data_value' => $phone_number,
                    ],
                    [
                        '%s',
                        '%s',
                    ],
                    [
                        '%s',
                        '%s',
                    ]
                );
            }
        }

        return $courier_history;
    }

    public static function get_customer_courier_history_percent_from_db($phone_number) {

        $phone_number = SBSP_Functions::sanitize_phone_number($phone_number);

        if(!$phone_number || strlen($phone_number) != 11){
            return null;
        }

        $courier_history = self::get_customer_courier_history_from_db($phone_number);

        if(!$courier_history){
            return null;
        }

        $total_success = 0;
        $total_cancel = 0;
        $total_order = 0;

        foreach($courier_history as $history){
            if(isset($history['success'])){
                $total_success += $history['success'];
            }
            if(isset($history['cancel'])){
                $total_cancel += $history['cancel'];
            }
            if(isset($history['total'])){
                $total_order += $history['total'];
            }
        }

        $success_percent = ($total_order > 0) ? ($total_success / $total_order) * 100 : 0;
        $cancel_percent = ($total_order > 0) ? ($total_cancel / $total_order) * 100 : 0;

        return [
            'total_order' => $total_order,
            'total_success' => $total_success,
            'total_cancel' => $total_cancel,
            'success_percent' => $success_percent,
            'cancel_percent' => $cancel_percent,
        ];
    }

}