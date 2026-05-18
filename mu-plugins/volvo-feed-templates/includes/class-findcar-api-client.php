<?php

if (!defined('ABSPATH')) {
    exit;
}

class FindCar_API_Client
{
    const API_VERSION = 'v1';
    const BASE_URL_DEV = 'https://uat.findcar.pl/';
    const BASE_URL_PROD = 'https://findcar.pl';
    const RATE_LIMIT = 10;
    const MAX_RETRIES = 3;

    private $api_key;
    private $location_token;
    private $base_url;
    private $last_request_time = 0;
    private $request_count = 0;
    private $request_count_reset = 0;

    public function __construct($api_key = '', $location_token = '', $is_dev = false)
    {
        $this->api_key = $api_key;
        $this->location_token = $location_token;
        
        $env_value = defined('WP_FINDCAR') ? WP_FINDCAR : (isset($_ENV['WP_FINDCAR']) ? $_ENV['WP_FINDCAR'] : '');
        $is_dev = ($env_value === 'stage' || $env_value === 'dev' || $env_value === 'uat');
        
        $this->base_url = $is_dev ? self::BASE_URL_DEV : self::BASE_URL_PROD;
        
        $log_file = '/www/wwwroot/main-stage.volvotest.pl/web/debug-findcar.log';
        $log_msg = '[' . date('Y-m-d H:i:s') . '] ENV: WP_FINDCAR=' . var_export($env_value, true) . ', is_dev=' . var_export($is_dev, true) . ', base_url=' . $this->base_url . ', api_key=' . (empty($api_key) ? 'EMPTY' : substr($api_key, 0, 10) . '...') . "\n";
        @file_put_contents($log_file, $log_msg, FILE_APPEND);
       
    }

    public function set_credentials($api_key, $location_token = '')
    {
        $this->api_key = $api_key;
        $this->location_token = $location_token;
    }

    public function is_configured()
    {
        return !empty($this->api_key);
    }

    private function get_headers()
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

if (!empty($this->location_token)) {
            $headers['X-Location-Auth-Token'] = $this->location_token;
        }
        
        $log_file = '/www/wwwroot/main-stage.volvotest.pl/web/debug-findcar.log';
        $log_msg = '[' . date('Y-m-d H:i:s') . '] get_headers: api_key=' . (empty($this->api_key) ? 'EMPTY' : substr($this->api_key, 0, 10) . '...') . ', location_token=' . (empty($this->location_token) ? 'EMPTY' : 'SET') . "\n";
        @file_put_contents($log_file, $log_msg, FILE_APPEND);

        return $headers;
    }

    private function rate_limit_wait()
    {
        $now = microtime(true);
        if ($now - $this->request_count_reset >= 1) {
            $this->request_count = 0;
            $this->request_count_reset = $now;
        }

        if ($this->request_count >= self::RATE_LIMIT) {
            $wait_time = 1 - ($now - $this->request_count_reset);
            if ($wait_time > 0) {
                usleep($wait_time * 1000000);
            }
            $this->request_count = 0;
            $this->request_count_reset = microtime(true);
        }

        $this->request_count++;
    }

    private function request($method, $endpoint, $data = null, $retry_count = 0)
    {
        $this->rate_limit_wait();

        $url = $this->base_url . '/api/v1/partner' . $endpoint;
        $args = [
            'method' => strtoupper($method),
            'headers' => $this->get_headers(),
            'timeout' => 30,
            'sslverify' => true,
        ];

        if ($data !== null) {
            $args['body'] = json_encode($data);
        }

        $curl_cmd = "curl -X " . strtoupper($method) . " '" . $url . "'";
        foreach ($args['headers'] as $k => $v) {
            $curl_cmd .= " -H '" . $k . ": " . $v . "'";
        }
        if (isset($args['body'])) {
            $curl_cmd .= " -d '" . $args['body'] . "'";
        }
        
        $log_file = '/www/wwwroot/main-stage.volvotest.pl/web/debug-findcar.log';
        $log_msg = '[' . date('Y-m-d H:i:s') . '] CURL: ' . $curl_cmd . "\n";
        file_put_contents($log_file, $log_msg, FILE_APPEND);

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            if ($retry_count < self::MAX_RETRIES) {
                $wait_time = pow(2, $retry_count);
                sleep($wait_time);
                return $this->request($method, $endpoint, $data, $retry_count + 1);
            }
            return new WP_Error('findcar_api_error', $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);

        $result = [
            'status_code' => $status_code,
            'body' => $body,
            'headers' => wp_remote_retrieve_headers($response),
        ];

        if ($status_code >= 400) {
            $error_data = json_decode($body, true);
            $error_message = 'API Error';

            if (isset($error_data['errors']) && is_array($error_data['errors'])) {
                $error_messages = [];
                foreach ($error_data['errors'] as $error) {
                    $error_messages[] = $error['message'] ?? $error['code'] ?? 'Unknown error';
                }
                $error_message = implode('; ', $error_messages);
            } elseif (isset($error_data['message'])) {
                $error_message = $error_data['message'];
            }

            if (in_array($status_code, [429, 500, 502, 503]) && $retry_count < self::MAX_RETRIES) {
                $wait_time = pow(2, $retry_count);
                sleep($wait_time);
                return $this->request($method, $endpoint, $data, $retry_count + 1);
            }

            return new WP_Error('findcar_api_error', $error_message, $result);
        }

        if (!empty($body)) {
            $result['data'] = json_decode($body, true);
        }

        return $result;
    }

    public function test_connection()
    {
        $response = $this->request('GET', '/location');


       
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return [
                'success' => true,
                'message' => 'Połączenie aktywne',
                'data' => $response['data'],
            ];
        }

        return [
            'success' => false,
            'message' => 'Nieoczekiwana odpowiedź: ' . $response['status_code'],
        ];
    }

    public function get_location()
    {
        $response = $this->request('GET', '/location');

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się pobrać lokalizacji');
    }

    public function get_dealership()
    {
        $response = $this->request('GET', '/dealership');

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się pobrać danych dealera');
    }

    public function get_locations()
    {
        $response = $this->request('GET', '/dealership/locations');

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się pobrać lokalizacji');
    }

    public function get_dictionaries()
    {
        $response = $this->request('GET', '/dictionaries');

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się pobrać słowników');
    }

    public function get_makes_models()
    {
        $response = $this->request('GET', '/makes-models');

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się pobrać marek i modeli');
    }

    public function create_listing($location_id, $partner_listing_id, $listing_data)
    {
        $endpoint = '/locations/' . rawurlencode($location_id) . '/listings';
        
        $payload = [
            'partnerListingId' => $partner_listing_id,
            'carListing' => $listing_data,
        ];

        $response = $this->request('POST', $endpoint, $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 201 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się utworzyć ogłoszenia');
    }

    public function create_listing_by_inventory($inventory_id, $partner_listing_id, $listing_data)
    {
        $endpoint = '/locations/' . rawurlencode($inventory_id) . '/listings';
        
        $payload = [
            'partnerListingId' => $partner_listing_id,
            'carListing' => $listing_data,
        ];

        $response = $this->request('POST', $endpoint, $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 201 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się utworzyć ogłoszenia');
    }

    public function update_listing($location_id, $partner_listing_id, $listing_data)
    {
        $endpoint = '/locations/' . rawurlencode($location_id) . '/listings/' . rawurlencode($partner_listing_id);

        $payload = [
            'carListing' => $listing_data,
        ];

        $response = $this->request('PUT', $endpoint, $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się zaktualizować ogłoszenia');
    }

    public function delete_listing($location_id, $partner_listing_id)
    {
        $endpoint = '/locations/' . rawurlencode($location_id) . '/listings/' . rawurlencode($partner_listing_id);

        $response = $this->request('DELETE', $endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 204) {
            return true;
        }

        return new WP_Error('findcar_api_error', 'Nie udało się usunąć ogłoszenia');
    }

    public function get_listing($location_id, $partner_listing_id)
    {
        $endpoint = '/locations/' . rawurlencode($location_id) . '/listings/' . rawurlencode($partner_listing_id);

        $response = $this->request('GET', $endpoint);

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 200 && isset($response['data'])) {
            return $response['data'];
        }

        if ($response['status_code'] === 404) {
            return new WP_Error('findcar_not_found', 'Ogłoszenie nie zostało znalezione');
        }

        return new WP_Error('findcar_api_error', 'Nie udało się pobrać ogłoszenia');
    }

    public function create_listing_brand_new($location_id, $partner_listing_id, $listing_data)
    {
        $endpoint = '/locations/' . rawurlencode($location_id) . '/brand-new';
        
        $payload = [
            'partnerListingId' => $partner_listing_id,
            'carListing' => $listing_data,
        ];

        $response = $this->request('POST', $endpoint, $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 201 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się utworzyć ogłoszenia nowego samochodu');
    }

    public function create_listing_pre_owned($location_id, $partner_listing_id, $listing_data)
    {
        $endpoint = '/locations/' . rawurlencode($location_id) . '/pre-owned';
        
        $payload = [
            'partnerListingId' => $partner_listing_id,
            'carListing' => $listing_data,
        ];

        $response = $this->request('POST', $endpoint, $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        if ($response['status_code'] === 201 && isset($response['data'])) {
            return $response['data'];
        }

        return new WP_Error('findcar_api_error', 'Nie udało się utworzyć ogłoszenia używanego samochodu');
    }
}