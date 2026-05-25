<?php

/**
 * Klasa obslugujaca API otomoto
 *
 */
class CarOtoMoto {

    const URL_API = 'https://www.otomoto.pl/api/open';

    private $client_id = '';
    private $username = '';
    private $password = '';
    private $access_token = '';
    private $refresh_token = '';
    private $enabled = false;

    public function __construct($username = null, $password = null, $client_id = null) {
        $this->loadSettings();

        if (!$this->enabled) {
            return;
        }

        if (null != $username && null != $password) {
            $this->username = $username;
            $this->password = $password;
        }

        if (null != $client_id) {
            $this->client_id = $client_id;
        }

        if (empty($this->username) || empty($this->password) || empty($this->client_id)) {
            return;
        }

        $this->authenticate();
    }

    private function loadSettings() {
        if (function_exists('get_field')) {
            $settings = get_field('otomoto_settings', 'options-dealer');
            if ($settings && isset($settings['otomoto_enabled']) && $settings['otomoto_enabled']) {
                $this->enabled = true;
                $this->username = $settings['otomoto_username'] ?? '';
                $this->password = $settings['otomoto_password'] ?? '';
                $client_id = $settings['otomoto_client_id'] ?? '';
                $client_secret = $settings['otomoto_client_secret'] ?? '';
                $this->client_id = $client_id . ':' . $client_secret;
            }
        }
    }

    private function authenticate() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL_API."/oauth/token");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'User-Agent: ' . $this->username,
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "grant_type=password&username=".rawurlencode($this->username)."&password=".rawurlencode($this->password));
        curl_setopt($ch, CURLOPT_USERPWD, $this->client_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $head = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json_decoded = json_decode($head, true);

        if ($httpCode >= 400 || empty($json_decoded['access_token'])) {
            error_log('[Otomoto Auth] Failed: HTTP ' . $httpCode . ' response=' . $head);
        }

        $this->access_token = $json_decoded['access_token'] ?? '';
        $this->refresh_token = $json_decoded['refresh_token'] ?? '';
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function isAuthenticated() {
        return !empty($this->access_token);
    }

    public function getAccessToken() {
        return $this->access_token;
    }

    private function apiRequest($url, $headers = [], $method = 'GET', $body = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token,
            'User-Agent: ' . $this->username,
        ], $headers));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'response' => $response,
            'httpCode' => $httpCode,
        ];
    }

    public function getRecords($limit = 0, $page = 1) {
        $url = (0 == $limit) ?
            self::URL_API."/account/adverts" :
            self::URL_API."/account/adverts?limit=$limit&page=$page";
        $result = $this->apiRequest($url);
        return json_decode($result['response'], true);
    }

    public function testConnection() {
        $result = $this->apiRequest(self::URL_API . '/account/status');
        $data = json_decode($result['response'], true);

        if ($result['httpCode'] >= 400) {
            $message = $data['error']['message'] ?? ('Connection test failed. HTTP ' . $result['httpCode']);
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data]);
        }

        return $data;
    }

    public function getRegions() {
        $result = $this->apiRequest(self::URL_API . '/regions');
        $data = json_decode($result['response'], true);

        if ($result['httpCode'] >= 400) {
            $message = $data['error']['message'] ?? ('Failed to fetch regions. HTTP ' . $result['httpCode']);
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data]);
        }

        return $data;
    }

    public function getCities($regionId) {
        $result = $this->apiRequest(self::URL_API . '/regions/' . intval($regionId) . '/cities');
        $data = json_decode($result['response'], true);

        if ($result['httpCode'] >= 400) {
            $message = $data['error']['message'] ?? ('Failed to fetch cities. HTTP ' . $result['httpCode']);
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data]);
        }

        return $data;
    }

    public function getDistricts($cityId) {
        $result = $this->apiRequest(self::URL_API . '/cities/' . intval($cityId) . '/districts');
        $data = json_decode($result['response'], true);

        if ($result['httpCode'] >= 400) {
            $message = $data['error']['message'] ?? ('Failed to fetch districts. HTTP ' . $result['httpCode']);
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data]);
        }

        return $data;
    }

    public function createImageCollection() {
        $result = $this->apiRequest(self::URL_API . '/account/image-collections', [], 'POST', json_encode([]));
        $data = json_decode($result['response'], true);

        if ($result['httpCode'] >= 400 || empty($data['id'])) {
            $message = $data['error']['message'] ?? ('Failed to create image collection. HTTP ' . $result['httpCode']);
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data]);
        }

        return $data;
    }

    public function addImageToCollection($collectionId, $imageUrl) {
        $tmpFile = download_url($imageUrl);
        if (is_wp_error($tmpFile)) {
            return $tmpFile;
        }

        $fileName = basename(parse_url($imageUrl, PHP_URL_PATH)) ?: 'image.jpg';
        $mimeType = wp_get_image_mime($tmpFile) ?: 'image/jpeg';

        $boundary = '----WebKitFormBoundary' . uniqid();
        $body = "--" . $boundary . "\r\n";
        $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . $fileName . "\"\r\n";
        $body .= "Content-Type: " . $mimeType . "\r\n\r\n";
        $body .= file_get_contents($tmpFile) . "\r\n";
        $body .= "--" . $boundary . "--\r\n";

        @unlink($tmpFile);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL_API . '/account/image-collections/' . rawurlencode($collectionId) . '/images');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data; boundary=' . $boundary,
            'Authorization: Bearer ' . $this->access_token,
            'User-Agent: ' . $this->username,
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            $message = $data['error']['message'] ?? ('Failed to upload image. HTTP ' . $httpCode);
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data]);
        }

        return $data;
    }

    public function createAdvert($payload) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL_API . '/account/adverts?v=2');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token,
            'User-Agent: ' . $this->username,
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            $message = $data['error']['message'] ?? ('Failed to create advert. HTTP ' . $httpCode);
            if (!empty($data['error']['details']) && is_array($data['error']['details'])) {
                $details = [];
                foreach ($data['error']['details'] as $field => $error) {
                    $details[] = $field . ': ' . $error;
                }
                $message .= ' (' . implode('; ', $details) . ')';
            }
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data, 'status_code' => $httpCode]);
        }

        return $data;
    }

    public function deleteAdvert($advertId) {
        $result = $this->apiRequest(self::URL_API . '/account/adverts/' . rawurlencode($advertId), [], 'DELETE');
        $data = json_decode($result['response'], true);

        if ($result['httpCode'] >= 400) {
            $message = $data['error']['message'] ?? (__('The listing could not be deleted.', 'partners-site_v2') . ' HTTP ' . $result['httpCode']);
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data]);
        }

        return true;
    }

    public function updateAdvert($advertId, $payload) {
        $result = $this->apiRequest(self::URL_API . '/account/adverts/' . rawurlencode($advertId), [], 'PUT', $payload);
        $data = json_decode($result['response'], true);

        if ($result['httpCode'] >= 400) {
            $message = $data['error']['message'] ?? (__('The listing could not be updated.', 'partners-site_v2') . ' HTTP ' . $result['httpCode']);
            if (!empty($data['error']['details']) && is_array($data['error']['details'])) {
                $details = [];
                foreach ($data['error']['details'] as $field => $error) {
                    $details[] = $field . ': ' . $error;
                }
                $message .= ' (' . implode('; ', $details) . ')';
            }
            return new \WP_Error('otomoto_api_error', $message, ['response' => $data, 'status_code' => $result['httpCode']]);
        }

        return $data;
    }
}
