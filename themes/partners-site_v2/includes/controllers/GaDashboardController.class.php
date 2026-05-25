<?php

namespace Controllers;
use Classes\MultisiteFixer;

class GaDashboardController {

    private $config = ['host' => 'redis', 'port' => 6379, 'weight' => 1];

    public function getGaApiKey() {
        switch_to_blog(MultisiteFixer::getCurrentBlogId());  
        $gaApiKey = get_field('gSecretKey', 'options-dealer');  
        restore_current_blog();     
        
        if (!$gaApiKey) {
            error_log('Błąd: Brak klucza API w opcjach ACF.');
        }

        return $gaApiKey;
    }

    public function getGaPropertyId() {
        switch_to_blog(MultisiteFixer::getCurrentBlogId());  
        $gaPropertyId = get_field('gId', 'options-dealer');  
        restore_current_blog();  
        
        if (!$gaPropertyId) {
            error_log('Błąd: Brak ID właściwości GA w opcjach ACF.');
        }

        return $gaPropertyId;
    }

    public function getAccessToken() {
        $cachedToken = wp_cache_get('ga_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $jsonKey = $this->getGaApiKey(); 

        if (!$jsonKey) {
            error_log('Brak klucza GA (Google Analytics)');
            return ['error' => 'Brak klucza GA (Google Analytics)'];
        }

        $serviceAccount = json_decode($jsonKey, true);
        if (!isset($serviceAccount['private_key'])) {
            error_log('Błąd: Nieprawidłowy format klucza JSON.');
            return ['error' => 'Błąd: Nieprawidłowy format klucza JSON.'];
        }

        $privateKey = $serviceAccount['private_key'];
        $clientEmail = $serviceAccount['client_email'];
        $tokenUri = 'https://oauth2.googleapis.com/token';

        $assertion = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => $tokenUri,
            'iat' => time(),
            'exp' => time() + 3600, 
        ];

        $jwt = $this->generate_jwt($assertion, $privateKey);

        $response = wp_remote_post($tokenUri, [
            'body' => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
        ]);

        if (is_wp_error($response)) {
            error_log('Błąd cURL: ' . print_r($response->get_error_messages(), true));
            return ['error' => 'Błąd cURL: ' . print_r($response->get_error_messages(), true)];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
       
        if (isset($body['access_token'])) {
            wp_cache_set('ga_access_token', $body['access_token'], '', 3500);
            return $body['access_token'];
        }

        return ['error' => 'Błąd: Nie udało się uzyskać tokena dostępu'];
    }

    public function generate_jwt($assertion, $privateKey) {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $claim = [
            'iss' => $assertion['iss'],
            'scope' => $assertion['scope'],
            'aud' => $assertion['aud'],
            'iat' => $assertion['iat'],
            'exp' => $assertion['exp'],
        ];

        $base64_url_header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64_url_claim = rtrim(strtr(base64_encode(json_encode($claim)), '+/', '-_'), '=');

        $signature = '';
        if (!openssl_sign($base64_url_header . '.' . $base64_url_claim, $signature, openssl_pkey_get_private($privateKey), OPENSSL_ALGO_SHA256)) {
            error_log('Błąd: Nie udało się podpisać JWT.');
        }

        $base64_url_signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return $base64_url_header . '.' . $base64_url_claim . '.' . $base64_url_signature;
    }

    public function getGaData($propertyId) {
        $accessToken = $this->getAccessToken();
        if (isset($accessToken['error'])) {
            return $accessToken; 
        }
    
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runReport";
    
        $data = [
            'dateRanges' => [
                ['startDate' => '7daysAgo', 'endDate' => 'today'],
            ],
            'dimensions' => [
                ['name' => 'date'],
            ],
            'metrics' => [
                ['name' => 'activeUsers'],
                ['name' => 'sessions'],
                ['name' => 'engagementRate'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'engagedSessions'],
            ],
        ];
    
        $args = [
            'method'    => 'POST',
            'headers'   => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ],
            'body'      => json_encode($data),
        ];
    
        $response = wp_remote_post($url, $args);
    
        if (is_wp_error($response)) {
            return ['error' => 'Błąd cURL: ' . print_r($response->get_error_messages(), true)];
        }
    
        $status_code = wp_remote_retrieve_response_code($response);
    
        if ($status_code !== 200) {
            return ['error' => 'Błąd: Zapytanie zwróciło niepoprawny status ' . $status_code];
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        if (isset($data['error'])) {
            return ['error' => 'Błąd: ' . $data['error']['message']];
        }
    
        return $data;
    }

    public function init() {
    
        add_action('wp_dashboard_setup', [__CLASS__, 'register_ga_widget']);
    }

    public static function register_ga_widget() {
        wp_add_dashboard_widget(
            'ds_ga_dashboard_widget',                  
            __('Visitor Statistics (Google Analytics)', 'partners-site_v2'),
            [__CLASS__, 'display_ga_widget'],          
            null,                                      
            null,                                      
            0                                          
        );
    }
    
    public static function display_ga_widget() {
        $gaController = new GaDashboardController();
        $propertyId = $gaController->getGaPropertyId();
        $data = $gaController->getGaData($propertyId);
        
        if (isset($data['error'])) {
            echo '<p><strong>' . esc_html__('An error occurred', 'partners-site_v2') . ':</strong> ' . esc_html($data['error']) . '</p>';
            return;
        }

        if ($data && isset($data['rows'])) {
            usort($data['rows'], function ($a, $b) {
                $dateA = $a['dimensionValues'][0]['value']; 
                $dateB = $b['dimensionValues'][0]['value']; 
                return strcmp($dateB, $dateA); 
            });
    
            $dates = [];
            $activeUsers = [];
            $sessions = [];
            $engagementRate = [];
            $averageSessionDuration = [];
    
            foreach ($data['rows'] as $row) {
                $date = $row['dimensionValues'][0]['value'];
                $dates[] = \DateTime::createFromFormat('Ymd', $date)->format('d.m.Y');
                $activeUsers[] = $row['metricValues'][0]['value'];
                $sessions[] = $row['metricValues'][1]['value'];
                $engagementRate[] = $row['metricValues'][2]['value'];
                $averageSessionDuration[] = isset($row['metricValues'][3]) ? round($row['metricValues'][3]['value'], 1) : 0;
            }
    
            echo '<canvas id="gaChart" style="width:100%;"></canvas>';
            echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
            echo '<script>
                    var ctx = document.getElementById("gaChart").getContext("2d");
                    var gaChart = new Chart(ctx, {
                        type: "line",
                        data: {
                            labels: ' . json_encode($dates) . ',
                            datasets: [{
                                label: ' . json_encode(__('Active users', 'partners-site_v2')) . ',
                                data: ' . json_encode($activeUsers) . ',
                                borderColor: "rgba(75, 192, 192, 1)",
                                backgroundColor: "rgba(0, 120, 125, 0.2)",
                                borderWidth: 3,
                                pointBackgroundColor: "rgba(0, 120, 125, 1)",
                                pointBorderColor: "rgba(255, 255, 255, 1)",
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                fill: true,
                                tension: 0.4
                            }, {
                                label: "Sesje",
                                data: ' . json_encode($sessions) . ',
                                borderColor: "rgba(40, 80, 120, 1)",
                                backgroundColor: "rgba(40, 80, 120, 0.2)",
                                borderWidth: 3,
                                pointBackgroundColor: "rgba(40, 80, 120, 1)",
                                pointBorderColor: "rgba(255, 255, 255, 1)",
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                fill: true,
                                tension: 0.4
                            }, {
                                label: ' . json_encode(__("Engagement", 'partners-site_v2')) . ',
                                data: ' . json_encode($engagementRate) . ',
                                borderColor: "rgba(255, 159, 64, 1)",
                                backgroundColor: "rgba(255, 159, 64, 0.2)",
                                borderWidth: 3,
                                pointBackgroundColor: "rgba(255, 159, 64, 1)",
                                pointBorderColor: "rgba(255, 255, 255, 1)",
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                fill: true,
                                tension: 0.4
                            }, {
                                label: ' . json_encode(__("Average session duration (s)", 'partners-site_v2')) . ',
                                data: ' . json_encode($averageSessionDuration) . ',
                                borderColor: "rgba(54, 162, 235, 1)",
                                backgroundColor: "rgba(54, 162, 235, 0.2)",
                                borderWidth: 3,
                                pointBackgroundColor: "rgba(54, 162, 235, 1)",
                                pointBorderColor: "rgba(255, 255, 255, 1)",
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: "top",
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                  </script>';
                  echo '<button id="toggleTableBtn" style="padding:10px;margin-top:10px;background-color:#284e80;color:white;border:none;border-radius:5px;cursor:pointer;">' . esc_html__('Show statistics', 'partners-site_v2') . '</button>';
            echo '<div id="statsTable" style="display:none;margin-top:20px;">';
            echo '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
                    <tr>
                        <th style="border:1px solid #ddd;padding:8px;">' . json_encode(__('Date', 'partners-site_v2')) . '</th>
                        <th style="border:1px solid #ddd;padding:8px; background-color: rgba(0, 120, 125, 0.2);">' . esc_html__('Active users', 'partners-site_v2') . '</th>
                        <th style="border:1px solid #ddd;padding:8px; background-color: rgba(40, 80, 120, 0.2);">' . esc_html__('Sessions', 'partners-site_v2') . '</th>
                        <th style="border:1px solid #ddd;padding:8px; background-color: rgba(255, 159, 64, 0.2);">' . esc_html__('Engagement (%)', 'partners-site_v2') . '</th>
                        <th style="border:1px solid #ddd;padding:8px; background-color: rgba(54, 162, 235, 0.2);">' . esc_html__('Average session duration (s)', 'partners-site_v2') . '</th>
                    </tr>';

            foreach ($data['rows'] as $index => $row) {
                echo '<tr>';
                echo '<td style="border:1px solid #ddd;padding:8px;">' . esc_html($dates[$index]) . '</td>';
                echo '<td style="border:1px solid #ddd;padding:8px; background-color: rgba(0, 120, 125, 0.2);">' . esc_html($activeUsers[$index]) . '</td>';
                echo '<td style="border:1px solid #ddd;padding:8px; background-color: rgba(40, 80, 120, 0.2);">' . esc_html($sessions[$index]) . '</td>';
                echo '<td style="border:1px solid #ddd;padding:8px; background-color: rgba(255, 159, 64, 0.2);">' . esc_html($engagementRate[$index]) . '</td>';
                echo '<td style="border:1px solid #ddd;padding:8px; background-color: rgba(54, 162, 235, 0.2);">' . esc_html($averageSessionDuration[$index]) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</div>';
            echo 
                '<script>
                    document.getElementById("toggleTableBtn").addEventListener("click", function() {
                        var table = document.getElementById("statsTable");
                        if (table.style.display === "none") {
                            table.style.display = "block";
                            this.innerText = ' . json_encode(__('Hide statistics', 'partners-site_v2'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';
                        } else {
                            table.style.display = "none";
                            this.innerText = ' . json_encode(__('Show statistics', 'partners-site_v2'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';
                        }
                    });
                </script>';
        } else {
            echo '<p>' . esc_html__('No data to display.', 'partners-site_v2') . '</p>';
        }
    }
}
?>
