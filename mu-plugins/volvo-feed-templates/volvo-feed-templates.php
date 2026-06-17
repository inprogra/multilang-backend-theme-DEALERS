<?php
/**
 * Plugin Name: Volvo Feed Templates
 * Plugin URI: https://volvotest.pl
 * Description: Create and manage feed templates (CSV/XML) for external services like Facebook, Otomoto, Findcar.pl
 * Version: 1.0.0
 * Author: Volvo Team
 * Author URI: https://volvotest.pl
 * License: GPL v2 or later
 * Network: true
 */

if (!defined('ABSPATH')) {
    exit;
}

define('VFT_VERSION', '1.0.0');
define('VFT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VFT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VFT_MAIN_BLOG_ID', 1);

require_once VFT_PLUGIN_DIR . 'includes/class-feed-template.php';
require_once VFT_PLUGIN_DIR . 'includes/class-feed-generator.php';
require_once VFT_PLUGIN_DIR . 'includes/class-feed-endpoint.php';
require_once VFT_PLUGIN_DIR . 'includes/class-findcar-acf-fields.php';
require_once VFT_PLUGIN_DIR . 'includes/class-findcar-api-client.php';
require_once VFT_PLUGIN_DIR . 'includes/class-findcar-data-mapper.php';
require_once VFT_PLUGIN_DIR . 'includes/class-findcar-sync-manager.php';
require_once VFT_PLUGIN_DIR . 'includes/class-findcar-admin.php';

class Volvo_Feed_Templates
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        $this->init_findcar_sync();
    }

    private function init_findcar_sync()
    {
        $sync_manager = new FindCar_Sync_Manager();
        $sync_manager->init_hooks();
        
        add_action('wp_ajax_findcar_test_connection', ['FindCar_Sync_Manager', 'handle_ajax_test_connection']);
        add_action('wp_ajax_findcar_sync_all', ['FindCar_Sync_Manager', 'handle_ajax_sync_all']);
        add_action('wp_ajax_findcar_sync_car', ['FindCar_Sync_Manager', 'handle_ajax_sync_car']);
        add_action('wp_ajax_findcar_enable_existing_cars', ['FindCar_Sync_Manager', 'handle_ajax_enable_existing_cars']);
        add_action('wp_ajax_findcar_preview_sync', ['FindCar_Sync_Manager', 'handle_ajax_preview_sync']);
        add_action('wp_ajax_findcar_bulk_preview', ['FindCar_Sync_Manager', 'handle_ajax_bulk_preview']);
        add_action('admin_notices', [$this, 'findcar_bulk_sync_notices']);
        add_action('admin_notices', [$this, 'findcar_preview_notice']);
        add_action('admin_footer-edit.php', [$this, 'findcar_list_footer_scripts']);
    }

    public function findcar_bulk_sync_notices()
    {
        if (!isset($_GET['findcar_bulk_synced']) && !isset($_GET['findcar_bulk_errors'])) {
            return;
        }
        
        $synced = isset($_GET['findcar_bulk_synced']) ? intval($_GET['findcar_bulk_synced']) : 0;
        $errors = isset($_GET['findcar_bulk_errors']) ? intval($_GET['findcar_bulk_errors']) : 0;
        $skipped = isset($_GET['findcar_bulk_skipped']) ? intval($_GET['findcar_bulk_skipped']) : 0;
        $error_details = isset($_GET['findcar_bulk_error_details']) ? urldecode($_GET['findcar_bulk_error_details']) : '';
        
        if ($errors > 0 || $skipped > 0) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . sprintf(__('FindCar: %d synchronized, errors: %d, skipped: %d. %s', 'volvo-feed-templates'), $synced, $errors, $skipped, $error_details) . '</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . sprintf(__('FindCar: %d cars have been successfully synchronized.', 'volvo-feed-templates'), $synced) . '</p>';
            echo '</div>';
        }
    }

    public function findcar_preview_notice()
    {
        if (!isset($_GET['findcar_preview']) || !isset($_GET['findcar_preview_data'])) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'edit' || $screen->post_type !== 'stock-car') {
            return;
        }
        
        $preview_data = json_decode(base64_decode($_GET['findcar_preview_data']), true);
        
        if (!$preview_data) {
            return;
        }
        
        echo '<div class="notice notice-info is-dismissible findcar-preview-summary">';
        echo '<div class="findcar-preview-content">';
        echo '<h3>FindCar - Podgląd synchronizacji</h3>';
        echo '<div class="findcar-preview-stats">';
        echo '<span class="findcar-stat findcar-stat-ready"><strong>' . esc_html($preview_data['ready_to_sync']) . '</strong> Gotowe do synchronizacji</span>';
        echo '<span class="findcar-stat findcar-stat-missing"><strong>' . esc_html($preview_data['missing_fields']) . '</strong> Brakuje danych</span>';
        echo '<span class="findcar-stat findcar-stat-total"><strong>' . esc_html($preview_data['total_enabled']) . '</strong> Włączone</span>';
        echo '</div>';
        
        if (!empty($preview_data['cars_missing_info'])) {
            echo '<details>';
            echo '<summary>Samochody wymagające uzupełnienia danych (' . count($preview_data['cars_missing_info']) . ')</summary>';
            echo '<ul class="findcar-preview-list">';
            foreach (array_slice($preview_data['cars_missing_info'], 0, 10) as $car) {
                echo '<li>';
                echo '<strong>' . esc_html($car['car_title']) . '</strong> (#' . $car['car_id'] . ')';
                echo '<ul class="findcar-missing-fields">';
                foreach ($car['missing'] as $field) {
                    echo '<li>' . esc_html($field) . '</li>';
                }
                echo '</ul>';
                echo '</li>';
            }
            if (count($preview_data['cars_missing_info']) > 10) {
                echo '<li class="findcar-more">... i ' . (count($preview_data['cars_missing_info']) - 10) . ' więcej</li>';
            }
            echo '</ul>';
            echo '</details>';
        }
        
        echo '</div>';
        echo '</div>';
    }

    public function findcar_list_footer_scripts()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'edit' || $screen->post_type !== 'stock-car') {
            return;
        }
        
        $enabled = get_field('findcar_enabled', 'options-dealer');
        if (!$enabled) {
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
                return;
            }
        }
        
        wp_enqueue_style('findcar-sync-modal', VFT_PLUGIN_URL . 'admin/assets/css/findcar-modal.css', [], VFT_VERSION);
        wp_enqueue_script('findcar-sync-modal', VFT_PLUGIN_URL . 'admin/assets/js/findcar-sync-modal.js', ['jquery'], VFT_VERSION, true);
        
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.findcar-preview-summary').on('click', '.findcar-preview-content', function(e) {
                if (e.target.tagName === 'A' || e.target.tagName === 'SUMMARY') {
                    return;
                }
                if (window.FindcarSyncModal && window.findcarPreviewData) {
                    window.FindcarSyncModal.showPreview(window.findcarPreviewData, {
                        onSync: function() {
                            $('#doaction, #doaction2').trigger('click');
                        }
                    });
                }
            });
        });
        </script>
        <style>
        .findcar-preview-summary {
            border-left-color: #0073aa;
        }
        .findcar-preview-content {
            padding: 10px 0;
        }
        .findcar-preview-content h3 {
            margin: 0 0 12px 0;
            font-size: 16px;
        }
        .findcar-preview-stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .findcar-stat {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
        }
        .findcar-stat-ready {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .findcar-stat-missing {
            background: #fff3e0;
            color: #e65100;
        }
        .findcar-stat-total {
            background: #e3f2fd;
            color: #1565c0;
        }
        .findcar-preview-list {
            margin: 10px 0 0 20px;
        }
        .findcar-missing-fields {
            margin: 5px 0 0 15px;
            color: #666;
        }
        .findcar-more {
            color: #999;
            font-style: italic;
        }
        .findcar-list-btn {
            margin-left: 8px;
        }
        </style>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var previewBtnHtml = '<button type="button" class="button findcar-list-btn" id="findcar-list-preview-btn">Podgląd FindCar</button>';
            
            $('.tablenav.top .actions').each(function() {
                if (!$(this).find('#findcar-list-preview-btn').length) {
                    $(this).append(previewBtnHtml);
                }
            });
        });
        </script>
        <?php
    }

    public function init()
    {
        if (is_multisite()) {
            $this->init_feed_endpoint();
        }
        
        if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            $this->init_admin_ajax();
            
            if (is_super_admin()) {
                switch_to_blog(1);
                Feed_Template::create_default_templates(1);
                restore_current_blog();
            }
        }
    }

    private function init_feed_endpoint()
    {
        new Feed_Endpoint();
    }

    private function init_admin_ajax()
    {
        Feed_Template::init_ajax_handlers();
    }

    public function add_admin_menu()
    {
        $blog_id = get_current_blog_id();
        
        if ($blog_id == 1 && is_super_admin()) {
            add_menu_page(
                __('Product feeds', 'volvo-feed-templates'),
                __('Product feeds', 'volvo-feed-templates'),
                'manage_network',
                'volvo-feed-templates',
                [$this, 'render_admin_page'],
                'dashicons-download',
                30
            );
        } else {
            add_menu_page(
                __('My feeds', 'volvo-feed-templates'),
                __('My feeds', 'volvo-feed-templates'),
                'edit_posts',
                'volvo-feed-templates',
                [$this, 'render_admin_page'],
                'dashicons-download',
                30
            );
        }
    }

    public function render_admin_page()
    {
        $blog_id = get_current_blog_id();
        $is_main_blog = ($blog_id == 1);
        
        $templates = Feed_Template::get_templates($blog_id);
        $templates = array_map(function($t) { return $t->to_array(); }, $templates);
        
        $global_templates = Feed_Template::get_templates(1);
        $global_templates = array_map(function($t) { return $t->to_array(); }, $global_templates);

        wp_enqueue_script('vue-js');
        wp_enqueue_style('volvo-feed-templates-admin');
        wp_enqueue_script('volvo-feed-templates-admin');

        wp_localize_script('volvo-feed-templates-admin', 'volvoFeedTemplates', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('volvo_feed_templates_nonce'),
            'blogId' => $blog_id,
            'isMainBlog' => $is_main_blog,
            'isSuperAdmin' => is_super_admin(),
            'siteUrl' => get_site_url($blog_id),
            'templates' => $templates,
            'globalTemplates' => $global_templates,
            'i18n' => [
                'title' => $is_main_blog ? __('Product feeds', 'volvo-feed-templates') : __('My feeds', 'volvo-feed-templates'),
                'createNew' => __('Create feed', 'volvo-feed-templates'),
                'editTemplate' => __('Edit feed', 'volvo-feed-templates'),
                'delete' => __('Delete', 'volvo-feed-templates'),
                'copy' => __('Copy', 'volvo-feed-templates'),
                'templateName' => __('Feed name', 'volvo-feed-templates'),
                'slug' => __('Slug (URL)', 'volvo-feed-templates'),
                'format' => __('Format', 'volvo-feed-templates'),
                'carType' => __('Car type', 'volvo-feed-templates'),
                'used' => __('Used', 'volvo-feed-templates'),
                'new' => __('New', 'volvo-feed-templates'),
                'both' => __('Both', 'volvo-feed-templates'),
                'csv' => __('CSV', 'volvo-feed-templates'),
                'xml' => __('XML', 'volvo-feed-templates'),
                'fields' => __('Fields', 'volvo-feed-templates'),
                'save' => __('Save', 'volvo-feed-templates'),
                'cancel' => __('Cancel', 'volvo-feed-templates'),
                'copyUrl' => __('Copy URL', 'volvo-feed-templates'),
                'preview' => __('Preview', 'volvo-feed-templates'),
                'globalTemplates' => __('Global feeds', 'volvo-feed-templates'),
                'myTemplates' => __('My feeds', 'volvo-feed-templates'),
                'availableTemplates' => __('Available feeds', 'volvo-feed-templates'),
                'noTemplates' => __('No feeds found', 'volvo-feed-templates'),
                'confirmDelete' => __('Are you sure you want to delete this feed?', 'volvo-feed-templates'),
                'urlCopied' => __('URL copied to clipboard', 'volvo-feed-templates'),
            ]
        ]);

        echo '<div id="volvo-feed-templates-app"></div>';
    }

    public function enqueue_assets()
    {
    }

    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'volvo-feed-templates') === false) {
            return;
        }

        wp_enqueue_style('volvo-feed-templates-admin', VFT_PLUGIN_URL . 'admin/assets/css/admin.css', [], VFT_VERSION);
        
        if (!file_exists(VFT_PLUGIN_DIR . 'admin/assets/js/vue.min.js')) {
    wp_register_script('vue-js', 'https://unpkg.com/vue@3/dist/vue.global.prod.js', [], '3.4.0', true);
} else {
    wp_register_script('vue-js', VFT_PLUGIN_URL . 'admin/assets/js/vue.min.js', [], '3.4.0', true);
}
        
        wp_register_script('volvo-feed-templates-admin', VFT_PLUGIN_URL . 'admin/assets/js/FeedTemplatesApp.js', ['vue-js'], VFT_VERSION, true);
    }

    public static function activate()
    {
        if (!is_multisite()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('This plugin requires WordPress Multisite to be enabled.', 'volvo-feed-templates'));
        }

        global $wpdb;
        
        $blogs = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
        
        foreach ($blogs as $blog_id) {
            switch_to_blog($blog_id);
            Feed_Template::create_default_templates($blog_id);
            flush_rewrite_rules();
            restore_current_blog();
        }
    }

    public static function deactivate()
    {
    }
}

function Volvo_Feed_Templates()
{
    return Volvo_Feed_Templates::get_instance();
}

register_activation_hook(__FILE__, ['Volvo_Feed_Templates', 'activate']);
register_deactivation_hook(__FILE__, ['Volvo_Feed_Templates', 'deactivate']);

add_action('plugins_loaded', function () {
    load_textdomain(
        'volvo-feed-templates',
        VFT_PLUGIN_DIR . 'languages/volvo-feed-templates-' . determine_locale() . '.mo'
    );
});

add_action('init', 'Volvo_Feed_Templates', 5);
