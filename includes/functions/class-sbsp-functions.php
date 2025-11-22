<?php

class SBSP_Functions {
    public static function check_cron_ajax_rest(){
        if(
            defined('DOING_CRON') ||
            wp_doing_ajax() ||
            defined('DOING_AJAX') ||
            defined('DOING_REST')){
                return true;
            }
    
        return false;
    }
    
    public static function check_preview(){
        if( is_preview() || isset( $_GET['elementor-preview'] ) || isset($_GET['wp_scrape_key']) ) {
            return true;
        }
    
        return false;
    }
    
    public static function normalize_name_fb($name){
        $name = trim($name);
        $name = preg_replace('/\p{P}/u', '', $name);
        $name = strtolower($name);

        return $name;
    }

    public static function normalize_phone_fb($phone){
        $phone = preg_replace('/\D/', '', $phone);
        $phone = self::sanitize_phone_number($phone);

        return '88' . $phone;
    }

    public static function normalize_email_fb($email){
        $email = trim($email);
        $email = strtolower($email);

        return $email;
    }

    public static function normalize_city_fb($city){
        $city = trim($city);
        $city = preg_replace('/[\p{P}\s]/u', '', $city);
        $city = strtolower($city);

        return $city;
    }

    public static function normalize_state_fb($state){
        $state = trim($state);
        $state = preg_replace('/[^A-Za-z0-9]/', '', $state);
        $state = strtolower($state);

        return $state;
    }

    public static function get_customer_state($country = null, $state_code = null){

        if(!$country || !$state_code){
            return '';
        }
        
        $states = WC()->countries->get_states( $country );
        $state = isset( $states[ $state_code ] ) ? $states[ $state_code ] : '';

        return $state;
    }

    public static function normalize_zip_code_fb($zip_code){
        $zip_code = trim($zip_code);
        $zip_code = preg_replace('/[ -]/', '', $zip_code);
        $zip_code = strtolower($zip_code);

        return $zip_code;
    }

    public static function normalize_country_fb($country){
        $country = trim($country);
        $country = preg_replace('/[^A-Za-z]/', '', $country);
        $country = strtolower($country);

        return $country;
    }
    
    public static function get_hashed_user_data($user_id = null, $user_data = []) {
    
        $current_user_id = is_null($user_id) ? get_current_user_id() : $user_id;

        if(!$current_user_id){
            return $user_data;
        }

        $customer = new WC_Customer($current_user_id);

        $fields = [
            'fn' => self::normalize_name_fb($customer->get_first_name()),
            'ln' => self::normalize_name_fb($customer->get_last_name()),
            'em' => self::normalize_email_fb($customer->get_email()),
            'ct' => self::normalize_city_fb($customer->get_city()),
            'zp' => self::normalize_zip_code_fb($customer->get_postcode()),
            'st' => self::normalize_state_fb(self::get_customer_state($customer->get_country(), $customer->get_state())),
            'country' => self::normalize_country_fb($customer->get_country()),
        ];
    
        foreach ($fields as $key => $value) {
            if ((!array_key_exists($key, $user_data) || (array_key_exists($key, $user_data) && empty($user_data[$key]))) && !empty($value)) {
                $user_data[$key] = hash('sha256', $value);
            }
        }
    
        $fields = [
            'fn' => self::normalize_name_fb($customer->get_billing_first_name()),
            'ln' => self::normalize_name_fb($customer->get_billing_last_name()),
            'ph' => self::normalize_phone_fb($customer->get_billing_phone()),
            'em' => self::normalize_email_fb($customer->get_billing_email()),
            'ct' => self::normalize_city_fb($customer->get_billing_city()),
            'zp' => self::normalize_zip_code_fb($customer->get_billing_postcode()),
            'st' => self::normalize_state_fb(self::get_customer_state($customer->get_billing_country(), $customer->get_billing_state())),
            'country' => self::normalize_country_fb($customer->get_billing_country()),
        ];
    
        foreach ($fields as $key => $value) {
            if ((!array_key_exists($key, $user_data) || (array_key_exists($key, $user_data) && empty($user_data[$key]))) && !empty($value)) {
                $user_data[$key] = hash('sha256', $value);
            }
        }

        $fields = [
            'fn' => self::normalize_name_fb($customer->get_shipping_first_name()),
            'ln' => self::normalize_name_fb($customer->get_shipping_last_name()),
            'ph' => self::normalize_phone_fb($customer->get_shipping_phone()),
            'ct' => self::normalize_city_fb($customer->get_shipping_city()),
            'zp' => self::normalize_zip_code_fb($customer->get_shipping_postcode()),
            'st' => self::normalize_state_fb(self::get_customer_state($customer->get_shipping_country(), $customer->get_shipping_state())),
            'country' => self::normalize_country_fb($customer->get_shipping_country()),
        ];
    
        foreach ($fields as $key => $value) {
            if ((!array_key_exists($key, $user_data) || (array_key_exists($key, $user_data) && empty($user_data[$key]))) && !empty($value)) {
                $user_data[$key] = hash('sha256', $value);
            }
        }
    
        return $user_data;
    }
    
    public static function get_hashed_order_user_data($order_id = null, $user_data = []) {
    
        $order = wc_get_order($order_id);
    
        if(!$order){
            return $user_data;
        }

        $fields = [
            'fn'      => self::normalize_name_fb($order->get_shipping_first_name()),
            'ln'      => self::normalize_name_fb($order->get_shipping_last_name()),
            'ph'      => self::normalize_phone_fb($order->get_shipping_phone()),
            'ct'      => self::normalize_city_fb($order->get_shipping_city()),
            'zp'      => self::normalize_zip_code_fb($order->get_shipping_postcode()),
            'st'   => self::normalize_state_fb(self::get_customer_state($order->get_shipping_country(), $order->get_shipping_state())),
            'country' => self::normalize_country_fb($order->get_shipping_country()),
        ];
    
        foreach ($fields as $key => $value) {
            if ( !empty($value) ) {
                $user_data[$key] = hash('sha256', $value);
            }
        }
    
        $fields = [
            'fn'      => self::normalize_name_fb($order->get_billing_first_name()),
            'ln'      => self::normalize_name_fb($order->get_billing_last_name()),
            'ph'      => self::normalize_phone_fb($order->get_billing_phone()),
            'em'      => self::normalize_email_fb($order->get_billing_email()),
            'ct'      => self::normalize_city_fb($order->get_billing_city()),
            'zp'      => self::normalize_zip_code_fb($order->get_billing_postcode()),
            'st'   => self::normalize_state_fb(self::get_customer_state($order->get_billing_country(), $order->get_billing_state())),
            'country' => self::normalize_country_fb($order->get_billing_country()),
        ];
    
        foreach ($fields as $key => $value) {
            if ( !empty($value) ) {
                $user_data[$key] = hash('sha256', $value);
            }
        }
    
        return $user_data;
    }
    
    public static function get_http_client_data($user_data = []){
        $user_data['client_ip_address'] = $_SERVER['REMOTE_ADDR'];
        $user_data['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    
        return $user_data;
    }

    public static function after_payload_post_data_response_exit_fb(){
        if( /*(get_option('sbsp_show_payload_fb', false) ||
            get_option('sbsp_show_post_data_fb', false) ||
            get_option('sbsp_show_response_fb', false)
            ) &&*/
            get_option('sbsp_after_payload_post_data_response_exit_fb', false)){
            exit;
        }
    }

    public static function get_customer_data_from_db($data_type = null, $data_value = null){
        if(!$data_type || !$data_value){
            return false;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'sbsp_customers_data';

        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE data_type = %s AND data_value = %s", $data_type, $data_value);
        $result = $wpdb->get_row($sql);

        return $result;
    }

    public static function get_customer_access_class($row){
        if( $row && is_object($row) && $row->data_access === 'blocked'){
            return 'sbsp-customer-access-blocked';
        }else{
            return 'sbsp-customer-access-allowed';
        }
    }

    public static function sanitize_phone_number($phone){
        $phone = preg_replace('/^\+?88/', '', $phone); // Remove +88 or 88
        $phone = preg_replace('/^\+/', '', $phone);    // Remove + if still at start
        $phone = preg_replace('/[^0-9]/', '', $phone); // Remove all except numbers

        return $phone;
    }
    
}