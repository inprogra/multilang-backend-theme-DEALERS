<?php

if (!defined('ABSPATH')) {
    exit;
}
use function Env\env;
class FindCar_Sync_Manager
{
    private $api_client;
    private $data_mapper;

    public function __construct()
    {
        $this->data_mapper = new FindCar_Data_Mapper();
    }
    private $find_car_env;
    public function init_hooks()
    {
        add_filter('bulk_actions-edit-stock-car', [$this, 'register_bulk_actions']);
        add_filter('handle_bulk_actions-edit-stock-car', [$this, 'handle_bulk_action'], 10, 3);
        
        if (!get_field('findcar_enabled', 'options-dealer')) {
            return;
        }
        
        $env_value = defined('WP_FINDCAR') ? WP_FINDCAR : (isset($_ENV['WP_FINDCAR']) ? $_ENV['WP_FINDCAR'] : (function_exists('env') ? env('WP_FINDCAR') : ''));
        $this->find_car_env = ($env_value === 'stage' || $env_value === 'dev' || $env_value === 'uat');
     
        add_action('transition_post_status', [$this, 'handle_status_change'], 10, 3);
        add_action('save_post_stock-car', [$this, 'handle_car_save'], 20, 3);
        add_action('trashed_post', [$this, 'handle_car_trash']);
        add_action('delete_post', [$this, 'handle_car_delete']);
        
        add_action('findcar_pull_sync_event', [$this, 'pull_sync_all']);
        add_action('findcar_auto_sync_all_event', [$this, 'enable_and_sync_all_existing']);
        
        if (!wp_next_scheduled('findcar_pull_sync_event')) {
            wp_schedule_event(time(), 'hourly', 'findcar_pull_sync_event');
        }
        
        add_filter('acf/validate_save_post', [$this, 'validate_dealer_options']);
    }

    public function register_bulk_actions($bulk_actions)
    {
        $dealer_enabled = get_field('findcar_enabled', 'options-dealer');
        
        if (!$dealer_enabled) {
            $showrooms = get_posts([
                'post_type' => 'showroom',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
            ]);
            
            $has_showroom_enabled = false;
            foreach ($showrooms as $showroom_id) {
                if (get_field('findcar_enabled', $showroom_id)) {
                    $has_showroom_enabled = true;
                    break;
                }
            }
            
            if (!$has_showroom_enabled) {
                return $bulk_actions;
            }
        }
        
        $bulk_actions['findcar_sync'] = __('Send to FindCar', 'volvo-feed-templates');
        $bulk_actions['findcar_preview'] = 'FindCar - ' . __('Preview', 'volvo-feed-templates');
        return $bulk_actions;
    }

    public function handle_bulk_action($redirect_to, $action, $post_ids)
    {
        if ($action === 'findcar_preview') {
            $preview_data = $this->get_bulk_preview_data($post_ids);
            $redirect_to = add_query_arg([
                'findcar_preview' => 1,
                'findcar_preview_data' => base64_encode(json_encode($preview_data)),
            ], $redirect_to);
            return $redirect_to;
        }
        
        if ($action !== 'findcar_sync') {
            return $redirect_to;
        }
        
        $synced_count = 0;
        $error_count = 0;
        $skip_count = 0;
        $errors = [];
        
        $dealer_enabled = get_field('findcar_enabled', 'options-dealer');
        $dealer_api_key = get_field('findcar_api_key', 'options-dealer');        
        $dealer_location_id = get_field('findcar_location_id', 'options-dealer');
        $dealer_location_token = get_field('findcar_location_token', 'options-dealer');
        
        foreach ($post_ids as $car_id) {
            $showroom_id = get_field('showroom', $car_id);
            
            if ($showroom_id) {
                $showroom_findcar_enabled = get_field('findcar_enabled', $showroom_id);
                if ($showroom_findcar_enabled) {
                    $missing_fields = $this->data_mapper->validate_car_fields($car_id);
                    if (empty($missing_fields)) {
                        update_field('findcar_enabled', true, $car_id);
                    }
                } elseif ($dealer_enabled && !empty($dealer_api_key) && !empty($dealer_location_id)) {
                    $missing_fields = $this->data_mapper->validate_car_fields($car_id);
                    if (empty($missing_fields)) {
                        update_field('findcar_enabled', true, $car_id);
                    }
                }
            } elseif ($dealer_enabled && !empty($dealer_api_key) && !empty($dealer_location_id)) {
                $missing_fields = $this->data_mapper->validate_car_fields($car_id);
                if (empty($missing_fields)) {
                    update_field('findcar_enabled', true, $car_id);
                }
            }
           
            $result = $this->sync_car($car_id, true, true);
            
            if (is_wp_error($result)) {
                $error_count++;
                $car_title = get_post($car_id)->post_title ?? 'Car #' . $car_id;
                $errors[] = $car_title . ': ' . $result->get_error_message();
            } else {
                $synced_count++;
            }
        }
        
        $redirect_to = add_query_arg([
            'findcar_bulk_synced' => $synced_count,
            'findcar_bulk_errors' => $error_count,
            'findcar_bulk_skipped' => $skip_count,
            'findcar_bulk_error_details' => urlencode(implode('; ', array_slice($errors, 0, 5))),
        ], $redirect_to);
        
        return $redirect_to;
    }

    public function get_bulk_preview_data($post_ids)
    {
        $ready_to_sync = 0;
        $missing_fields = 0;
        $cars_missing_info = [];
        $enabled_count = 0;
        
        foreach ($post_ids as $car_id) {
            $findcar_enabled = get_field('findcar_enabled', $car_id);
            
            if (!$findcar_enabled) {
                continue;
            }
            
            $enabled_count++;
            $car_missing = $this->data_mapper->validate_car_fields($car_id);
            
            if (empty($car_missing)) {
                $ready_to_sync++;
            } else {
                $missing_fields++;
                $cars_missing_info[] = [
                    'car_id' => $car_id,
                    'car_title' => get_the_title($car_id),
                    'missing' => $car_missing,
                ];
            }
        }
        
        return [
            'total_selected' => count($post_ids),
            'total_enabled' => $enabled_count,
            'ready_to_sync' => $ready_to_sync,
            'missing_fields' => $missing_fields,
            'cars_missing_info' => $cars_missing_info,
        ];
    }

    public function validate_dealer_options()
    {
        if (!isset($_POST['_acf_screen']) || $_POST['_acf_screen'] !== 'options') {
            return;
        }
        
        $enabled = isset($_POST['acf']['field_findcar_dealer_enabled']) ? $_POST['acf']['field_findcar_dealer_enabled'] : 0;
        
        if ($enabled) {
            $api_key = isset($_POST['acf']['field_findcar_dealer_api_key']) ? $_POST['acf']['field_findcar_dealer_api_key'] : '';
            
            if (empty($api_key)) {
                acf_add_validation_error('acf[field_findcar_dealer_api_key]', __('API key is required', 'volvo-feed-templates'));
            }
        }
    }

    private function is_dealer_auto_sync_enabled()
    {
        return get_field('findcar_auto_sync', 'options-dealer');
    }

    private function is_car_explicitly_disabled($car_id)
    {
        $raw = get_post_meta($car_id, 'findcar_enabled', true);
        return ($raw === '0' || $raw === 0 || $raw === false);
    }

    public function handle_status_change($new_status, $old_status, $post)
    {
        if ($post->post_type !== 'stock-car') {
            return;
        }

        if ($new_status === 'publish' && $old_status !== 'publish') {
            if ($this->is_dealer_auto_sync_enabled() && !$this->is_car_explicitly_disabled($post->ID)) {
                update_field('findcar_enabled', true, $post->ID);
            }
            $this->sync_car($post->ID);
        }
    }

    public function handle_car_save($post_id, $post, $update)
    {
        if ($post->post_type !== 'stock-car') {
            return;
        }

        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if ($post->post_status !== 'publish') {
            return;
        }

        if (!$update) {
            return;
        }

        $findcar_enabled = get_field('findcar_enabled', $post_id);
        $dealer_auto_sync = $this->is_dealer_auto_sync_enabled();
        $explicitly_disabled = $this->is_car_explicitly_disabled($post_id);

        if (!$findcar_enabled) {
            if ($dealer_auto_sync && !$explicitly_disabled) {
                update_field('findcar_enabled', true, $post_id);
                $this->sync_car($post_id);
                return;
            }

            $existing_listing_id = get_field('findcar_listing_id', $post_id);
            if (!empty($existing_listing_id)) {
                $this->delete_listing($post_id);
            }
            return;
        }

        if ($dealer_auto_sync) {
            $this->sync_car($post_id);
            return;
        }

        $showroom_id = get_field('showroom', $post_id);
        if (!$showroom_id) {
            return;
        }

        $showroom_findcar_enabled = get_field('findcar_enabled', $showroom_id);
        if (!$showroom_findcar_enabled) {
            return;
        }

        $this->sync_car($post_id);
    }

    public function handle_car_trash($post_id)
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'stock-car') {
            return;
        }

        $this->delete_listing($post_id);
    }

    public function handle_car_delete($post_id)
    {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'stock-car') {
            return;
        }

        $this->delete_listing($post_id);
    }

    public function sync_car($car_id, $force = false, $skip_enabled_check = false)
    {
        $findcar_enabled = get_field('findcar_enabled', $car_id);
        $dealer_auto_sync = $this->is_dealer_auto_sync_enabled();

        if (!$skip_enabled_check && !$findcar_enabled) {
            $existing_listing_id = get_field('findcar_listing_id', $car_id);
            if (!empty($existing_listing_id)) {
                return $this->delete_listing($car_id);
            }
            return new WP_Error('findcar_skip', __('The car is not included in FindCar synchronization', 'volvo-feed-templates'));
        }

        $showroom_id = get_field('showroom', $car_id);

        $api_key = get_field('findcar_api_key', 'options-dealer');
        $location_id = get_field('findcar_location_id', 'options-dealer');
        $location_token = get_field('findcar_location_token', 'options-dealer');
        
        error_log('[FindCar] Sync Car - Global credentials check: api_key=' . (empty($api_key) ? 'EMPTY' : substr($api_key, 0, 8) . '...') . ', location_id=' . (empty($location_id) ? 'EMPTY' : substr($location_id, 0, 20) . '...') . ', find_car_env=' . var_export($this->find_car_env, true));
         
      
        if (empty($api_key) || empty($location_id) || empty($api_key)) {
            return new WP_Error('findcar_error', __('No credentials', 'volvo-feed-templates'));
        }

        $missing_fields = $this->data_mapper->validate_car_fields($car_id);
        if (!empty($missing_fields)) { 
            $error_msg = __('Missing required fields', 'volvo-feed-templates') . ': ' . implode(', ', $missing_fields);
            update_field('findcar_sync_error', $error_msg, $car_id);
            return new WP_Error('findcar_validation_error', $error_msg);
        }

        $listing_data = $this->data_mapper->map_car_to_listing($car_id, $showroom_id);
        
      
        if (is_wp_error($listing_data)) {
            update_field('findcar_sync_error', $listing_data->get_error_message(), $car_id);
            return $listing_data;
        }

        $log_file = '/www/wwwroot/main-stage.volvotest.pl/web/debug-findcar.log';
            
        $client = new FindCar_API_Client($api_key, $location_token, $this->find_car_env);
            
        $log_msg = '[' . date('Y-m-d H:i:s') . '] Creating client with find_car_env=' . var_export($this->find_car_env, true) . ', api_key=' . (empty($api_key) ? 'EMPTY' : substr($api_key, 0, 10) . '...') . ', location_token=' . (empty($location_token) ? 'EMPTY' : 'SET') . "\n";
        file_put_contents($log_file, $log_msg, FILE_APPEND);
            
        $partner_listing_id = $this->data_mapper->get_car_listing_id($car_id);
            
        $existing_listing_id = get_field('findcar_listing_id', $car_id);
            
        if (!empty($existing_listing_id)) {
            $result = $client->update_listing($location_id, $partner_listing_id, $listing_data);
        } else {
            $result = $client->create_listing($location_id, $partner_listing_id, $listing_data);
        }

        if (is_wp_error($result)) {
            update_field('findcar_sync_error', $result->get_error_message(), $car_id);
            return $result;
        }

        if (isset($result['id'])) {
            update_field('findcar_listing_id', $result['id'], $car_id);
        }

        if (isset($result['publicListingNumber'])) {
            update_field('findcar_listing_number', $result['publicListingNumber'], $car_id);
        }

        if (isset($result['listingUrl'])) {
            update_field('findcar_listing_url', $result['listingUrl'], $car_id);
        }

        update_field('findcar_last_sync', current_time('mysql'), $car_id);
        update_field('findcar_sync_error', '', $car_id);

        do_action('findcar_car_synced', $car_id, $result);

        return $result;
    }

    public function delete_listing($car_id)
    {
        $dealer_auto_sync = $this->is_dealer_auto_sync_enabled();
        $showroom_id = get_field('showroom', $car_id);

        if (!$dealer_auto_sync && !$showroom_id) {
            return false;
        }

        if ($dealer_auto_sync) {
            $api_key = get_field('findcar_api_key', 'options-dealer');
            $location_id = get_field('findcar_location_id', 'options-dealer');
            $location_token = get_field('findcar_location_token', 'options-dealer');
        } else {
            $api_key = get_field('findcar_api_key', $showroom_id);
            $location_id = get_field('findcar_location_id', $showroom_id);
            $location_token = get_field('findcar_location_token', $showroom_id);
        }

        if (empty($api_key) || empty($location_id)) {
            return false;
        }

        $partner_listing_id = $this->data_mapper->get_car_listing_id($car_id);
        
        $client = new FindCar_API_Client($api_key, $location_token, $this->find_car_env);
        $result = $client->delete_listing($location_id, $partner_listing_id);

        if (!is_wp_error($result)) {
            update_field('findcar_listing_id', '', $car_id);
            update_field('findcar_listing_number', '', $car_id);
            update_field('findcar_listing_url', '', $car_id);
            
            return true;
        }

        return false;
    }

    public function pull_sync_car($car_id)
    {
        $dealer_auto_sync = $this->is_dealer_auto_sync_enabled();
        $showroom_id = get_field('showroom', $car_id);

        if (!$dealer_auto_sync && !$showroom_id) {
            return new WP_Error('findcar_error', __('No showroom', 'volvo-feed-templates'));
        }

        if ($dealer_auto_sync) {
            $api_key = get_field('findcar_api_key', 'options-dealer');
            $location_id = get_field('findcar_location_id', 'options-dealer');
            $location_token = get_field('findcar_location_token', 'options-dealer');
        } else {
            $api_key = get_field('findcar_api_key', $showroom_id);
            $location_id = get_field('findcar_location_id', $showroom_id);
            $location_token = get_field('findcar_location_token', $showroom_id);
        }

        if (empty($api_key) || empty($location_id)) {
            return new WP_Error('findcar_error', __('No credentials', 'volvo-feed-templates'));
        }

        $listing_id = get_field('findcar_listing_id', $car_id);
        if (empty($listing_id)) {
            return new WP_Error('findcar_error', __('No offer ID', 'volvo-feed-templates'));
        }

        $client = new FindCar_API_Client($api_key, $location_token, $this->find_car_env);
        $result = $client->get_listing($location_id, $listing_id);

        if (is_wp_error($result)) {
            return $result;
        }

        $mapped_data = $this->data_mapper->map_listing_to_car($result);
        
        if (isset($mapped_data['listing_status'])) {
            update_post_meta($car_id, 'findcar_listing_status', $mapped_data['listing_status']);
        }

        if (isset($mapped_data['status'])) {
            update_post_meta($car_id, 'findcar_status', $mapped_data['status']);
        }

        update_post_meta($car_id, 'findcar_last_pull', current_time('mysql'));

        return $result;
    }

    public function pull_sync_all()
    {
        $args = [
            'post_type' => 'stock-car',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'findcar_enabled',
                    'value' => '1',
                    'compare' => '=',
                ],
                [
                    'key' => 'findcar_listing_id',
                    'value' => '',
                    'compare' => '!=',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $synced_count = 0;
        $error_count = 0;
        $errors = [];

        foreach ($query->posts as $car_id) {
            $result = $this->pull_sync_car($car_id);
            
            if (is_wp_error($result)) {
                $error_count++;
                $errors[] = [
                    'car_id' => $car_id,
                    'error' => $result->get_error_message(),
                ];
            } else {
                $synced_count++;
            }
        }

        return [
            'synced' => $synced_count,
            'errors' => $error_count,
            'error_details' => $errors,
        ];
    }

    public function sync_all_for_showroom($showroom_id, $force = false)
    {
        $showroom_findcar_enabled = get_field('findcar_enabled', $showroom_id);
        
        if (!$showroom_findcar_enabled) {
            return new WP_Error('findcar_error', __('FindCar integration is not enabled for this showroom', 'volvo-feed-templates'));
        }

        $args = [
            'post_type' => 'stock-car',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'showroom',
                    'value' => $showroom_id,
                    'compare' => '=',
                ],
                [
                    'key' => 'findcar_enabled',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $synced_count = 0;
        $error_count = 0;
        $errors = [];

        foreach ($query->posts as $car_id) {
            $result = $this->sync_car($car_id, $force);
            
            if (is_wp_error($result)) {
                $error_count++;
                $errors[] = [
                    'car_id' => $car_id,
                    'error' => $result->get_error_message(),
                ];
            } else {
                $synced_count++;
            }
        }

        return [
            'synced' => $synced_count,
            'errors' => $error_count,
            'error_details' => $errors,
            'total' => count($query->posts),
        ];
    }

    public function enable_existing_cars($showroom_id)
    {
        $args = [
            'post_type' => 'stock-car',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'key' => 'showroom',
                'value' => $showroom_id,
                'compare' => '=',
            ],
        ];

        $query = new WP_Query($args);
        $enabled_count = 0;
        $skipped_count = 0;

        $mapper = new FindCar_Data_Mapper();

        foreach ($query->posts as $car_id) {
            $already_enabled = get_field('findcar_enabled', $car_id);
            
            if ($already_enabled) {
                $skipped_count++;
                continue;
            }

            $missing_fields = $mapper->validate_car_fields($car_id);
            
            if (empty($missing_fields)) {
                update_field('findcar_enabled', true, $car_id);
                $enabled_count++;
            } else {
                $skipped_count++;
            }
        }

        return [
            'enabled' => $enabled_count,
            'skipped' => $skipped_count,
            'total' => count($query->posts),
        ];
    }

    public function test_connection($showroom_id)
    {
        $api_key = get_field('findcar_api_key', $showroom_id);
        $location_token = get_field('findcar_location_token', $showroom_id);

        if (empty($api_key)) {
            return [
                'success' => false,
                'message' => 'Brak API Key',
            ];
        }

        $client = new FindCar_API_Client($api_key, $location_token, $this->find_car_env);
        $result = $client->test_connection();

        if (isset($result['success']) && $result['success']) {
            update_field('findcar_connection_status', 'Aktywne - ' . date('Y-m-d H:i'), $showroom_id);
        } else {
            $message = isset($result['message']) ? $result['message'] : 'Błąd połączenia';
            update_field('findcar_connection_status', 'Błąd: ' . $message, $showroom_id);
        }

        return $result;
    }

    public static function handle_ajax_test_connection()
    {
        check_ajax_referer('findcar_nonce', 'nonce');
        
        $dealer_key = isset($_POST['dealer_key']) ? sanitize_text_field($_POST['dealer_key']) : 'options';
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : get_current_blog_id();
       
        if ($dealer_key === 'options') {
           

            $api_key = get_field('findcar_api_key','options-dealer');           
            $location_token = get_field('findcar_location_token','options-dealer');
            $location_id = get_field('findcar_location_id','options-dealer');
            $enabled = get_field('findcar_enabled','options-dealer');            
           
            if (!$enabled) {
                restore_current_blog();
                wp_send_json_error(['message' => __('FindCar integration is not enabled', 'volvo-feed-templates')]);
            }
            
            if (empty($api_key)) {
                restore_current_blog();
                wp_send_json_error(['message' => __('No API key', 'volvo-feed-templates')]);
            }

            $client = new FindCar_API_Client($api_key, $location_token, true);
            $result = $client->test_connection();

            if (isset($result['success']) && $result['success']) {
                update_field('findcar_connection_status', 'Aktywne - ' . date('Y-m-d H:i'),'options-dealer');
                restore_current_blog();
                wp_send_json_success($result);
            } else {
                $message = isset($result['message']) ? $result['message'] : 'Błąd połączenia';
                update_field('findcar_connection_status', 'Błąd: ' . $message,'options-dealer');
                restore_current_blog();
                wp_send_json_error(['message' => $message]);
            }
        } else {
            $showroom_id = intval($dealer_key);
            $manager = new self();
            $result = $manager->test_connection($showroom_id);
            
            if (isset($result['success']) && $result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error(['message' => $result['message'] ?? __('Connection error', 'volvo-feed-templates')]);
            }
        }
    }

    public static function handle_ajax_sync_all()
    {
        check_ajax_referer('findcar_nonce', 'nonce');
        
        $dealer_key = isset($_POST['dealer_key']) ? sanitize_text_field($_POST['dealer_key']) : 'options';
        $showroom_id = isset($_POST['showroom_id']) ? intval($_POST['showroom_id']) : 0;
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : get_current_blog_id();
        
        if ($dealer_key === 'options' && $showroom_id <= 0) {
            switch_to_blog($blog_id);
            $enabled = get_field('findcar_enabled', 'options-dealer');
            
            if (!$enabled) {
                restore_current_blog();
                wp_send_json_error(['message' => __('FindCar integration is not enabled', 'volvo-feed-templates')]);
            }

            $result = self::sync_all_for_dealer();
            
            if (is_wp_error($result)) {
                restore_current_blog();
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            
            restore_current_blog();
            wp_send_json_success($result);
        } else {
            $showroom_id = $showroom_id > 0 ? $showroom_id : intval($dealer_key);
            $manager = new self();
            $result = $manager->sync_all_for_showroom($showroom_id);
            
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            
            wp_send_json_success($result);
        }
    }

    public static function sync_all_for_dealer()
    {
        $enabled = get_field('findcar_enabled', 'options-dealer');
        
        if (!$enabled) {
            return new WP_Error('findcar_error', __('Integration is not enabled', 'volvo-feed-templates'));
        }

        $args = [
            'post_type' => 'stock-car',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'findcar_enabled',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $synced_count = 0;
        $error_count = 0;
        $errors = [];

        $api_key = get_field('findcar_api_key', 'options-dealer');
        $location_id = get_field('findcar_location_id', 'options-dealer');
        $location_token = get_field('findcar_location_token', 'options-dealer');

        if (empty($api_key) || empty($location_id)) {
            return new WP_Error('findcar_error', __('No credentials', 'volvo-feed-templates'));
        }

        foreach ($query->posts as $car_id) {
            $result = self::sync_car_for_dealer($car_id, $api_key, $location_id, $location_token);
            
            if (is_wp_error($result)) {
                $error_count++;
                $errors[] = [
                    'car_id' => $car_id,
                    'error' => $result->get_error_message(),
                ];
            } else {
                $synced_count++;
            }
        }

        return [
            'synced' => $synced_count,
            'errors' => $error_count,
            'error_details' => $errors,
            'total' => count($query->posts),
        ];
    }

    private static function sync_car_for_dealer($car_id, $api_key, $location_id, $location_token)
    {
        $findcar_enabled = get_field('findcar_enabled', $car_id);
        
        if (!$findcar_enabled) {
            $existing_listing_id = get_field('findcar_listing_id', $car_id);
            if (!empty($existing_listing_id)) {
                return self::delete_listing_for_dealer($car_id, $api_key, $location_id, $location_token);
            }
            return new WP_Error('findcar_skip', __('The car is not included in FindCar synchronization', 'volvo-feed-templates'));
        }

        $mapper = new FindCar_Data_Mapper();
        
        $missing_fields = $mapper->validate_car_fields($car_id);
        if (!empty($missing_fields)) {
            $error_msg = 'Brak wymaganych pól: ' . implode(', ', $missing_fields);
            update_field('findcar_sync_error', $error_msg, $car_id);
            return new WP_Error('findcar_validation_error', $error_msg);
        }

        $listing_data = $mapper->map_car_to_listing($car_id);
        
        if (is_wp_error($listing_data)) {
            update_field('findcar_sync_error', $listing_data->get_error_message(), $car_id);
            return $listing_data;
        }

        $client = new FindCar_API_Client($api_key, $location_token, false);
        
        $partner_listing_id = $mapper->get_car_listing_id($car_id);
        
        $existing_listing_id = get_field('findcar_listing_id', $car_id);
        
        if (!empty($existing_listing_id)) {
            $result = $client->update_listing($location_id, $partner_listing_id, $listing_data);
        } else {
            $result = $client->create_listing($location_id, $partner_listing_id, $listing_data);
        }

        if (is_wp_error($result)) {
            update_field('findcar_sync_error', $result->get_error_message(), $car_id);
            return $result;
        }

        if (isset($result['id'])) {
            update_field('findcar_listing_id', $result['id'], $car_id);
        }

        if (isset($result['publicListingNumber'])) {
            update_field('findcar_listing_number', $result['publicListingNumber'], $car_id);
        }

        if (isset($result['listingUrl'])) {
            update_field('findcar_listing_url', $result['listingUrl'], $car_id);
        }

        update_field('findcar_last_sync', current_time('mysql'), $car_id);
        update_field('findcar_sync_error', '', $car_id);

        return $result;
    }

    private static function delete_listing_for_dealer($car_id, $api_key, $location_id, $location_token)
    {
        $mapper = new FindCar_Data_Mapper();
        $partner_listing_id = $mapper->get_car_listing_id($car_id);
        
        $client = new FindCar_API_Client($api_key, $location_token, false);
        $result = $client->delete_listing($location_id, $partner_listing_id);

        if (!is_wp_error($result)) {
            update_field('findcar_listing_id', '', $car_id);
            update_field('findcar_listing_number', '', $car_id);
            update_field('findcar_listing_url', '', $car_id);
            
            return true;
        }

        return false;
    }

    public static function handle_ajax_sync_car()
    {
        check_ajax_referer('findcar_nonce', 'nonce');
        
        $car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : 0;
        
        if (!$car_id) {
            wp_send_json_error(['message' => __('Brak ID samochodu', 'volvo-feed-templates')]);
        }
        
        $manager = new self();
        $result = $manager->sync_car($car_id, true);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }

    public static function handle_ajax_enable_existing_cars()
    {
        check_ajax_referer('findcar_nonce', 'nonce');
        
        $showroom_id = isset($_POST['showroom_id']) ? intval($_POST['showroom_id']) : 0;
        
        if (!$showroom_id) {
            wp_send_json_error(['message' => __('No showroom ID', 'volvo-feed-templates')]);
        }
        
        $manager = new self();
        $result = $manager->enable_existing_cars($showroom_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }

    public static function handle_ajax_preview_sync()
    {
        check_ajax_referer('findcar_nonce', 'nonce');
        
        $showroom_id = isset($_POST['showroom_id']) ? intval($_POST['showroom_id']) : 0;
        
        $manager = new self();
        $result = $manager->preview_sync_status($showroom_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success($result);
    }

    public static function handle_ajax_bulk_preview()
    {
        check_ajax_referer('findcar_nonce', 'nonce');
        
        $car_ids = isset($_POST['car_ids']) ? array_map('intval', $_POST['car_ids']) : [];
        
        if (empty($car_ids)) {
            wp_send_json_error(['message' => __('No cars selected', 'volvo-feed-templates')]);
        }
        
        $manager = new self();
        $result = $manager->get_bulk_preview_data($car_ids);
        
        wp_send_json_success($result);
    }

    public function preview_sync_status($showroom_id = 0)
    {
        $mapper = new FindCar_Data_Mapper();
        
        $args = [
            'post_type' => 'stock-car',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];
        
        if ($showroom_id > 0) {
            $args['meta_query'] = [
                [
                    'key' => 'showroom',
                    'value' => $showroom_id,
                    'compare' => '=',
                ],
                [
                    'key' => 'findcar_enabled',
                    'value' => '1',
                    'compare' => '=',
                ],
            ];
        } else {
            $args['meta_query'] = [
                [
                    'key' => 'findcar_enabled',
                    'value' => '1',
                    'compare' => '=',
                ],
            ];
        }
        
        $query = new WP_Query($args);
        
        $ready_to_sync = 0;
        $missing_fields = 0;
        $cars_missing_info = [];
        
        foreach ($query->posts as $car_id) {
            $car_missing = $mapper->validate_car_fields($car_id);
            
            if (empty($car_missing)) {
                $ready_to_sync++;
            } else {
                $missing_fields++;
                $cars_missing_info[] = [
                    'car_id' => $car_id,
                    'car_title' => get_the_title($car_id),
                    'missing' => $car_missing,
                ];
            }
        }
        
        return [
            'total_enabled' => count($query->posts),
            'ready_to_sync' => $ready_to_sync,
            'missing_fields' => $missing_fields,
            'cars_missing_info' => $cars_missing_info,
        ];
    }

    public function enable_and_sync_all_existing()
    {
        $args = [
            'post_type' => 'stock-car',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        $query = new WP_Query($args);
        $synced_count = 0;
        $error_count = 0;
        $errors = [];

        foreach ($query->posts as $car_id) {
            $raw_meta = get_post_meta($car_id, 'findcar_enabled', true);

            if ($raw_meta === '0' || $raw_meta === 0 || $raw_meta === false) {
                continue;
            }

            update_field('findcar_enabled', true, $car_id);
            $result = $this->sync_car($car_id);

            if (is_wp_error($result)) {
                $error_count++;
                $errors[] = [
                    'car_id' => $car_id,
                    'error' => $result->get_error_message(),
                ];
            } else {
                $synced_count++;
            }
        }

        return [
            'synced' => $synced_count,
            'errors' => $error_count,
            'error_details' => $errors,
            'total' => count($query->posts),
        ];
    }

    public static function handle_auto_sync_all()
    {
        $manager = new self();
        $manager->enable_and_sync_all_existing();
    }
}