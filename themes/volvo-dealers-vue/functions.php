<?php
/**
 * Volvo Dealers Vue Theme functions and definitions
 *
 * @package Volvo_Dealers_Vue
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function volvo_dealers_vue_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('customize-selective-refresh-widgets');
    
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'volvo-dealers-vue'),
        'footer' => __('Footer Menu', 'volvo-dealers-vue'),
    ));
    
    add_post_type_support('page', 'excerpt');
}
add_action('after_setup_theme', 'volvo_dealers_vue_setup');

/**
 * Check if local asset exists, otherwise return CDN URL
 */
function volvo_dealers_vue_asset($local_path, $cdn_url) {
    $theme_dir = get_template_directory();
    if (file_exists($theme_dir . $local_path)) {
        return get_template_directory_uri() . $local_path;
    }
    return $cdn_url;
}

/**
 * Enqueue scripts and styles
 */
function volvo_dealers_vue_scripts() {
    $theme_uri = get_template_directory_uri();
    
    // Theme stylesheet
    wp_enqueue_style(
        'volvo-dealers-vue-style',
        $theme_uri . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Volvo font - CDN fallback
    wp_enqueue_style(
        'volvo-font',
        'https://assets.volvo.com/is/content/VolvoInformationTechnologyAB/fonts/volvo-sans.css',
        array(),
        null
    );
    
    // Vue 3 - CDN (reliable)
    wp_enqueue_script(
        'vue',
        'https://unpkg.com/vue@3/dist/vue.global.js',
        array(),
        '3.2.31',
        true
    );
    
    // Vue Router 4 - CDN
    wp_enqueue_script(
        'vue-router',
        'https://unpkg.com/vue-router@4/dist/vue-router.global.js',
        array('vue'),
        '4.0.14',
        true
    );
    
    // Vue i18n 9 - CDN
    wp_enqueue_script(
        'vue-i18n',
        'https://unpkg.com/vue-i18n@9/dist/vue-i18n.global.js',
        array('vue'),
        '9.1.11',
        true
    );
    
    // Swiper - CDN
    wp_enqueue_style(
        'swiper',
        'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css',
        array(),
        '8.3.2'
    );
    wp_enqueue_script(
        'swiper',
        'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js',
        array(),
        '8.3.2',
        true
    );
    
    // Theme data
    wp_localize_script('vue', 'volvoThemeData', array(
        'siteUrl' => home_url(),
        'apiUrl' => home_url('/wp-json/wp/v2'),
        'locale' => get_locale(),
        'themeUrl' => $theme_uri,
        'assetsUrl' => $theme_uri . '/assets',
        'nonce' => wp_create_nonce('wp_rest'),
    ));
    
    // Vue app - bundled version
    wp_enqueue_script(
        'volvo-dealers-vue-app',
        $theme_uri . '/vue-src/dist/app.js',
        array('vue', 'vue-router', 'vue-i18n', 'swiper'),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'volvo_dealers_vue_scripts');

/**
 * Register custom REST API fields
 */
function volvo_dealers_vue_register_rest_fields() {
    register_rest_field(array('post', 'page'), 'featured_image', array(
        'get_callback' => function($object) {
            $featured_image = get_post_thumbnail_id($object['id']);
            if ($featured_image) {
                $image = wp_get_attachment_image_src($featured_image, 'full');
                return array(
                    'src' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                );
            }
            return null;
        },
    ));
    
    if (function_exists('get_fields')) {
        register_rest_field(array('post', 'page'), 'acf', array(
            'get_callback' => function($object) {
                return get_fields($object['id']);
            },
        ));
    }
}
add_action('rest_api_init', 'volvo_dealers_vue_register_rest_fields');

/**
 * Custom REST API endpoints
 */
function volvo_dealers_vue_register_routes() {
    register_rest_route('volvo/v1', '/car-models', array(
        'methods' => 'GET',
        'callback' => 'volvo_dealers_vue_get_car_models',
        'permission_callback' => '__return_true',
    ));
    
    register_rest_route('volvo/v1', '/homepage', array(
        'methods' => 'GET',
        'callback' => 'volvo_dealers_vue_get_homepage_data',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'volvo_dealers_vue_register_routes');

/**
 * Get car models
 */
function volvo_dealers_vue_get_car_models() {
    // Use Volvo CDN for car images (reliable)
    $car_models = array(
        array('id' => 1, 'name' => 'XC90', 'type' => 'SUV', 'price' => '351 900', 'image' => 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc90-recharge-side?qlt=82&wid=600', 'link' => '/modele/xc90'),
        array('id' => 2, 'name' => 'XC60', 'type' => 'SUV', 'price' => '289 900', 'image' => 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc60-recharge-side?qlt=82&wid=600', 'link' => '/modele/xc60'),
        array('id' => 3, 'name' => 'XC40', 'type' => 'SUV', 'price' => '199 900', 'image' => 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/xc40-recharge-side?qlt=82&wid=600', 'link' => '/modele/xc40'),
        array('id' => 4, 'name' => 'V90', 'type' => 'Kombi', 'price' => '319 900', 'image' => 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/v90-recharge-side?qlt=82&wid=600', 'link' => '/modele/v90'),
        array('id' => 5, 'name' => 'V60', 'type' => 'Kombi', 'price' => '249 900', 'image' => 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/v60-recharge-side?qlt=82&wid=600', 'link' => '/modele/v60'),
        array('id' => 6, 'name' => 'EX30', 'type' => 'Elektryczny SUV', 'price' => '179 900', 'image' => 'https://assets.volvo.com/is/image/VolvoInformationTechnologyAB/ex30-side?qlt=82&wid=600', 'link' => '/modele/ex30'),
    );
    return new WP_REST_Response($car_models, 200);
}

/**
 * Get homepage data
 */
function volvo_dealers_vue_get_homepage_data() {
    $homepage_id = get_option('page_on_front');
    $data = array(
        'title' => get_the_title($homepage_id),
        'content' => apply_filters('the_content', get_post_field('post_content', $homepage_id)),
    );
    return new WP_REST_Response($data, 200);
}

/**
 * Disable admin bar for non-admins
 */
function volvo_dealers_vue_disable_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'volvo_dealers_vue_disable_admin_bar');

/**
 * Add meta tags
 */
function volvo_dealers_vue_meta_tags() {
    echo '<meta name="theme-color" content="#141414">';
}
add_action('wp_head', 'volvo_dealers_vue_meta_tags');

/**
 * Disable emoji scripts
 */
function volvo_dealers_vue_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
}
add_action('init', 'volvo_dealers_vue_disable_emojis');
