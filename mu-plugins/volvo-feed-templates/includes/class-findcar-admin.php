<?php

if (!defined('ABSPATH')) {
    exit;
}

class FindCar_Admin
{
    public function __construct()
    {
        add_action('acf/input/admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('acf/save_post', [$this, 'handle_save']);
        add_action('admin_notices', [$this, 'show_car_sync_error_notice']);
    }

    public function enqueue_admin_scripts()
    {
        $screen = get_current_screen();
        
        $allowed_screens = [
            'toplevel_page_options-dealer',
            'stock-car',
            'edit-stock-car',
        ];
        
        if (!$screen || !in_array($screen->base, $allowed_screens)) {
            return;
        }

        wp_enqueue_style(
            'findcar-sync-modal',
            VFT_PLUGIN_URL . 'admin/assets/css/findcar-modal.css',
            [],
            VFT_VERSION
        );

        wp_enqueue_script(
            'findcar-sync-modal',
            VFT_PLUGIN_URL . 'admin/assets/js/findcar-sync-modal.js',
            ['jquery'],
            VFT_VERSION,
            true
        );

        wp_enqueue_script(
            'findcar-admin',
            VFT_PLUGIN_URL . 'admin/assets/js/findcar-admin.js',
            ['jquery', 'findcar-sync-modal'],
            VFT_VERSION,
            true
        );

        wp_localize_script('findcar-admin', 'findcarAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('findcar_nonce'),
            'dealerKey' => 'options',
            'blogId' => get_current_blog_id(),
            'i18n' => [
                'testing' => __('Connection testing...', 'volvo-feed-templates'),
                'testSuccess' => __('Active connection', 'volvo-feed-templates'),
                'testError' => __('Connection error', 'volvo-feed-templates'),
                'syncing' => __('Synchronization...', 'volvo-feed-templates'),
                'syncSuccess' => __('Synchronized', 'volvo-feed-templates'),
                'syncError' => __('Synchronization error', 'volvo-feed-templates'),
                'enabling' => __('Enabling...', 'volvo-feed-templates'),
                'enableSuccess' => __('Synchronization is enabled', 'volvo-feed-templates'),
                'enableError' => __('Error', 'volvo-feed-templates'),
                'preview' => __('Preview', 'volvo-feed-templates'),
                'loading' => __('Loading...', 'volvo-feed-templates'),
            ],
        ]);
    }

    public function handle_save($post_id)
    {
        if ($post_id !== 'options') {
            return;
        }

        $dealer_enabled = get_field('findcar_enabled', 'options-dealer');
        $auto_sync = get_field('findcar_auto_sync', 'options-dealer');

        if ($dealer_enabled && $auto_sync) {
            if (!wp_next_scheduled('findcar_auto_sync_all_event')) {
                wp_schedule_single_event(time() + 10, 'findcar_auto_sync_all_event');
            }
        }
    }

    public function show_car_sync_error_notice()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'stock-car') {
            return;
        }

        if ($screen->base !== 'post' && $screen->base !== 'post-edit') {
            return;
        }

        $post_id = get_the_ID();
        if (!$post_id) {
            return;
        }

        $sync_error = get_field('findcar_sync_error', $post_id);
        if (empty($sync_error)) {
            return;
        }

        $is_missing_fields = strpos($sync_error, __('Missing required fields', 'volvo-feed-templates') . ':') !== false;
        $notice_class = $is_missing_fields ? 'notice-warning' : 'notice-error';
        $notice_title = $is_missing_fields ? 'FindCar - ' . __('Missing required fields', 'volvo-feed-templates') : 'FindCar - ' . __('Synchronization error', 'volvo-feed-templates');

        echo '<div class="notice ' . $notice_class . ' is-dismissible">';
        echo '<p><strong>' . esc_html($notice_title) . '</strong></p>';
        echo '<p>' . esc_html($sync_error) . '</p>';
        echo '</div>';
    }
}

new FindCar_Admin();