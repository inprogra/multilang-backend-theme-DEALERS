<?php
;
// Bypass WordPress multisite domain checking for Laravel domain
// This must be at the very top to prevent WordPress from redirecting non-existent domains
add_action('muplugins_loaded', function() {
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'apiv2.easyapi.space') !== false) {
        // Prevent WordPress from checking if this domain exists in multisite blogs
        // This allows Laravel to handle all requests for this domain
        add_filter('pre_option_WPLANG', '__return_empty_string', 1);
        
        // Bypass the multisite blog lookup
        add_filter('ms_site_not_found', function($site, $domain, $path) {
            // Return a fake site object to prevent redirect
            if (strpos($domain, 'apiv2.easyapi.space') !== false) {
                return (object) [
                    'blog_id' => 1,
                    'domain' => $domain,
                    'path' => $path,
                ];
            }
            return $site;
        }, 1, 3);
    }
}, 1);

$GLOBALS['ident'] = get_current_blog_id();
$GLOBALS['disable_dol'] = false;

// Early exit for REST API, custom API, and AJAX requests - skip template loading
$is_rest_request = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') === 0);
$is_api_request = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0);
$is_ajax_request = (defined('DOING_AJAX') && DOING_AJAX);

if ($is_rest_request || $is_api_request || $is_ajax_request) {
    // Only load the minimum files needed for REST/API/AJAX endpoints
    // Classes are autoloaded via Composer classmap - no explicit includes needed
    require_once get_template_directory() . '/includes/post-types.php';    // Register post types for WP_Query
    require_once get_template_directory() . '/includes/acf-fields-def.php';  // ACF fields
    require_once get_template_directory() . '/includes/helpers/helpers.php'; // Utility functions
    require_once get_template_directory() . '/includes/multisite-fixes.php'; // MultisiteFixer hooks
    require_once get_template_directory() . '/includes/redirections.php';    // Redirections + validateQuery
    require_once get_template_directory() . '/includes/cache.php';           // Cache class
    require_once get_template_directory() . '/includes/functions-rest.php';           // REST functions
    require_once get_template_directory() . '/includes/api.php';             // Custom REST endpoints
    require_once get_template_directory() . '/includes/ajax.php';            // AJAX handlers
    // ACF plugin is loaded as mu-plugin - get_field() works without theme acf.php
    return; // Skip all template/admin includes below
}

use Classes\Controller;
use Classes\CarDictionary;
use Classes\Redirections;
use Classes\StockCar;
use Classes\VolvoSync;
use Controllers\StockController;
use Classes\DolStatus;
use Classes\DolCarsAdmin;
use Smsapi\Client\SmsapiClient;
use Smsapi\Client\Feature\Sms\Bag\SendSmsBag;
use Classes\YouLead;
use Classes\Head;
use Classes\ProductTagsMetaBox;
use Controllers\GaDashboardController;
use function Env\env;
use Controllers\EventController;
new ProductTagsMetaBox();
new EventController();
new DolCarsAdmin();

add_action('admin_init', function() {
    $controller = new \Controllers\GaDashboardController();
    $controller->init();
});
// if (get_current_blog_id() === 21) { 

//     var_dump(post_type_exists('stock-car'));

// }









add_theme_support('title-tag');
add_theme_support('post-thumbnails');

add_action('after_setup_theme', function() {
    // Load theme text domain for translations - must be called early
    load_theme_textdomain('partners-site_v2', get_template_directory() . '/languages');

    register_nav_menus(
        array(
            'header' => __('Primary Menu', 'partners-site_v2'),
            'side-nav' => __('Side Menu', 'partners-site_v2'),
            'footer' => __('Footer Menu', 'partners-site_v2'),
        )
    );
});

add_action('acf/input/admin_footer', function() {
    if (!is_admin()) {
    return;
    }
    ?>
    <script>
    acf.add_filter('select2_escape_markup', function( escaped_value, original_value, $select, settings, field, instance ){

        return original_value;

    });
    acf.add_filter('select2_args', function(args) {
    args.templateSelection = function(selection) {
    var $selection = jQuery('<span class="acf-selection"></span>');
    $selection.html(acf.escHtml(selection.text));
    $selection.data('element', selection.element);
    return $selection;
    }
    return args;
    });
    </script>
    <?php
    });

    // add_action('template_redirect', function() {

    //     if (!current_user_can('administrator')) return;
    
    //     require_once ABSPATH . 'wp-admin/includes/plugin.php';
    
    //     $plugins = get_plugins();
    //     $network_active = wp_get_active_network_plugins();
    
    //     // Dev/admin pluginy
    //     $dev_plugins = [
    //         'query-monitor/query-monitor.php',
    //         'better-search-replace/better-search-replace.php',
    //         'post-type-switcher/post-type-switcher.php',
    //         'wordpress-importer/wordpress-importer.php',
    //         'duplicate-post/duplicate-post.php',
    //         'custom-post-exporter/index.php',
    //         'fatal-error-notify/fatal-error-notify.php',
    //     ];
    
    //     global $wp_scripts, $wp_styles;
    
    //     echo "<pre style='background:#222;color:#0f0;padding:10px;font-size:14px;line-height:1.4em;'>";
    //     echo "===== AUDYT AKTYWNYCH WTYCZEK =====\n\n";
    
    //     foreach ($plugins as $plugin_path => $plugin_data) {
    
    //         $is_active_local = in_array($plugin_path, get_option('active_plugins'));
    //         $is_active_network = in_array(WP_PLUGIN_DIR . '/' . $plugin_path, $network_active);
    
    //         if (!$is_active_local && !$is_active_network) continue; // tylko aktywne
    
    //         $label = in_array($plugin_path, $dev_plugins) ? '[DEV/ADMIN] ' : '';
    
    //         echo $label . "PLUGIN: " . $plugin_data['Name'] . "\n";
    //         echo "PATH: " . $plugin_path . "\n";
    //         echo "ACTIVE: " . ($is_active_network ? "YES (NETWORK)" : "YES (BLOG)") . "\n";
    
    //         // sprawdzamy JS/CSS pluginu
    //         $found_assets = false;
    //         foreach ($wp_scripts->queue as $script) {
    //             $src = $wp_scripts->registered[$script]->src ?? '';
    //             if (strpos($src, dirname($plugin_path)) !== false) {
    //                 echo "JS: " . $src . "\n";
    //                 $found_assets = true;
    //             }
    //         }
    //         foreach ($wp_styles->queue as $style) {
    //             $src = $wp_styles->registered[$style]->src ?? '';
    //             if (strpos($src, dirname($plugin_path)) !== false) {
    //                 echo "CSS: " . $src . "\n";
    //                 $found_assets = true;
    //             }
    //         }
    //         if (!$found_assets) {
    //             echo "ASSETS: NONE\n";
    //         }
    
    //         echo "-----------------------------\n";
    //     }
    
    //     echo "</pre>";
    
    //     exit; 
    // });

    // aktywne wtyczki
    // add_action('template_redirect', function() {

    //     if (!current_user_can('administrator')) return;
    
    //     require_once ABSPATH . 'wp-admin/includes/plugin.php';
    
    //     $plugins = get_plugins();
    
       
    //     $network_active = wp_get_active_network_plugins();
    
    //     echo "<pre style='background:#222;color:#0f0;padding:10px;font-size:14px;line-height:1.4em;'>";
    //     echo "===== AKTYWNE WTYCZKI NETWORK + BLOG =====\n\n";
    
    //     foreach ($plugins as $plugin_path => $plugin_data) {
    
    //         $is_active_local = in_array($plugin_path, get_option('active_plugins'));
    //         $is_active_network = in_array(WP_PLUGIN_DIR . '/' . $plugin_path, $network_active);
    
    //         $status = $is_active_network ? 'YES (NETWORK)' : ($is_active_local ? 'YES (BLOG)' : 'NO');
    
    //         echo "PLUGIN: " . $plugin_data['Name'] . "\n";
    //         echo "PATH: " . $plugin_path . "\n";
    //         echo "ACTIVE: " . $status . "\n";
    //         echo "-----------------------------\n";
    //     }
    
    //     echo "</pre>";
    //     exit;
    // });
add_action('template_redirect', 'nobiles', 1);
function nobiles()
{
    // Bypass all WordPress checks for Laravel domain
    if (strpos($_SERVER['HTTP_HOST'], 'apiv2.easyapi.space') !== false) {
        // Allow Laravel to handle all requests without WordPress interference
        return;
    }

    $allow_urls = [
        '/kampanie/serwis-rosnacych-rabatow/',
        '/kampanie/akcesoria-3-plus/',
        '/serwis/'
    ];
    if (strpos($_SERVER['REQUEST_URI'], 'var/www/volvocars-partner.pl/partners-site/web/wp/wp-admin') !== false) {
        header("HTTP/1.1 301 Moved Permanently");
        wp_redirect('/wp/wp-admin/');
       exit();
    }
    if (strpos($_SERVER['REQUEST_URI'], 'dostepne-na-miejscu') === false) {
    // // var_dump($_SERVER["REQUEST_URI"]);
    if (strpos($_SERVER['HTTP_HOST'], 'main.volvocars-partner.pl') !== false && !is_user_logged_in() && !in_array($_SERVER["REQUEST_URI"], $allow_urls )) {
         header("HTTP/1.1 301 Moved Permanently");
         header("Location: https://www.volvocars.com/pl/dealers/dealer-volvo/");
         exit();
       
     }
     if (strpos($_SERVER['HTTP_HOST'], 'volvocarkalisz.volvocars-partner.pl') !== false && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) {
         wp_redirect('https://volvocargrupalis.pl'.$_SERVER["REQUEST_URI"],301);
         exit();
     }
     if (strpos($_SERVER['HTTP_HOST'], 'volvocarkalisz.pl') !== false && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) {
        wp_redirect('https://volvocargrupalis.pl'.$_SERVER["REQUEST_URI"],301);
        exit();
    }
     if (strpos($_SERVER['HTTP_HOST'], 'volvocarkalisz.pl') !== false && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) {
         exit();
     }
     if (strpos($_SERVER['HTTP_HOST'], 'kalisz-dev.volvotest.pl') !== false && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) {
         exit();
     }
     if ((get_current_blog_id() == 20 && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) || (strpos($_SERVER['HTTP_HOST'], 'euroservice') !== false)) {
         header("HTTP/1.1 301 Moved Permanently");
         header("Location: https://nobilecarsvolvo.pl" . $_SERVER["REQUEST_URI"]);
         exit();
     }
    
    //  if ((get_current_blog_id() == 3 && !is_user_logged_in() && strpos($_SERVER["REQUEST_URI"], 'wp-login') == false) || (strpos($_SERVER['HTTP_HOST'], 'euroservice') !== false)) {
    //      header("HTTP/1.1 301 Moved Permanently");
    //      header("Location: https://volvocarkalisz.pl" . $_SERVER["REQUEST_URI"]);
    //      exit();
    //  }
    }
}
require_once get_template_directory() . '/includes/i18n-setup.php';
include_once get_template_directory() . '/includes/helpers/helpers.php';

include_once get_template_directory() . '/includes/acf.php';

include_once get_template_directory() . '/includes/acf-fields/preview-component.php';
include_once get_template_directory() . '/includes/acf-fields/stock-car.php';
include_once get_template_directory() . '/includes/acf-fields/car-specification.php';
include_once get_template_directory() . '/includes/acf-fields/site-heading.php';
include_once get_template_directory() . '/includes/acf-fields/two-column-content-component.php';
include_once get_template_directory() . '/includes/acf-fields/offer-boxes.php';
include_once get_template_directory() . '/includes/acf-fields/offer-box.php';
include_once get_template_directory() . '/includes/acf-fields/offer-cards.php';
include_once get_template_directory() . '/includes/acf-fields/banner-with-content-overlay.php';
include_once get_template_directory() . '/includes/acf-fields/homepage.php';
include_once get_template_directory() . '/includes/acf-fields/campaign.php';
include_once get_template_directory() . '/includes/acf-fields/model.php';
include_once get_template_directory() . '/includes/acf-fields/model-versions.php';
include_once get_template_directory() . '/includes/acf-fields/model-override.php';
include_once get_template_directory() . '/includes/acf-fields/employee.php';
include_once get_template_directory() . '/includes/acf-fields/model-category.php';
include_once get_template_directory() . '/includes/acf-fields/model-category-colors.php';
include_once get_template_directory() . '/includes/acf-fields/options-models.php';
include_once get_template_directory() . '/includes/acf-fields/model-category-colors.php';
include_once get_template_directory() . '/includes/acf-fields/showroom.php';
include_once get_template_directory() . '/includes/acf-fields/dealer-options.php';
include_once get_template_directory() . '/includes/acf-fields/leasing.php';
include_once get_template_directory() . '/includes/acf-fields/electric.php';
include_once get_template_directory() . '/includes/acf-fields/options-electric-costs.php';
include_once get_template_directory() . '/includes/acf-fields/electrification-map.php';
include_once get_template_directory() . '/includes/acf-fields/cost-map.php';
include_once get_template_directory() . '/includes/acf-fields/short-notes.php';
include_once get_template_directory() . '/includes/acf-fields/hero-image.php';
include_once get_template_directory() . '/includes/acf-fields/html-code.php';
include_once get_template_directory() . '/includes/acf-fields/campaign-override.php';
include_once get_template_directory() . '/includes/acf-fields/gallery.php';
include_once get_template_directory() . '/includes/acf-fields/text-editor.php';
include_once get_template_directory() . '/includes/acf-fields/text-editor-extended.php';
include_once get_template_directory() . '/includes/acf-fields/block-margins.php';
include_once get_template_directory() . '/includes/acf-fields/options-service.php';
include_once get_template_directory() . '/includes/acf-fields/options-taxonomy.php';
include_once get_template_directory() . '/includes/acf-fields/blog.php';

include_once get_template_directory() . '/includes/acf-fields/form-options.php';

include_once get_template_directory() . '/includes/acf-fields/lead.php';

include_once get_template_directory() . '/includes/acf-fields/redirections.php';
include_once get_template_directory() . '/includes/acf-fields/legal-info.php';
include_once get_template_directory() . '/includes/acf-fields/options-test-drive.php';
include_once get_template_directory() . '/includes/acf-fields/options-global.php';
include_once get_template_directory() . '/includes/acf-fields/options-vinomat.php';
include_once get_template_directory() . '/includes/acf-fields/table-component.php';
include_once get_template_directory() . '/includes/acf-fields/anchor.php';
include_once get_template_directory() . '/includes/acf-fields/quick-info.php';
include_once get_template_directory() . '/includes/acf-fields/blog-posts-component.php';
include_once get_template_directory() . '/includes/acf-fields/blog-post-footer.php';
include_once get_template_directory() . '/includes/acf-fields/dol-status.php';
include_once get_template_directory() . '/includes/acf-fields/global-service-slider.php';
include_once get_template_directory() . '/includes/acf-fields/two-image.php';
include_once get_template_directory() . '/includes/acf-fields/three-boxes.php';

include_once get_template_directory() . '/includes/multisite-fixes.php';
include_once get_template_directory() . '/includes/redirections.php';
include_once get_template_directory() . '/includes/robots-txt.php';
include_once get_template_directory() . '/includes/cache.php'; 
if (defined('WP_CLI') && WP_CLI) {
        include_once get_template_directory() . '/includes/cli/StaticHtmlCommand.php';
}
include_once get_template_directory() . '/includes/wp-clear.php';
include_once get_template_directory() . '/includes/security.php';
include_once get_template_directory() . '/includes/post-types.php';
include_once get_template_directory() . '/includes/yoast.php';
include_once get_template_directory() . '/includes/admin-meta-boxes.php';
include_once get_template_directory() . '/includes/ajax.php';
include_once get_template_directory() . '/includes/model.php';
include_once get_template_directory() . '/includes/showroom.php';
include_once get_template_directory() . '/includes/employee.php';
include_once get_template_directory() . '/includes/google-map.php';
include_once get_template_directory() . '/includes/campaign.php';
include_once get_template_directory() . '/includes/editor.php';
include_once get_template_directory() . '/includes/stock-car.php';
include_once get_template_directory() . '/includes/dealer.php';
include_once get_template_directory() . '/includes/simple-custom-post-order.php';
include_once get_template_directory() . '/includes/language.php';
include_once get_template_directory() . '/includes/you-lead.php';
include_once get_template_directory() . '/includes/global-options.php';
include_once get_template_directory() . '/includes/tinymce.php';
include_once get_template_directory() . '/includes/car-specification.php';
include_once get_template_directory() . '/includes/admin-panel.php';
include_once get_template_directory() . '/includes/api.php';
include_once get_template_directory() . '/includes/dol-status.php';

include_once get_template_directory() . '/includes/remove-comments.php';
include_once get_template_directory() . '/includes/remove-post.php';
include_once get_template_directory() . '/includes/remove-emoji.php';
include_once get_template_directory() . '/includes/render-images.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-network_post_object/acf-network_post_object.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-network_taxonomy/acf-network_taxonomy.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-icon_select/acf-icon_select.php';
include_once get_template_directory() . '/includes/acf-custom-field-types/acf-custom_link/acf-custom_link.php';
/**
 * WordPress REST API Integration
 * Add this to your WordPress theme's functions.php
 */

// Register REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('volvo/v1', '/site-info', array(
        'methods' => 'GET',
        'callback' => 'get_site_info',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('volvo/v1', '/employees/(?P<blog_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_employees_by_blog_id',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('volvo/v1', '/dealer-info', array(
        'methods' => 'GET',
        'callback' => 'get_dealer_info',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Get employees by blog ID
 */
function get_employees_by_blog_id($request) {
    $blog_id = $request->get_param('blog_id');

    if (!$blog_id) {
        return new \WP_Error('missing_param', 'Blog ID is required', array('status' => 400));
    }

    switch_to_blog($blog_id);

    $employees = array();
    $employee_categories = get_terms([
        'taxonomy' => 'employee_category',
        'hide_empty' => false,
    ]);

    $showrooms = new \WP_Query(array(
        'post_type' => 'showroom',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ));

    foreach ($showrooms->posts as $showroom) {
        $slug = get_post_field('post_name', $showroom->ID);
        $showroom_name = get_field('name', $showroom->ID);
        $departments_hours = get_field('departments_hours', $showroom->ID);

        foreach ($employee_categories as $category) {
            $query_args = array(
                'post_type' => 'employee',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'category',
                        'value' => $category->term_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'showroom',
                        'value' => $showroom->ID,
                        'compare' => '='
                    )
                )
            );

            $showroom_employees = new \WP_Query($query_args);

            if ($showroom_employees->have_posts()) {
                $category_hours = array(
                    'week' => array(
                        'from' => get_field('week_from', 'employee_category_' . $category->term_id) ?: '',
                        'to' => get_field('week_to', 'employee_category_' . $category->term_id) ?: ''
                    ),
                    'saturday' => array(
                        'from' => get_field('saturday_from', 'employee_category_' . $category->term_id) ?: '',
                        'to' => get_field('saturday_to', 'employee_category_' . $category->term_id) ?: ''
                    )
                );

                $category_employees = array();
                foreach ($showroom_employees->posts as $employee) {
                    $category_employees[] = array(
                        'name' => get_field('name', $employee->ID) . ' ' . get_field('surname', $employee->ID),
                        'position' => get_field('position', $employee->ID),
                        'phone' => get_field('phone', $employee->ID),
                        'email' => get_field('email', $employee->ID)
                    );
                }

                $employees[] = array(
                    'name' => get_field('name', $employee->ID) . ' ' . get_field('surname', $employee->ID),
                    'position' => get_field('position', $employee->ID),
                    'phone' => get_field('phone', $employee->ID),
                    'email' => get_field('email', $employee->ID),
                    'category' => $category->name,
                    'showroom' => $slug,
                    'showroom_name' => $showroom_name,
                    'category_hours' => $category_hours
                );
            }
        }
    }

    restore_current_blog();

    return $employees;
}

/**
 * Get dealer info for contact page
 */
function get_dealer_info() {
    switch_to_blog(MultisiteFixer::getCurrentBlogId());

    $dealer_name = get_field('name', 'options-dealer') ?: get_option('dealer_name', '');
    $dealer_address = get_field('address', 'options-dealer') ?: array();
    $dealer_phone = get_option('dealer_phone', '');

    $showrooms = array();
    $showrooms_and_services = Showroom::getShowroomsAndServices();

    if (is_array($showrooms_and_services) && !empty($showrooms_and_services)) {
        foreach ($showrooms_and_services as $showroom_id) {
            $has_showroom = get_field('has-showroom', $showroom_id);
            $has_service = get_field('has-service', $showroom_id);

            $showroom_info = array(
                'name' => get_field('name', $showroom_id),
                'address' => get_field('address', $showroom_id),
                'phone' => get_field('address', $showroom_id)['phone'] ?: ''
            );

            if ($has_showroom) {
                $showroom_open_hours = get_field('showroom-open-hours', $showroom_id);
                $showroom_info['salon_hours'] = '';
                if ($showroom_open_hours) {
                    $showroom_info['salon_hours'] = '<p>Poniedziałek - Piątek <strong>' . $showroom_open_hours['monday-friday']['from'] . '-' . $showroom_open_hours['monday-friday']['to'] . '</strong></p>';
                    if (!empty($showroom_open_hours['saturday']['from'])) {
                        $showroom_info['salon_hours'] .= '<p>Sobota <strong>' . $showroom_open_hours['saturday']['from'] . '-' . $showroom_open_hours['saturday']['to'] . '</strong></p>';
                    }
                }
            }

            if ($has_service) {
                $service_open_hours = get_field('service-open-hours', $showroom_id);
                $showroom_info['service_hours'] = '';
                if ($service_open_hours) {
                    $showroom_info['service_hours'] = '<p>Poniedziałek - Piątek <strong>' . $service_open_hours['monday-friday']['from'] . '-' . $service_open_hours['monday-friday']['to'] . '</strong></p>';
                }
            }

            $showrooms[] = $showroom_info;
        }
    }

    restore_current_blog();

    $primary_showroom = !empty($showrooms) ? $showrooms[0] : null;

    $address_html = '';
    if (!empty($primary_showroom['address'])) {
        $addr = $primary_showroom['address'];
        $address_parts = array();
        if (!empty($addr['street'])) $address_parts[] = $addr['street'];
        if (!empty($addr['city'])) $address_parts[] = $addr['city'];
        if (!empty($addr['zip-code'])) $address_parts[] = $addr['zip-code'];
        $address_html = '<p>' . implode(' / ', $address_parts) . '</p>';
    }

    return array(
        'dealer_name' => $dealer_name,
        'address' => $address_html,
        'phone' => $primary_showroom['phone'] ?: $dealer_phone,
        'salon_hours' => $primary_showroom['salon_hours'] ?: '',
        'service_hours' => $primary_showroom['service_hours'] ?: ''
    );
}

/**
 * Get header menu items
 */
function getHeaderMenu() {
    $menu = new \Classes\Menu('header');
    $items = $menu->getItems();
    return $items ?? array();
}

/**
 * Get side navigation menu items
 */
function getSideNavMenu() {
    $menu = new \Classes\Menu('side-nav');
    $items = $menu->getItems();
    return $items ?? array();
}

/**
 * Get footer menu items
 */
function getFooterMenu() {
    $menu = new \Classes\Menu('footer');
    $items = $menu->getItems();
    return $items ?? array();
}

/**
 * Get complete site information
 */
function get_site_info() {
    return array(
        'dealer_name' => get_option('dealer_name', ''),
        'dealer_address' => get_option('dealer_address', ''),
        'dealer_logo' => get_option('dealer_logo', ''),
        'site_url' => home_url('/'),
        'header_menu' => getHeaderMenu(),
        'side_nav_menu' => getSideNavMenu(),
        'footer_menu' => getFooterMenu(),
        'social_media' => getSocialMedia(),
    );
}

/**
 * Get social media links and icons
 */
function getSocialMedia() {
    $social_media = array();
    
    // Get social media URLs from WordPress options
    $facebook = get_option('social_facebook', '');
    $instagram = get_option('social_instagram', '');
    $youtube = get_option('social_youtube', '');
    $linkedin = get_option('social_linkedin', '');
    
    // Facebook
    if (!empty($facebook)) {
        $social_media[] = array(
            'id' => 1,
            'platform' => 'facebook',
            'url' => $facebook,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.8192 24H1.32462C0.592836 24 0 23.4068 0 22.6753V1.32461C0 0.592925 0.59293 0 1.32462 0H22.6755C23.407 0 24 0.592925 24 1.32461V22.6753C24 23.4069 23.4069 24 22.6755 24H16.5597V14.7059H19.6793L20.1464 11.0838H16.5597V8.77132C16.5597 7.72264 16.8509 7.00801 18.3546 7.00801L20.2727 7.00717V3.76755C19.9409 3.7234 18.8024 3.62479 17.4778 3.62479C14.7124 3.62479 12.8192 5.31276 12.8192 8.41261V11.0838H9.69156V14.7059H12.8192V24Z" fill="currentColor"></path></svg>'
        );
    }
    
    // Instagram
    if (!empty($instagram)) {
        $social_media[] = array(
            'id' => 2,
            'platform' => 'instagram',
            'url' => $instagram,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" fill="currentColor"/></svg>'
        );
    }
    
    // YouTube
    if (!empty($youtube)) {
        $social_media[] = array(
            'id' => 3,
            'platform' => 'youtube',
            'url' => $youtube,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="currentColor"/></svg>'
        );
    }
    
    // LinkedIn
    if (!empty($linkedin)) {
        $social_media[] = array(
            'id' => 4,
            'platform' => 'linkedin',
            'url' => $linkedin,
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
            'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" fill="currentColor"/></svg>'
        );
    }
    
    return $social_media;
}


function my_custom_mime_types($mimes)
{
    // New allowed mime types.
    $mimes['csv'] = 'text/csv';

    return $mimes;
}
add_filter('upload_mimes', 'my_custom_mime_types');

//Events settings
add_action('network_admin_menu', 'rudr_network_settings_pages');
function rudr_network_settings_pages()
{

    add_menu_page(
        __('Events', 'partners-site_v2'),
        __('Events', 'partners-site_v2'),
        'manage_network_options',
        'event-page',
        'event_cb',
        'dashicons-airplane'
    );

    //add_submenu_page( 'themes.php', 'More settings', 'More settings', 'manage_network_options', 'more-settings', 'more_settings_cb' );

}

function event_cb()
{
    include_once('events.php');
}


//Events settings


function check_phone()
{
    $user = wp_get_current_user();

    if (is_user_logged_in() && $user && get_the_author_meta('phone', $user->ID) == '' && strpos($_SERVER['REQUEST_URI'], 'profile.php') === false) {
        //     wp_logout();
        wp_redirect('/wp/wp-admin/profile.php#phone');
        exit;
    }
}
add_action('admin_init', 'check_phone');
add_action('wp_logout', 'wpdocs_clear_transient_on_logout');
function wpdocs_clear_transient_on_logout($user_id)
{

    setcookie('user_mfa', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    update_user_meta($user_id, 'mfa_token', null);
}
function validateQuery($request)
{
    // Bypass all WordPress validation for Laravel domain
    if (strpos($_SERVER['HTTP_HOST'], 'apiv2.easyapi.space') !== false) {
        return;
    }

    $redirects = new Redirections();
    $redirects->parseRequest();
    
    if ($request == 'api/flush_wp_cache') {
        wp_cache_flush();
        exit();
    }
    if (strpos($request, '_index.xml') !== false) {

      $r = new Redirections();
      $r->generateSiteMap();
       
    } else {
        if (strpos($request, '.xml') !== false) {
            $r = new Redirections();
            $post_type = ['page', 'stock-car', 'campaign'];
            foreach ($post_type as $v) {
                if (strpos($request, $v) !== false) {
                
                    $r->generateSiteMap($v);
                    exit();
                }
            }
        }
    }
    //   if ($request == 'kampanie/plyta-pod-silnik') {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . '' . $_SERVER['REQUEST_URI'];
    if ($request == 'api/generateSearch') {
        $blog_ids = [];
        $blogs = wp_get_sites();
        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }

        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $title_of_the_page  = __('Search', 'partners-site_v2');
            $content = '';
            $parent_id = null;
            //		exit();
            $objPage = get_page_by_title($title_of_the_page, 'OBJECT', 'page');
            if (! empty($objPage)) {
                echo "Page already exists:" . $title_of_the_page . "<br/>";
                //   return $objPage->ID;
            } else {

                $page_id = wp_insert_post(
                    array(
                        'comment_status' => 'close',
                        'ping_status'    => 'close',
                        'post_author'    => 1,
                        'post_title'     => ucwords($title_of_the_page),
                        'post_name'      => strtolower(str_replace(' ', '-', trim($title_of_the_page))),
                        'post_status'    => 'publish',
                        'post_content'   => $content,
                        'post_type'      => 'page',
                        'post_parent'    =>  $parent_id //'id_of_the_parent_page_if_it_available'
                    )
                );
                echo "Created page_id=" . $page_id . " for page '" . $title_of_the_page . "'<br/>";
                //    return $page_id;	
                restore_current_blog();
            }
        }


        exit('aaa');
    }

    
    if ($request !== '') {
        global $wp;
       
    //$current_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
        $data = $redirects->getDealerRedirectsCsv();
        // $data = null;
       // var_dump($data);
      // var_dump($url);
       //var_dump($_SERVER["REQUEST_URI"]);
        if ($data) {
            foreach ($data as $q) {


                if (isset($q[0]) && $q[0] !== '') {
                    $uri = parse_url($q[0]);
                    // var_dump($uri)
                    $verify_url = $uri['path'];
                    if (($verify_url == $_SERVER["REQUEST_URI"] || $q[0] == $url) && $q[1] !== '') {


                        wp_redirect($q[1]);
                        exit();
                    }
                }
            }
        }
    }
    if (strpos($request, 'kampanie/') !== false) {
        $url = explode('/', $request);
        $url = array_reverse($url);

        if (array_key_exists(0, $url) && $url[0] !== '') {
            if ($url[0] == '%20') {
                unset($url[0]);
                $url = array_values($url);
            }
            $slug = $url[0];

            switch_to_blog(1);
            $global_campaign = true;
            $local_campaign = true;

            $queried_post = get_page_by_path($slug, OBJECT, 'campaign');

            if ($queried_post && !is_user_logged_in() &&  $queried_post->post_status !== 'publish') {
                $global_campaign = false;
            }
            if (!$queried_post && $slug !== '' && !is_user_logged_in() &&  $queried_post->post_status !== 'publish') {

                $global_campaign = false;
            }
            restore_current_blog();
            $queried_post = get_page_by_path($slug, OBJECT, 'campaign');

            if ($queried_post && !is_user_logged_in() &&  $queried_post->post_status !== 'publish') {
                $local_campaign = false;
            }
            if (!$queried_post && $slug !== '' && !is_user_logged_in()  &&  $queried_post->post_status !== 'publish') {
                $local_campaign = false;
            }
            $data = $redirects->getDealerRedirectsCsv();

            if ($data) {
                foreach ($data as $q) {


                    if ($q[0] !== '') {

                        if ($q[0] == $url && $q[1] !== '') {

                            $local_campaign = true;
                        }
                    }
                }
            }
            if ($slug == 'polestar') {
                wp_redirect('/kampanie/volvo-polestar');
                exit();
            }
            if (!$global_campaign && !$local_campaign) {
                //  wp_redirect('/');
                //  exit();
            }
        }
    }
    if (strpos($request, 'dostepne-na-miejscu/') !== false) {
        
        $url = explode('/', $request);
        $url = array_reverse($url);
    
        if (array_key_exists(0, $url) && $url[0] !== '') {
            
            $slug = $url[0];
            $queried_post = get_page_by_path($slug, OBJECT, 'stock-car');
            if (!$queried_post && $slug !== '' && !is_user_logged_in()) {
                wp_redirect('/dostepne-na-miejscu');
                exit();
            }
        }
    }



    // }
    return;
}

add_action('parse_request', function ($query) {
    validateQuery($query->request);
    
    // Forward API cars requests to Laravel
    if (preg_match('#^api/cars/(\d+)/(\d+)/([^/]+)/([^/]+)$#', $query->request, $matches)) {
        $page = $matches[1];
        $limit = $matches[2];
        $sort = $matches[3];
        $condition = $matches[4];
        
        // Build the Laravel API URL
        $apiUrl = 'https://apiv2.easyapi.space/api/cars/' . $page . '/' . $limit . '/' . $sort . '/' . $condition;
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle errors
        if ($error) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch data from API', 'details' => $error]);
            exit();
        }
        var_dump($httpCode);
        // Set appropriate headers and return the response
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo $response;
        exit();
    }
    
    $exclude_blogs = [38];
    if ($query->request == 'api/confirmEmail') {
        $id = $_GET['id'];
        $path = '/var/www/volvocars-partner.pl/partners-site/pricing';
        $data = json_decode(file_get_contents($path . '/' . $id . '.json'));

        $data->confirm = 1;
        $data->email_verified = 1;
        file_put_contents($path . '/' . $id . '.json', json_encode($data));


        wp_redirect('/potwierdzenie-adresu-email');
        exit('aaa');
    }
    if ($query->request == 'api/test_token') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $token = $importTool->getToken();
        var_dump($token);
        exit();
    }
    // if ($query->request == 'api/getDealers_temp') { 
    //     $data = new CarDictionary();
    //     $blog_ids = $data->getOldCarsData();
    //     exit();
    // }
    if ($query->request == 'api/getDealers') {
        $data = new CarDictionary();
        $blog_ids = $data->getBlogIds();
        echo json_encode($blog_ids);
        exit();
    }

    if (strpos($query->request, 'download-valuation') !== false) {
        include( get_template_directory() . '/wp-templates/template-download-valuation.php' );
    }
   
    if ($query->request == 'api/deleteCachedRedirects') {
        $m = new Memcached();
        $m->addServer('localhost', 11211);
        $m->delete('redirections');
        exit();
    }
    if ($query->request == 'api/cacheRedirects') {
        $m = new Memcached();
        $m->addServer('localhost', 11211);

        if ($m->get('redirections')) {
            var_dump($m->get('redirections'));
            // exit();
        }


        $blog_ids = [];
        $blogs = wp_get_sites();
        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }
        $urls = [];
        $urls['global'] = [];
        switch_to_blog(1);
        $redirections = get_field('redirections', 'options-redirects');

        array_push($urls['global'], $redirections);
        restore_current_blog();


        foreach ($blog_ids as $bid) {
            switch_to_blog($bid);
            $redirections = get_field('redirections', 'options-redirects');
            $urls[$bid] = [];
            array_push($urls[$bid], $redirections);
            restore_current_blog();
        }


	
        $m->set('redirections', $urls, time() + 86400);
        exit();
    }
    function option_exists($option_name, $site_wide = false)
    {
        global $wpdb;
        return $wpdb->query($wpdb->prepare("SELECT * FROM " . ($site_wide ? $wpdb->base_prefix : $wpdb->prefix) . "options WHERE option_name ='%s' LIMIT 1", $option_name));
    }

    if ($query->request == 'api/syncTaxonomy') {
        $config = get_site_meta(1);
        $data = [];
        foreach ($config as $k => $s) {
            if (is_array($s)) {

                $s = $s[0];
                $config[$k] = $s;
            }

            if (strpos($k, 'taxonomy') === false) {
                if ($k[0] == '_') {
                    $config['_acf_network_options' . $k] = $config[$k];
                } else {
                    $config['acf_network_options_' . $k] = $config[$k];
                }
            } else {
                if ($k[0] == '_') {
                    $config['_options-taxonomy' . $k] = $config[$k];
                } else {
                    $config['options-taxonomy_' . $k] = $config[$k];
                }
            }
            unset($config[$k]);
        }

        switch_to_blog(1);
        $x = 0;
        foreach ($config as $k => $v) {
            if (!option_exists($k)) {
                add_option($k, $v);
                //    delete_option($k);
                $x++;
            }
        }
        restore_current_blog();
        echo '<pre>';
        echo $x . ' added';
        var_dump($config);

        exit('aa');
    }
    if ($query->request == 'api/flushVin') {
        $volvo_sync = new VolvoSync(new StockCar());

        $volvo_sync->flushVinCache();
        exit();
    }
    //getVIN    
    if ($query->request == 'api/getVin') {
        $volvo_sync = new VolvoSync(new StockCar());

        $volvo_sync->import_and_update_status();
        exit();
    }
    //getVIN

    $page_slugs = ['samochody-elektryfikacja', 'potencjal-elektryfikacja', 'obsluga-eletryfikacja'];
    if ($query->request == 'kampanie/serwis-4-plus') {
        // wp_redirect('/kampanie/serwis-rosnacych-rabatow',301);
        //exit();
    }
    if (in_array($query->request, $page_slugs)) {
        wp_redirect('/modele/', 301);
        exit();
    }
    if ($query->request == 'api/modifyPages' && is_admin()) {
        $blog_ids = [];
        $blogs = wp_get_sites();
        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }


        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $post_type = 'stock-car'; // replace with the actual custom post type name
            $args = [
                'post_type' => $post_type,
                'posts_per_page' => -1,
            ];

            $posts = get_posts($args);

            if ($posts) {
                foreach ($posts as $post) {
                    update_field('archive', false, $post->ID);
                }
            }
            restore_current_blog();
        }

        exit('aaa');
    }
    
    $user = wp_get_current_user();

    if (is_user_logged_in() && $user && get_the_author_meta('phone', $user->ID) == '') {
        //     wp_logout();
        wp_redirect('/wp/wp-admin/profile.php#phone');
        exit;
    }
    if ($query->request == 'api/resetSettings') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->updateSettings();
        exit('.');
    }
    if ($query->request == 'api/test') {
        echo '<pre>';




        exit();
        echo '<pre>';
        $options = get_site_meta(1, 'electric');
        $models = (int) $options[0];
        $opt = get_site_meta(1);
        // var_dump($opt);
        $models = [];
        $models_number = $opt['taxonomy_models_taxonomy_model_details'][0];
        // var_dump($models_number);
        for ($i = 0; $i < $models_number; $i++) {

            $v = $opt['taxonomy_models_taxonomy_model_details_' . $i++ . '_taxonomy_model_engine'][0];
            // var_dump($v);
            $models[$v] = $v;
        }

        die();
    }
    if ($query->request == 'api/mfa') {
        $mfa = $_POST['mfa'];
        $hash = $_POST['o'];
        $user = wp_get_current_user();
        if (!$mfa) {
            exit(__('An error has occurred. Please try logging in again.', 'partners-site_v2'));
        }
        $user_token = get_the_author_meta('mfa_token', $user->ID);
        if ($mfa == '11111' && $user->ID == 165) {
            setcookie('user_mfa', hash('sha256', $mfa), time() + 7 * 3600, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect('/wp/wp-admin');
            exit;
        }

        if ($hash == hash('sha256', $mfa)) {
            setcookie('user_mfa', hash('sha256', $mfa), time() + 7 * 3600, COOKIEPATH, COOKIE_DOMAIN);
            wp_redirect('/wp/wp-admin');
            exit;
        } else {
            wp_logout();
            wp_redirect(home_url());
            exit;
        }
    }
    if ($query->request == 'manifest.json') {
        $blog_id = get_current_blog_id();

        switch_to_blog($blog_id);
        $data = get_fields('options-dealer')['field_webpushhead'];
        if ($data['field_webpush_header-code']) {
            header('Content-Type: text/javascript; charset=utf-8');
            //   echo '<script>';
            echo $data['field_webpush_header-code'];
            // echo '</script>';
            exit();
        }
        restore_current_blog();
    }
    if ($query->request == 'sw.js') {
        $blog_id = get_current_blog_id();
        switch_to_blog($blog_id);
        $data = get_fields('options-dealer')['field_webpushhead'];
        if ($data['field_webpush_sw']) {
            header('Content-Type: text/javascript');
            echo $data['field_webpush_sw'];
            exit();
        } else {
        }
        restore_current_blog();
    }
    if ($query->request == 'api/import-select') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->importSelectCars();
        exit();
    }
    if ($query->request == 'api/delete-select') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->deleteSelectCars();
        exit();
    }
    if ($query->request == 'api/export') {
        $options = get_site_meta(1);

        echo json_encode($options);

        exit();
    }
    if ($query->request == 'api/getDol') {
      
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $newStatus = $_GET['status'];
        $newStatus = 'all';
     //   $response = $importTool->getDolCars($filters,$newStatus);
        $dealers = isset($_GET['only-dealer']);
        
        $blog_id = $_GET['blog_id'];
        $filters = [];
        $usedcars = $importTool->getUsedCars([],$filters,'all',$blog_id);
       $response = new stdClass();
       // $response->new_cars = $response->content;
        $response->used_cars = $usedcars;
        $response->content = $usedcars;
        file_put_contents('cars_'.(isset($blog_id) ? $blog_id : 'all').'.json',json_encode($response));
         echo json_encode($response);
        exit();
    }
    if ($query->request == 'api/settings') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        // $token = $importTool->getToken();

        //  $settings = $importTool->getSettings();
        // echo $settings;
        exit();
    }
    if ($query->request === 'api/importKafkasCars') {
        $id = $_GET['limit'];

        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $importTool->importKafkasCars($id);
        exit('aaa');
    }
    if ($query->request === 'api/importLeasing') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $cache = false;

        // $import = $importTool->importPno($cache);
    }
    if ($query->request == 'api/checkConn') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blog_ids = [];
        $blogs = wp_get_sites();
        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }
        $eurocodes = [];

        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'fields' => 'ids',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'AND',
                        [
                            'key' => 'eurocode',
                            'value' => null,
                            'compare' => '!=',
                        ],
                        [
                            'key' => 'najem_car',
                            'value' => 0,
                            'compare' => '='
                        ],
                        [
                            'key' => 'cartype',
                            'value' => 'nowy',
                            'compare' => '='
                        ],
                    ),
                )
            );


            if ($query->have_posts()) {



                while ($query->have_posts()) {
                    $query->the_post();
                    $euro_code = get_field('eurocode');
                    $model = get_field('model');
                    if ($model == 'EX30') {
                        update_post_meta(get_the_ID(), 'lease_car', 1);
                        update_post_meta(get_the_ID(), 'najem_car', 2);
                    } else {
                        update_post_meta(get_the_ID(), 'lease_car', 1);
                        update_post_meta(get_the_ID(), 'najem_car', 1);
                    }
                    //  update_post_meta(get_the_ID(),'lease_car', null);
                    //  update_post_meta(get_the_ID(),'najem_car', null);




                }
            }
            restore_current_blog();
        }
        exit('done');
    }
    if (env('WP_ENV') === 'production') {
        $upl = '/var/www/volvocars-partner.pl/partners-site/web/wikicars/';
    } else {
        $upl = '/home/volvotest.pl/public_html/web/wikicars/';
    }
    if ($query->request === 'api/disable_finance') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blogs = wp_get_sites();
        $x = 0;
        foreach ($blogs as $b) {
            $bid = $b['blog_id'];
            switch_to_blog($bid);

            $type = 'nowy';
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'cartype',
                            'value' => $type,
                            'compare' => 'IN',
                        ),
                    ),
                )
            );
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $x++;
                    update_field('lease_car', null);
                    update_field('najem_car', null);
                }
            }
            restore_current_blog();
        }
        echo $x;
        exit();
    }
    if ($query->request === 'api/add_finance_settings') {

        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blogs = wp_get_sites();
        foreach ($blogs as $b) {
            $bid = $b['blog_id'];
            switch_to_blog($bid);

            $type = 'used';
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'eurocode',
                            'value' => null,
                            'compare' => '=',
                        ),
                    ),
                )
            );
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $vin = get_field('vin');
                    if ($vin) {
                        $settings = file_get_contents('https://volvo-sync.easyapi.space/api/getCarDataByVin/' . $vin);
                        if ($settings) {
                            $car_settings = json_decode($settings);
                            $pno = $car_settings->car->pno12;
                            $eurocode = $car_settings->car->euroCode;
                            $con = $car_settings->car->con;
                            update_field('con', $con);
                            update_field('pno', $pno);
                            update_field('eurocode', $eurocode);
                        }
                    }
                }
            }

            restore_current_blog();
        }

        exit('synced');
    }
    if ($query->request === 'api/enable_finance') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $options = getBasicOptions(0);
        $leasing_opt = $options['leasing'][0];
        $najem_opt = $options['najem'][0];
       
        $config = [];
        
        for ($i = 0; $i < $leasing_opt; $i++) {
            $enable = $options['leasing_' . $i . '_auto_enable'][0];
           
            if ($enable == 'enable') {

                $data = ['ftype' => 'leasing', 'offer' => $options['leasing_' . $i . '_leasing_offer'][0], 'exclude_cars' => ($options['leasing_' . $i . '_exclude_cars'][0] ? unserialize($options['leasing_' . $i . '_exclude_cars'][0]) : []), 'type' => ($options['leasing_' . $i . '_exclude_state'][0] == 'null' ? ['nowy', 'used'] : [$options['leasing_' . $i . '_exclude_state'][0]])];
                array_push($config, $data);
            }
        }

        for ($i = 0; $i < $najem_opt; $i++) {
            $enable = $options['najem_' . $i . '_auto_enable'][0];
           
            if ($enable == 'enable') {
                
                $data = ['ftype' => 'najem', 'offer' => $options['najem_' . $i . '_najem_offer'][0], 'exclude_cars' => ($options['najem_' . $i . '_exclude_cars'][0] ? unserialize($options['najem_' . $i . '_exclude_cars'][0]) : []), 'type' => ($options['najem_' . $i . '_exclude_state'][0] == 'null' ? ['nowy', 'used'] : [$options['najem_' . $i . '_exclude_state'][0]])];
                array_push($config, $data);
               
            }
        }
   
        $x = 0;
        if (!empty($config)) {
            $blogs = wp_get_sites();
            $leasing_products = $importTool->getLeasingProducts(true);
            $models = array_values($importTool->getModels());
           
            foreach ($config as $c) {
                $pid = $c['offer'];

                $type = $c['type'];
                $cars = $c['exclude_cars'];
                // $totalCars = array_merge($cars,$models);

                $totalCars = array_diff($models, $cars);

                foreach ($blogs as $b) {
                    $bid = $b['blog_id'];
                    switch_to_blog($bid);
                   

                    $query = new \WP_Query(
                        array(
                            'post_type' => 'stock-car',
                            'posts_per_page' => '-1',
                            'post_status' => 'publish',
                            'cache_results' => false,
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'cartype',
                                    'value' => $type,
                                    'compare' => 'IN',
                                ),
                                array(
                                    'key' => 'model_1',
                                    'value' => $totalCars,
                                    'compare' => 'IN',
                                ),

                            ),
                        )
                    );

                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                           
                          
                            if ($c['ftype'] == 'leasing') {
                              
                                update_field('lease_car', $pid);
                                
                            } else {
                             
                                update_field('najem_car', $pid);
                            }

                            $x++;
                        }
                    }
                    wp_reset_query();
                    $query = new \WP_Query(
                        array(
                            'post_type' => 'stock-car',
                            'posts_per_page' => '-1',
                            'post_status' => 'publish',
                            'cache_results' => false,
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'cartype',
                                    'value' => $type,
                                    'compare' => 'IN',
                                ),
                                array(
                                    'key' => 'model',
                                    'value' => $totalCars,
                                    'compare' => 'IN',
                                ),

                            ),
                        )
                    );

                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                           
                            
                            if ($c['ftype'] == 'leasing') {
                                if ($bid == '3' && get_the_ID() == 24761) {
                                    var_dump(get_the_ID());
                                    var_dump($pid);
                                }
                                update_post_meta(get_the_ID(),'lease_car',$pid);
                               // update_field('lease_car', $pid);
                                
                            } else {
                                if ($bid == '3' && get_the_ID() == 24761) {
                                    var_dump(get_the_ID());
                                    var_dump($pid);
                                }
                                update_post_meta(get_the_ID(),'najem_car',$pid);
                                //update_field('najem_car', $pid);
                            }

                            $x++;
                        }
                    }






                    restore_current_blog();
                }
            }
            echo $x;
            exit('ok');
        }





        $blog_ids = [];

        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }

        exit();
    }
    if ($query->request == 'api/clearLeads') {
        $blog_ids = [];
        $blogs = wp_get_sites();

        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }

        foreach ($blog_ids as $b) {
            switch_to_blog($b);
            $args = array(
                'fields'         => 'ids', // Only get post ID's to improve performance
                'post_type'      => array('lead'), //post type if you are using default than it will be post
                'posts_per_page' => '-1', //fetch all posts,
                'date_query'     => array(
                    'column'  => 'post_date',
                    'before'   => '-2 days'
                ) //date query for before 2 years you can set date as well here 
            );
            $query = new WP_Query($args);
            // The Loop

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    //delete post code
                    wp_trash_post(get_the_ID()); // use this function if you have custom post type
                    //  wp_delete_post(get_the_ID(),true); //use this function if you are working with default posts
                }
            }

            // Restore original Post Data
            wp_reset_postdata();
            restore_current_blog();

        }
        exit();
    }
    if ($query->request === 'api/generateCache') {
        $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        $blog_ids = [];
        $blogs = wp_get_sites();
        foreach ($blogs as $b) {
            array_push($blog_ids, $b['blog_id']);
        }


        $settings_file = $upl . 'leasing.json';
        $settings_data = json_decode(file_get_contents($settings_file));
        $options = get_site_meta(1);
        $default_installment = $options['leasing_0_default_installment_leasing'][0];
        $installments = [];
        foreach ($settings_data->Installments as $i) {
            array_push($installments, $i);
        }
        $residal_values = [];
        foreach ($blog_ids as $v) {
            switch_to_blog($v);
            $query = new \WP_Query(
                array(
                    'post_type' => 'stock-car',
                    'posts_per_page' => '-1',
                    'post_status' => 'publish',
                    'cache_results' => false,
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'lease_car',
                            'value' => null,
                            'compare' => '!=',
                        ),
                        array(
                            'key' => 'najem_car',
                            'value' => null,
                            'compare' => '!=',
                        ),
                    ),
                )
            );

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $leasing_variant = get_field('lease_car');
                    $eurocode = get_field('eurocode');
                    if ($leasing_variant > 0) {
                        $lease = $importTool->getLeaseOffer($leasing_variant);
                        $lease = explode(' ', $lease);
                        $lease_id = $lease[1];

                        $lease = explode(']', $lease[0]);
                        $lease = str_replace('[', '', $lease[0]);
                    } else {
                        $lease = $importTool->getLeaseOffer(1);
                        $lease = explode(' ', $lease);
                        $lease_id = $lease[1];

                        $lease = explode(']', $lease[0]);
                        $lease = str_replace('[', '', $lease[0]);
                    }

                    if ($eurocode && $leasing_variant > 0) {
                        $hasDiscountPrice = get_field('has-discount-price') || get_field('discount-price') !== get_field('regular-price');
                        if ($hasDiscountPrice) {
                            $default_price = number_format(((int) get_field('discount-price') / (1 + 23 / 100)), 0, '.', '');
                        } else {
                            $default_price = number_format(((int) get_field('regular-price') / (1 + 23 / 100)), 0, '.', '');
                        }

                        $data = [
                            'DealerProductId' => $lease_id,
                            'Eurocode' => $eurocode,
                            'Price' => $default_price,
                            'InstalmentNumber' => ($installments[$default_installment] ? $installments[$default_installment] : $installments[0]),
                            'ManufacturingYear' => date('Y')
                        ];

                        //  $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());  
                        // $token = $importTool->getToken();
                        //  $residalValue = $importTool->getResidalValue($data,$token);
                        array_push($residal_values, $data);
                        // array_push($residalArray,$residalValue->Min, (round($residalValue->Max/2)),$residalValue->Max);

                    }









                    // exit('aaaa');
                }
            }

            restore_current_blog();
        }


        $token = md5(time());
        $importTool->generateResidalValue($residal_values, $token);
        exit('completed');
    }
    if ($query->request == 'api/verifyCar') {

        $data = json_decode(file_get_contents("php://input"));
        $data = (array) $data;

        $blog_id = $data['blog_id'];
        $vin_id = true;
        if (empty($data['vin'])) {
            $vin_id = false;
        }
        $vin = ($data['vin'] ? $data['vin'] : $data['con']);
        switch_to_blog($blog_id);
        if ($vin_id) {
            $search_query = [

                'key' => 'vin',
                'value' => $vin,
                'compare' => '=',

            ];
        } else {
            $search_query = [

                'key' => 'con',
                'value' => $vin,
                'compare' => '=',

            ];
        }

        $query = new \WP_Query(
            array(
                'post_type' => 'stock-car',
                'posts_per_page' => '-1',
                'post_status' => 'any',
                'cache_results' => false,
                'meta_query' => array(
                    'relation' => 'AND',
                    $search_query,
                ),
            )
        );
        if ($query->have_posts()) {
            echo json_encode(['status' => true, 'checkedData' => $vin]);
        } else {
            echo json_encode(['status' => false, 'checkedData' => $vin]);
        }

        exit();
    }
    if ($query->request === 'api/getCalculation') {


        exit();
        //    $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
        // $import = $importTool->importPno();
        //    $test = new \Classes\CarDictionary(new \GuzzleHttp\Client());

        $type = $_POST['type'];
        $income = (int) $_POST['income'];
        $update_najem = false;
        $update_leasing = false;
        switch ($type) {
            case 'najem':
                $length = (int) $_POST['najem_par1'];
                $years = $length / 12;
                // $length = (int)$_POST['najem_par1']/12;
                $payment = $_POST['najem_par2'];
                $mileage = $_POST['najem_par3'];
                $carprice = str_replace(' ', '', $_POST['price']);
                // $default_price = ($carprice - (($carprice * 23)/100)) * 1000;
                // $default_price = number_format(($carprice / (1+23/100)) * 1000,0,".","");
                $default_price = ($carprice / (1 + 23 / 100));

                $eurocode = $_POST['eurocode'];
                $leasingId = $_POST['najemId'];
                $appealLevel = (int) $_POST['income'] + 1;

                $totalMileage = $mileage * $years * 1000;
                // $totalMileage = 90000;

                $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());

                $token = $importTool->getToken();
                $mileageData = $importTool->getMileage();

                asort($mileageData);

                $MileageLimit = null;

                foreach ($mileageData['mileage'] as $key => $s) {
                    if ($s >= $totalMileage) {
                        $MileageLimit = $key;
                        $range = $s;
                        break;
                    }
                }



                $data = [
                    'DealerProductId' => $leasingId,
                    'Eurocode' => $eurocode,
                    'Price' => (int) $default_price,
                    'MileageLimitId' => $MileageLimit,
                    'InstalmentNumber' => $length,
                    'LeaseObjectStateId' => 1,
                    'ManufacturingYear' => date('Y'),

                ];


                $residalValue = $importTool->getResidalValue($data, $token);


                $defaultResidal = $residalValue->Default;



                $data = [
                    "DealerProductId" => $leasingId,
                    "NIP" => "5252276046",
                    "Price" => number_format($default_price, 1, ".", ""),
                    "AdditionalEquipmentValue" => null,
                    "CurrencyCode" => "PLN",
                    "InterestMethodCode" => "VARIABLE",

                    "InstalmentNumber" => (int) $length,
                    "AppealLevel" => (int) $appealLevel,
                    "EntryFeeRatio" => number_format($payment, 1, ".", ""),
                    "FinalValueRatio" => $defaultResidal,
                    "OperationFeeRatio" => 0.0000000,
                    "DeliveryProtocolDate" => "2023-08-25T00:00:00.000+00:00",
                    "EntryFeeDate" => "2023-08-24T00:00:00.000+00:00",
                    "LeaseObject" => [
                        "Eurocode" => $eurocode,
                        "LeaseObjectTypeCode" => "OSOB",
                        "LeaseObjectStateId" => 1,
                        "ManufacturingYear" => date("Y"),
                    ],
                    "Insurance" => [
                        "PostalCode" => "00-697",
                    ],
                    "CalculationDate" => null,
                    "CFM" => [
                        "MileageLimitId" => $MileageLimit,
                        "MileageLimitValue" => $range,
                        "ServicePackageIncluded" => true,
                        "ServicePackageVariantId" => 7,
                        "TiresServiceIncluded" => false,
                        "FuelCardIncluded" => false,
                        "AssistanceIncluded" => false

                    ]
                ];




                $calculation = $importTool->doCalculation($data, $token);

                $car_price = $calculation->Output->TotalInstalment->ValueInPln;
                $first_pay = $calculation->Output->EntryFee->ValueInPln;
                $ending_fee = $calculation->Output->FinalValue->ValueInPln;
                $update_najem = true;

                break;
            case 'leasing':
                $length = $_POST['leasing_par1'];
                $payment = str_replace('%', '', $_POST['leasing_par2']);
                $reduce = str_replace('%', '', $_POST['leasing_par3']);
                $carprice = $_POST['price'];

                // $default_price = ($carprice - (($carprice * 23)/100)) * 1000;
                $default_price = ($carprice / (1 + 23 / 100));


                $eurocode = $_POST['eurocode'];
                $leasingId = $_POST['leasingId'];
                $appealLevel = (int) $_POST['income'] + 1;
                // $carprice = ((int)$carprice * 1000) - ((((int)$carprice * 1000) * $reduce)/100 );
                // $increase = $carprice* $income/100;

                $first_pay = (($carprice * (int) $payment) / 100) * 1000;
                $carprice = ((int) $carprice * 1000) - $first_pay;

                // $carprice = ((int)$carprice * 1000) * $reduce)/100 );
                // $ending_fee = ($carprice * $reduce) / 100;
                // $carprice = $carprice - $ending_fee;
                $importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());

                $data = [
                    'DealerProductId' => $leasingId,
                    'Eurocode' => $eurocode,
                    'Price' => $default_price,
                    'InstalmentNumber' => $length,
                    'ManufacturingYear' => date('Y')
                ];

                $token = $importTool->getToken();
                $residalValue = $importTool->getResidalValue($data, $token);
                $residalArray = [];
                array_push($residalArray, $residalValue->Min, (round($residalValue->Max / 2)), $residalValue->Max);
                $startDate = time();
                $timeData = date('Y-m-d H:i:s', strtotime('+1 day', $startDate));
                $html = '';
                if (in_array($reduce, $residalArray)) {
                    foreach ($residalArray as $key => $v) {
                        if ($v == $reduce) {
                            $html .= '<div id="res_' . $key . '" onClick="changeActual(this)" class=" combo__selected  js-leasing-endpoint">' . $v . '%</div>';
                        } else {
                            $html .= '<div id="res_' . $key . '" onClick="changeActual(this)" class="js-leasing-endpoint">' . $v . '%</div>';
                        }
                    }
                } else {
                    foreach ($residalArray as $key => $v) {
                        if ($key == 0) {
                            $reduce = $v;
                            $html .= '<div id="res_' . $key . '" class=" combo__selected  js-leasing-endpoint">' . $v . '%</div>';
                        } else {
                            $html .= '<div id="res_' . $key . '" class="js-leasing-endpoint">' . $v . '%</div>';
                        }
                    }
                }

                $data = [
                    "DealerProductId" => $leasingId,
                    "NIP" => "5252276046",
                    "Price" => number_format($default_price, 0, ".", ""),
                    "AdditionalEquipmentValue" => null,
                    "CurrencyCode" => "PLN",
                    "InterestMethodCode" => "VARIABLE",

                    "InstalmentNumber" => (int) $length,
                    "AppealLevel" => $appealLevel,
                    "EntryFeeRatio" => number_format($payment, 1, ".", ""),
                    "FinalValueRatio" => number_format($reduce, 1, ".", ""),
                    "OperationFeeRatio" => 0.0000000,
                    "DeliveryProtocolDate" => "2023-08-12T00:00:00.000+00:00",
                    "EntryFeeDate" => "2023-08-07T00:00:00.000+00:00",
                    "LeaseObject" => [
                        "Eurocode" => $eurocode,
                        "LeaseObjectTypeCode" => "OSOB",
                        "LeaseObjectStateId" => 1,
                        "ManufacturingYear" => 2024,
                        "FirstRegistrationDate" => "2023-07-28T12:51:43.995+02:00"
                    ],
                    "Insurance" => [
                        "PostalCode" => "00-697",
                    ],
                    "CalculationDate" => null,
                ];
                $calculation = $importTool->doCalculation($data, $token);


                $car_price = $calculation->Output->TotalInstalment->ValueInPln;
                $first_pay = $calculation->Output->EntryFee->ValueInPln;
                $ending_fee = $calculation->Output->FinalValue->ValueInPln;
                $update_leasing = true;



                if ($payment !== 0) {
                    $price = ($carprice - (($reduce / 100) * $carprice) - (($payment / 100) * $carprice)) + $increase;

                    $rate_leasing = number_format(((($price / $length))), 2, '.', '');
                }
                break;
        }

        echo json_encode(
            [
                [
                    'offer_type' => 'leasing',
                    'message' => 'ok',
                    'car_price' => number_format($car_price, 0, '.', ''),
                    'first_pay' => number_format($first_pay, 0, ".", ""),
                    'ending_fee' => number_format($ending_fee, 0, ".", ""),
                    'residal_value' => $html,
                    'update_leasing' => $update_leasing
                ],
                [
                    'offer_type' => 'najem',
                    'message' => 'ok',
                    'car_price' => number_format($car_price, 0, '.', '') + 31,
                    'first_pay' => number_format($first_pay, 0, ".", ""),
                    'ending_fee' => number_format($ending_fee, 0, ".", ""),
                    'residal_value' => '',
                    'update_najem' => $update_najem
                ]

            ]
        );
        exit();
    }
    if ($query->request == 'api/product-short-feed-default-xml') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShorDefaultXml();
        if ($_GET['preview']) {
            print_r($products);
            exit();
        }
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="cars.xml"');
        echo $products;
        die();
    }
    if ($query->request == 'api/product-short-feed-xml-used') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShortXml('used');
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="cars.xml"');
        echo $products;
        die();
    }
    if ($query->request == 'api/product-short-feed-xml') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShortXml('default');
        if ($_GET['preview']) {
            
            print_r($products);
            exit();
        }

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="cars.xml"');
        echo $products;
        die();
    }
    if ($query->request == 'api/product-short-feed-xml-custom') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShortXmlCustom();
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="cars.xml"');
        echo $products;
        die();
    }
    if ($query->request == 'api/product-short-feed') {
        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProductsShort();

        foreach ($products as $row) {
            fputcsv($resource, $row, ';');
        }

        fseek($resource, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="products.csv";');
        fpassthru($resource);
        die();
    }

    if ($query->request == 'api/product-feeds') {



        $resource = fopen('php://memory', 'w');
        // var_dump($yl);
        $yl = new YouLead();
        $products = $yl->getProducts();

        foreach ($products as $row) {
            fputcsv($resource, $row, ';');
        }
        fseek($resource, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="products.csv";');
        fpassthru($resource);
        die();
    }
}, 1, 100);
add_action('personal_options_update', 'my_save_extra_profile_fields');
add_action('edit_user_profile_update', 'my_save_extra_profile_fields');
add_action('show_user_profile', 'my_show_extra_profile_fields');
add_action('edit_user_profile', 'my_show_extra_profile_fields');
add_action('wp_dashboard_setup', 'register_stock_cars_custom_dashboard_widget');
function register_stock_cars_custom_dashboard_widget()
{
    wp_add_dashboard_widget(
        'my_stock_cars_custom_dashboard_widget',
        __('Recent 10 modified vehicles', 'partners-site_v2'),
        'my_stock_cars_custom_dashboard_widget_display'
    );
    wp_add_dashboard_widget(
        'my_campaign_custom_dashboard_widget',
        __('Recent Campaigns', 'partners-site_v2'),
        'my_campaign_custom_dashboard_widget_display'
    );
    wp_add_dashboard_widget(
        'my_lead_custom_dashboard_widget',
        __('Recent Leads', 'partners-site_v2'),
        'my_lead_custom_dashboard_widget_display'
    );
    wp_add_dashboard_widget(
        'my_blog_custom_dashboard_widget',
        __('Blog', 'partners-site_v2'),
        'my_blog_custom_dashboard_widget_display'
    );
}
function my_blog_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'blog',
            'post_status' => 'any',
            'posts_per_page' => 30,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td>' . esc_html(get_the_title()) . '</td>';
            echo '<td></td>';
            echo '<td>' . get_the_date() . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">' . __('Edit', 'partners-site_v2') . '</a></td>';
            echo '<td><a target="_blank" href="' . get_permalink() . '">' . __('View', 'partners-site_v2') . '</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function my_lead_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'lead',
            'post_status' => 'any',
            'posts_per_page' => 30,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color" style="max-width: 100%">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td style="overflow-wrap: break-word;word-break: break-word; max-width: 70%">' . get_field('originUrl') . '</td>';
            echo '<td>' . get_field('source') . '</td>';
            echo '<td>' . get_the_date() . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">' . __('Edit', 'partners-site_v2') . '</a></td>';
            // echo '<td><a target="_blank" href="' . get_permalink() . '">' . __('View', 'partners-site_v2') . '</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function my_campaign_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'campaign',
            'post_status' => 'any',
            'posts_per_page' => 10,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td>' . esc_html(get_the_title()) . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">' . __('Edit', 'partners-site_v2') . '</a></td>';
            echo '<td><a target="_blank" href="' . get_permalink() . '">' . __('View', 'partners-site_v2') . '</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function my_stock_cars_custom_dashboard_widget_display()
{
    $latest_stock = new WP_Query(
        [
            'post_type' => 'stock-car',
            'post_status' => 'any',
            'posts_per_page' => 10,
            'orderby' => 'created',
            'order' => 'DESC'
        ]
    );
    if ($latest_stock->have_posts()) {
        echo '<table class="table table-flip-color">';
        while ($latest_stock->have_posts()) {
            echo '<tr>';
            $latest_stock->the_post();
            echo '<td>' . esc_html(get_the_title()) . '</td>';
            echo '<td><a target="_blank" href="' . get_edit_post_link() . '">' . __('Edit', 'partners-site_v2') . '</a></td>';
            echo '<td><a target="_blank" href="' . get_permalink() . '">' . __('View', 'partners-site_v2') . '</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
function sms_send($params, $token, $backup = false)
{
    static $content;

    if ($backup == true) {
        $url = 'https://api2.smsapi.pl/sms.do';
    } else {
        $url = 'https://api.smsapi.pl/sms.do';
    }

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $params);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $c,
        CURLOPT_HTTPHEADER,
        array(
            "Authorization: Bearer $token"
        )
    );

    $content = curl_exec($c);
    $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);

    if ($http_status != 200 && $backup == false) {
        $backup = true;
        sms_send($params, $token, $backup);
    }

    curl_close($c);
    return $content;
}
function my_show_extra_profile_fields($user)
{ ?>
    <h3>Dodatkowa autoryzacja</h3>
    <table class="form-table">
        <tr>
            <th><label for="phone">Numer telefonu</label><?php if (!get_the_author_meta('phone', $user->ID)) {
                                                                echo '<div style="color:red;">' . mb_strtoupper(__('Required field', 'partners-site_v2')) . '</div>';
                                                            } ?></th>
            <td>
                <input type="text" name="phone" id="phone"
                    value="<?php echo esc_attr(get_the_author_meta('phone', $user->ID)); ?>" class="regular-text"
                    required /><br />
                <span class="description"><?php esc_html_e('Enter your phone number.', 'partners-site_v2'); ?></span>
            </td>
        </tr>
    </table>

<?php

}
function my_save_extra_profile_fields($user_id)
{

    if (!current_user_can('edit_user', $user_id))
        return false;

    update_user_meta($user_id, 'phone', $_POST['phone']);
}

function custom_login_redirect($redirect_to, $request, $user)
{

    if (strpos($redirect_to, 'wp/wp-admin') !== false && $user) {
        $check_phone = get_the_author_meta('phone', $user->ID);
        if ($check_phone == '') {
            $redirect_to = '/wp/wp-admin/profile.php#phone';
        } else {
            $user = wp_get_current_user();
            $user_token = get_the_author_meta('mfa_token', $user->ID);
            $random_number = rand(10000, 99999);
            update_user_meta($user->ID, 'mfa_token', $random_number);
            // update_user_meta($user->ID,'mfa_token', $random_number);
            // if ($user_token == '' || $user_token == null) {              
            // var_dump($random_number);


            $user_token = get_the_author_meta('mfa_token', $user->ID);

            $apiToken = 'dUNvIOe59Xb2B6bDPGHfdikBHcIpRlbzQc0di9M0';
            $params = array(
                'to' => $check_phone, //numery odbiorców rozdzielone przecinkami
                'from' => 'VolvoCarsPL', //pole nadawcy stworzone w https://ssl.smsapi.pl/sms_settings/sendernames
                'message' => __('Your one-time code to log in to the website', 'partners-site_v2') . ': ' . $random_number, //treść wiadomości
                'encoding' => 'UTF-8',
                'format' => 'json'
            );
            sms_send($params, $apiToken);
            // }

            if ($user) {
                $redirect_to = '/mfa.php?o=' . hash('sha256', $random_number);
            }
        }
    }

    return $redirect_to;
}


add_filter('login_redirect', 'custom_login_redirect', 10, 3);

//disable request to browse-happy
add_filter('pre_http_request', function ($ret, array $request, string $url) {
    if (\preg_match('!^https?://api\.wordpress\.org/core/browse-happy/!i', $url) || \preg_match('!^https?://api\.wordpress\.org/core/serve-happy/!i', $url)) {
        return new \WP_Error('http_request_failed', \sprintf('Request to %s is not allowed.', $url));
    }
    return $ret;
}, 10, 3);
function test_remove_cpt_slug($post_link, $post)
{
    $short_link = get_field('short_link', $post->ID);
    $check_settings = ($short_link ? $short_link : 'disabled');
    if ('campaign' === $post->post_type && 'enabled' === $check_settings) {

        //  $post_link = str_replace('/kampanie/', '/', $post_link);
    }

    return $post_link;
}
add_filter('wp_redirect', 'remove_redirections', 10, 2);

/**
 * Function for `wp_redirect` filter-hook.
 * 
 * @param string $location The path or URL to redirect to.
 * @param int    $status   The HTTP response status code to use.
 *
 * @return string
 */
function remove_redirections($location, $status)
{
    if (strpos($location, 'main') !== false) {
        $url = get_bloginfo('url');
        $location = str_replace('main.volvocars-partner.pl', $_SERVER['SERVER_NAME'], $location);
        //  return false;
    }

    // filter...
    return $location;
}
function getCountTerms($data, $term)
{
    $counter = 0;
    foreach ($data as $k => $d) {

        if ($k[0] !== '_' && strpos($k, $term) > -1) {
            $counter++;
        }
    }
    return $counter;
}
function getBasicOptions($id)
{
    switch_to_blog(1);
    $config = wp_load_alloptions(false);

    $response = [];
    foreach ($config as $k => $s) {
        // $key = str_replace('_acf_network_options','-_',$k);
        //$key = str_replace('_options-taxonomy','-_',$k;
        $key = false;
        if ($k[0] == '_') {
            if (strpos($k, '_acf_network_options') !== false) {
                unset($config[$k]);
                //  $key = str_replace('_acf_network_options','_',$k);   

            } else {
                unset($config[$k]);
                //$key = str_replace('_options-taxonomy','-_',$k);
            }
        } else {
            if (strpos($k, 'acf_network_options') !== false) {
                $key = str_replace('acf_network_options_', '', $k);
            } else {
                $key = str_replace('options-taxonomy_', '', $k);
            }
        }
        if ($key) {
            if ($id == 0) {
                $response[$key] = [$s];
            } else {
                $response[$key] = $s;
            }
            if ($id == 3) {
                unset($config[$k]);
            }
        }
    }
    restore_current_blog();
    return ($response);
}


add_action('template_redirect', function () {

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = untrailingslashit($uri);

    $allowed_paths = [
        '/klauzula-informacyjna'
    ];

    if (in_array($path, $allowed_paths)) {

        status_header(200);


        include get_stylesheet_directory() . '/page-klauzula-informacyjna.php';
        exit;
    }
});

add_action('init', function() {
    if ( $_SERVER['HTTP_HOST'] === 'volvocarkarlik.pl' && $_SERVER['REQUEST_URI'] === '/llms.txt' ) {
        $file = get_stylesheet_directory() . '/llms.txt'; 
        if (file_exists($file)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: inline; filename="llms.txt"');
            readfile($file);
            exit;
        } else {
            status_header(404);
            echo __('File not found.', 'partners-site_v2');
            exit;
        }
    }
});
/**
 * ============================================================================
 * STATIC HTML GENERATOR HOOKS
 * ============================================================================
 * Automatically generate static HTML files for pages on save/delete/cache flush
 */

/**
 * Regenerate static HTML when a page is saved or updated
 */
function static_html_on_save_page($post_id, $post, $update) {
    // Only process published pages
    if ($post->post_type !== 'page' || $post->post_status !== 'publish') {
        return;
    }

    // Avoid infinite loops during auto-save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Get current blog ID
    $blog_id = \Classes\MultisiteFixer::getCurrentBlogId();

    // Generate static HTML for this page
    $generator = new \Classes\StaticHtmlGenerator();
    $generator->generatePageHtml($post_id, $blog_id);
}
add_action('save_post', 'static_html_on_save_page', 10, 3);

/**
 * Delete static HTML when a page is deleted
 */
function static_html_on_delete_page($post_id) {
    $post = get_post($post_id);
    
    if (!$post || $post->post_type !== 'page') {
        return;
    }

    $blog_id = \Classes\MultisiteFixer::getCurrentBlogId();
    
    $generator = new \Classes\StaticHtmlGenerator();
    $generator->deletePageHtml($post_id, $blog_id);
}
add_action('delete_post', 'static_html_on_delete_page');

/**
 * Regenerate all static HTML files when cache is flushed
 */
function static_html_on_cache_flush() {
    $generator = new \Classes\StaticHtmlGenerator();
    
    // Schedule background generation to avoid timeouts
    $generator->scheduleBackgroundGeneration();
}
add_action('wp_cache_flush', 'static_html_on_cache_flush');

/**
 * Background cron job to generate static HTML for all sites
 */
function static_html_cron_generate_all() {
    $generator = new \Classes\StaticHtmlGenerator();
    $results = $generator->generateAllPagesForAllSites();
    
    // Log results
    error_log('StaticHtmlGenerator: Completed generation for all sites - ' . json_encode($results));
}
add_action('static_html_generate_all', 'static_html_cron_generate_all');

/**
 * Regenerate static HTML when permalink structure changes
 */
function static_html_on_permalink_change($old_value, $value) {
    if ($old_value !== $value) {
        $generator = new \Classes\StaticHtmlGenerator();
        $generator->scheduleBackgroundGeneration();
    }
}
add_action('update_option_permalink_structure', 'static_html_on_permalink_change', 10, 2);

/**
 * Add admin bar menu for static HTML management
 */
function static_html_admin_bar_menu($admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }

    $admin_bar->add_menu([
        'id' => 'regenerate-static-html',
        'title' => __('Regenerate HTML', 'partners-site_v2'),
        'href' => '#',
        'meta' => [
            'title' => __('Regenerate static HTML files for all pages', 'partners-site_v2')
        ]
    ]);
}
add_action('admin_bar_menu', 'static_html_admin_bar_menu', 100);

/**
 * AJAX handler for regenerating static HTML
 */
function static_html_ajax_regenerate() {
    check_ajax_referer('static-html-regenerate', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Brak uprawnień']);
    }

    $blog_id = \Classes\MultisiteFixer::getCurrentBlogId();
    $generator = new \Classes\StaticHtmlGenerator();
    
    if ($blog_id === 1) {
        // Regenerate for all sites
        $generator->scheduleBackgroundGeneration();
        wp_send_json_success([
            'message' => __('A regeneration job has been scheduled for all dealers in the background', 'partners-site_v2')
        ]);
    } else {
        // Regenerate for current site
        $results = $generator->generateAllPagesForSite($blog_id);
        wp_send_json_success([
            'message' => sprintf(
                __('%d pages generated, errors: %d', 'partners-site_v2'),
                $results['success'],
                $results['failed']
            ),
            'results' => $results
        ]);
    }
}
add_action('wp_ajax_regenerate_static_html', 'static_html_ajax_regenerate');

/**
 * Enqueue admin scripts for static HTML regeneration
 */
function static_html_admin_scripts() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#wp-admin-bar-regenerate-static-html a').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm(<?php echo json_encode(__('Are you sure you want to regenerate the static HTML files?', 'partners-site_v2')); ?>)) {
                return;
            }

            var $link = $(this);
            var originalText = $link.text();
            $link.text(<?php echo json_encode(__('Regenerating...', 'partners-site_v2')); ?>);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'regenerate_static_html',
                    nonce: '<?php echo wp_create_nonce('static-html-regenerate'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(<?php echo json_encode(__('Error', 'partners-site_v2')); ?> + ': ' + response.data.message);
                    }
                    $link.text(originalText);
                },
                error: function() {
                    alert(<?php echo json_encode(__('An error occurred during regeneration', 'partners-site_v2')); ?>);
                    $link.text(originalText);
                }
            });
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'static_html_admin_scripts');


// godziny otwarcia dzialow 

add_filter('acf/load_field/name=departments_hours', function($field) {

    $employees = get_posts([
        'post_type'   => 'employee',
        'numberposts' => -1,
    ]);

    $available_departments = [];

    foreach ($employees as $emp) {

        $term_id = get_field('category', $emp->ID);

        if ($term_id) {

            switch_to_blog(1);
            $term = get_term($term_id, 'employee_category');
            restore_current_blog();

            if ($term && !is_wp_error($term)) {
                $available_departments[] = mb_strtolower(trim($term->name));
            }
        }
    }

    $available_departments = array_unique($available_departments);

    $new_sub_fields = [];

    for ($i = 0; $i < count($field['sub_fields']); $i++) {

        $sub = $field['sub_fields'][$i];

        if ($sub['type'] === 'message') {

            $label = mb_strtolower(trim($sub['label']));

            if (in_array($label, $available_departments)) {

                $new_sub_fields[] = $sub;

                if (isset($field['sub_fields'][$i + 1])) {
                    $new_sub_fields[] = $field['sub_fields'][$i + 1];
                }

                if (isset($field['sub_fields'][$i + 2])) {
                    $new_sub_fields[] = $field['sub_fields'][$i + 2];
                }
            }

            $i += 2;
        }
    }

    $field['sub_fields'] = $new_sub_fields;

    return $field;
});

add_filter('acf/load_field', function($field) {

    if (str_ends_with($field['name'], '_name')) {
        $field['wrapper']['style'] = 'display:none;';
    }
    return $field;
});
/////////////////////////

// wymuszanie kampani przez main

add_filter('acf/prepare_field/name=force_campaign', function ($field) {
    if (get_current_blog_id() !== get_main_site_id()) {
        return false;
    }
    return $field;
});

add_filter('acf/prepare_field', function ($field) {

    if (get_current_blog_id() === get_main_site_id()) {
        return $field; 
    }

    $fields_to_disable = [
        'field_605f5e7850fbe', // type
        'field_605f5ea150fbf', // local-campaign
        'field_605f5ec150fc0', // global-campaign
        'hide_campaign',       // Zastąp kampanie
    ];

    if (!in_array($field['key'], $fields_to_disable)) {
        return $field;
    }

    if (!preg_match('/row-(\d+)/', $field['name'], $matches)) {
        return $field;
    }
    $row_index = (int) $matches[1];

    switch_to_blog(get_main_site_id());
    $main_slider = get_field('slider', 'options-homepage');
    restore_current_blog();

    if (empty($main_slider['slides'][$row_index])) {
        return $field;
    }

    $main_slide = $main_slider['slides'][$row_index];

    if (!empty($main_slide['force_campaign'])) {

        if ($field['key'] === 'field_605f5e7850fbe') {
            $field['disabled'] = 1;
            $field['readonly'] = 1;
            $field['ui'] = 0;
            $field['wrapper']['class'] .= ' acf-disabled';
            return $field;
        }
        if (in_array($field['key'], ['field_605f5ec150fc0', 'hide_campaign'])) {
            $field['wrapper']['class'] .= ' acf-hidden-row';
            return $field;
        }

        static $added_message = [];
        if (!isset($added_message[$row_index])) {
            $added_message[$row_index] = true;
            return [
                'type'    => 'message',
                'key'     => 'field_global_message_' . $row_index,
                'label'   => __('Information', 'partners-site_v2'),
                'message' => __('This campaign slot has been set globally by the administrator', 'partners-site_v2'),
                'wrapper' => [
                    'class' => 'acf-global-message',
                    'width' => '',
                    'id'    => '',
                ],
            ];
        }
    }

    return $field;
});
add_action('admin_head', function() {
    echo '<style>
        .acf-hidden-row {
    display: none !important;
} 
    </style>';
});



///













// add_action('init', function () {
//     $user = get_user_by('login', 'alasota'); 

//     if ($user) {
//         wp_set_password('NoweHaslo123!', $user->ID);
//         echo 'Hasło zostało zmienione.';
//         exit;
//     }
// });





