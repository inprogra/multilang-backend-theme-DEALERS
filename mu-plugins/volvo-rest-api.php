<?php
/**
 * Plugin Name: Volvo REST API Endpoints
 * Description: Custom REST API endpoints for Volvo dealer information
 * Version: 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add CORS headers for volvo/v1 namespace
 */
add_filter('rest_pre_serve_request', function ($value, $result, $request, $server) {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    // Allow all *.volvotest.pl origins
    if ($origin && preg_match('/\.volvotest\.pl$/', parse_url($origin, PHP_URL_HOST))) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Domain, X-WP-Token, X-Requested-With');
    }
    
    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit;
    }
    
    return $value;
}, 10, 4);

/**
 * Register custom REST API endpoints
 */
add_action('rest_api_init', function () {
    // Register dealer information endpoint
    register_rest_route('volvo/v1', '/dealer-info', array(
        'methods'             => 'GET',
        'callback'            => 'volvo_get_dealer_info',
        'permission_callback' => '__return_true',
    ));

    // Register menus endpoint
    register_rest_route('volvo/v1', '/menus', array(
        'methods'             => 'GET',
        'callback'            => 'volvo_get_menus',
        'permission_callback' => '__return_true',
    ));

    // Register combined endpoint
    register_rest_route('volvo/v1', '/site-info', array(
        'methods'             => 'GET',
        'callback'            => 'volvo_get_site_info',
        'permission_callback' => '__return_true',
    ));

    // Register getDealer endpoint
    register_rest_route('volvo/v1', '/getDealer', array(
        'methods'             => 'GET',
        'callback'            => 'volvo_get_dealer',
        'permission_callback' => '__return_true',
        'args' => array(
            'domain' => array(
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
});

/**
 * Get dealer information from ACF options
 *
 * @return WP_REST_Response
 */
function volvo_get_dealer_info() {
    $social = get_field( 'social-media', 'options-dealer' );
    foreach($social as $key=>$value) {
        $svg = '';
        switch($key) {
            case 'facebook':
                $svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8192 24H1.32462C0.592836 24 0 23.4068 0 22.6753V1.32461C0 0.592925 0.59293 0 1.32462 0H22.6755C23.407 0 24 0.592925 24 1.32461V22.6753C24 23.4069 23.4069 24 22.6755 24H16.5597V14.7059H19.6793L20.1464 11.0838H16.5597V8.77132C16.5597 7.72264 16.8509 7.00801 18.3546 7.00801L20.2727 7.00717V3.76755C19.9409 3.7234 18.8024 3.62479 17.4778 3.62479C14.7124 3.62479 12.8192 5.31276 12.8192 8.41261V11.0838H9.69156V14.7059H12.8192V24Z" fill="currentColor"></path>
            </svg>';
            break;
            case 'instagram':
                $svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 0C8.741 0 8.33234 0.0138139 7.05242 0.0722133C5.77516 0.13047 4.90283 0.333343 4.13954 0.630008C3.35044 0.936629 2.68123 1.34695 2.01406 2.01406C1.34695 2.68123 0.936629 3.35044 0.630008 4.13954C0.333343 4.90283 0.13047 5.77516 0.0722133 7.05242C0.0138139 8.33234 0 8.741 0 12C0 15.259 0.0138139 15.6677 0.0722133 16.9476C0.13047 18.2248 0.333343 19.0972 0.630008 19.8605C0.936629 20.6496 1.34695 21.3188 2.01406 21.9859C2.68123 22.6531 3.35044 23.0634 4.13954 23.37C4.90283 23.6667 5.77516 23.8695 7.05242 23.9278C8.33234 23.9862 8.741 24 12 24C15.259 24 15.6677 23.9862 16.9476 23.9278C18.2248 23.8695 19.0972 23.6667 19.8605 23.37C20.6496 23.0634 21.3188 22.6531 21.9859 21.9859C22.6531 21.3188 23.0634 20.6496 23.37 19.8605C23.6667 19.0972 23.8695 18.2248 23.9278 16.9476C23.9862 15.6677 24 15.259 24 12C24 8.741 23.9862 8.33234 23.9278 7.05242C23.8695 5.77516 23.6667 4.90283 23.37 4.13954C23.0634 3.35044 22.6531 2.68123 21.9859 2.01406C21.3188 1.34695 20.6496 0.936629 19.8605 0.630008C19.0972 0.333343 18.2248 0.13047 16.9476 0.0722133C15.6677 0.0138139 15.259 0 12 0ZM12 2.16211C15.2041 2.16211 15.5836 2.17435 16.849 2.23208C18.019 2.28543 18.6544 2.48092 19.0772 2.64526C19.6373 2.86295 20.0371 3.12298 20.457 3.54293C20.8769 3.96282 21.137 4.36257 21.3546 4.92269C21.519 5.34554 21.7145 5.98093 21.7678 7.15092C21.8256 8.41627 21.8378 8.79582 21.8378 12C21.8378 15.2041 21.8256 15.5836 21.7678 16.849C21.7145 18.019 21.519 18.6544 21.3546 19.0772C21.137 19.6373 20.8769 20.0371 20.457 20.457C20.0371 20.8769 19.6373 21.137 19.0772 21.3546C18.6544 21.519 18.019 21.7145 16.849 21.7678C15.5838 21.8256 15.2043 21.8378 12 21.8378C8.79558 21.8378 8.41613 21.8256 7.15092 21.7678C5.98093 21.7145 5.34554 21.519 4.92269 21.3546C4.36257 21.137 3.96282 20.8769 3.54293 20.457C3.12303 20.0371 2.86295 19.6373 2.64526 19.0772C2.48092 18.6544 2.28543 18.019 2.23208 16.849C2.17435 15.5836 2.16211 15.2041 2.16211 12C2.16211 8.79582 2.17435 8.41627 2.23208 7.15092C2.28543 5.98093 2.48092 5.34554 2.64526 4.92269C2.86295 4.36257 3.12298 3.96282 3.54293 3.54293C3.96282 3.12298 4.36257 2.86295 4.92269 2.64526C5.34554 2.48092 5.98093 2.28543 7.15092 2.23208C8.41627 2.17435 8.79582 2.16211 12 2.16211ZM5.8377 11.9999C5.8377 8.59657 8.59657 5.8377 11.9999 5.8377C15.4031 5.8377 18.162 8.59657 18.162 11.9999C18.162 15.4031 15.4031 18.162 11.9999 18.162C8.59657 18.162 5.8377 15.4031 5.8377 11.9999ZM11.9998 15.9998C9.79066 15.9998 7.9998 14.209 7.9998 11.9998C7.9998 9.79066 9.79066 7.9998 11.9998 7.9998C14.209 7.9998 15.9998 9.79066 15.9998 11.9998C15.9998 14.209 14.209 15.9998 11.9998 15.9998ZM18.4058 7.0343C19.2011 7.0343 19.8458 6.38962 19.8458 5.59432C19.8458 4.79902 19.2011 4.1543 18.4058 4.1543C17.6105 4.1543 16.9658 4.79902 16.9658 5.59432C16.9658 6.38962 17.6105 7.0343 18.4058 7.0343Z" fill="currentColor"></path>
            </svg>';
            break;
            case 'linkedin':
                $svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.4844 0H3.51562C1.57709 0 0 1.57709 0 3.51562V20.4844C0 22.4229 1.57709 24 3.51562 24H20.4844C22.4229 24 24 22.4229 24 20.4844V3.51562C24 1.57709 22.4229 0 20.4844 0ZM22.5938 20.4844C22.5938 21.6475 21.6475 22.5938 20.4844 22.5938H3.51562C2.35254 22.5938 1.40625 21.6475 1.40625 20.4844V3.51562C1.40625 2.35254 2.35254 1.40625 3.51562 1.40625H20.4844C21.6475 1.40625 22.5938 2.35254 22.5938 3.51562V20.4844Z" fill="currentColor"></path>
                <path d="M4.26562 19.7812H8.48438V9.84375H4.26562V19.7812ZM5.67188 11.25H7.07812V18.375H5.67188V11.25Z" fill="currentColor"></path>
                <path d="M15.5197 9.84375C15.5184 9.84375 15.5169 9.84375 15.5156 9.84375C15.0295 9.84375 14.556 9.92505 14.1094 10.0829V9.84375H9.89062V19.7812H14.1094V14.7656C14.1094 14.378 14.4249 14.0625 14.8125 14.0625C15.2001 14.0625 15.5156 14.378 15.5156 14.7656V19.7812H19.7344V14.2822C19.7344 12.0066 17.8883 9.84595 15.5197 9.84375ZM18.3281 18.375H16.9219V14.7656C16.9219 13.6025 15.9756 12.6562 14.8125 12.6562C13.6494 12.6562 12.7033 13.6025 12.7031 14.7654V18.375H11.2969V11.25H12.7031V12.6572L13.8285 11.812C14.3179 11.4443 14.9013 11.25 15.5156 11.25H15.5184C17.0151 11.2515 18.3281 12.6683 18.3281 14.2822V18.375Z" fill="currentColor"></path>
                <path d="M4.26562 8.4375H8.48438V4.21875H4.26562V8.4375ZM5.67188 5.625H7.07812V7.03125H5.67188V5.625Z" fill="currentColor"></path>
            </svg>';
            break;
            case 'youtube':
                $svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.522 15.635L9.52125 8.84387L16.0057 12.2511L9.522 15.635ZM23.76 7.66708C23.76 7.66708 23.5252 6.0016 22.806 5.26818C21.8932 4.30515 20.8703 4.30062 20.4015 4.24472C17.043 4 12.0052 4 12.0052 4H11.9948C11.9948 4 6.957 4 3.5985 4.24472C3.129 4.30062 2.10675 4.30515 1.19325 5.26818C0.474 6.0016 0.24 7.66708 0.24 7.66708C0.24 7.66708 0 9.62336 0 11.5789V13.4128C0 15.3691 0.24 17.3246 0.24 17.3246C0.24 17.3246 0.474 18.9901 1.19325 19.7235C2.10675 20.6865 3.306 20.6563 3.84 20.7568C5.76 20.9426 12 21 12 21C12 21 17.043 20.9924 20.4015 20.7477C20.8703 20.6911 21.8932 20.6865 22.806 19.7235C23.5252 18.9901 23.76 17.3246 23.76 17.3246C23.76 17.3246 24 15.3691 24 13.4128V11.5789C24 9.62336 23.76 7.66708 23.76 7.66708Z" fill="currentColor"></path>
            </svg>';
            break;
        }
        $social[] = ['name' => $key, 'url' => $social[$key],'svg' => $svg];
        unset($social[$key]);
        if ($social[$key] == '') {
            unset($social[$key]);
        }
    }
    $dealer_info = array(
        'dealer_name'    => get_field('name', 'options-dealer') ?: '',
        'dealer_logo'    => get_field('logo', 'options-dealer') ?: '',
        'dealer_address' => get_field('indicata_setup_settings', 'options-dealer')['indicata_address'] ?? '',
        'social_media' => $social,
    );

    // If logo is an attachment ID, get the URL
    if (is_numeric($dealer_info['dealer_logo'])) {
        $dealer_info['dealer_logo'] = wp_get_attachment_url($dealer_info['dealer_logo']);
    }

    return new WP_REST_Response($dealer_info, 200);
}

/**
 * Get menu information
 *
 * @return WP_REST_Response
 */
function volvo_get_menus() {
    $menus = array();

    // Get all registered menu locations
    $locations = get_nav_menu_locations();
    
    // Get header menu (main menu)
    if (isset($locations['header'])) {
        $menu_items = wp_get_nav_menu_items($locations['header']);
        foreach($menu_items as $key=>$value) {
            if ($value->post_title == 'Elektromobilność') {
                unset($menu_items[$key]);
            }
        }
        array_values($menu_items);
        $menus['header_menu'] = volvo_format_menu_items($menu_items);
    } else {
        $menus['header_menu'] = array();
    }

    // Get side navigation menu
    if (isset($locations['side-nav'])) {
        $menu_items = wp_get_nav_menu_items($locations['side-nav']);
        $menus['side_nav_menu'] = volvo_format_menu_items($menu_items);
    } else {
        $menus['side_nav_menu'] = array();
    }

    // Get footer menu
    if (isset($locations['footer'])) {
        $menu_items = wp_get_nav_menu_items($locations['footer']);
        $menus['footer_menu'] = volvo_format_menu_items($menu_items);
    } else {
        $menus['footer_menu'] = array();
    }

    return new WP_REST_Response($menus, 200);
}

/**
 * Format menu items for API response
 *
 * @param array $menu_items
 * @return array
 */
function volvo_format_menu_items($menu_items) {
    if (!$menu_items) {
        return array();
    }

    $formatted_items = array();
    
    foreach ($menu_items as $item) {
        $formatted_items[] = array(
            'id'          => $item->ID,
            'title'       => $item->title,
            'url'         => $item->url,
            'target'      => $item->target,
            'parent_id'   => $item->menu_item_parent,
            'order'       => $item->menu_order,
            'classes'     => implode(' ', $item->classes),
            'description' => $item->description,
        );
    }

    return $formatted_items;
}

/**
 * Get addresses from FooterController
 *
 * @return array
 */
function volvo_get_addresses() {
    // Check if FooterController class exists
    if (!class_exists('Controllers\FooterController')) {
        return array();
    }

    // Create instance of FooterController
    $footerController = new \Controllers\FooterController();
    
    // Use reflection to access the private getAddresses method
    $reflection = new ReflectionClass($footerController);
    $method = $reflection->getMethod('getAddresses');
    $method->setAccessible(true);
    
    // Call the method and return addresses
    $addresses = $method->invoke($footerController);
    
    return $addresses ?: array();
}

/**
 * Get combined site information
 *
 * @return WP_REST_Response
 */
function volvo_get_site_info() {
    $dealer_info = volvo_get_dealer_info()->get_data();
    $menus = volvo_get_menus()->get_data();
    $addresses = volvo_get_addresses();

    $site_info = array_merge(
        $dealer_info,
        $menus,
        array(
            'site_name' => get_bloginfo('name'),
            'site_url'  => get_bloginfo('url'),
            'addresses' => $addresses,
        )
        
    );

    return new WP_REST_Response($site_info, 200);
}

function volvo_get_dealer($request) {
    $requested_domain = $request->get_param('domain');
    if (empty($requested_domain)) {
        $requested_domain = $_SERVER['HTTP_HOST'];
    }
    $requested_domain = strtolower($requested_domain);

    $blogs = wp_get_sites();
    $exclude_blogs = [3, 38];
    $mSalon = ['PL041', 'PL050'];

    foreach ($blogs as $blog) {
        if (in_array($blog['blog_id'], $exclude_blogs)) {
            continue;
        }

        $blog_domain = strtolower($blog['domain']);

        if ($blog_domain !== $requested_domain) {
            continue;
        }

        switch_to_blog($blog['blog_id']);

        $options = get_fields('options-dealer');
        $dealerId = $options['dealerId'] ?? '';
        $dealerName = $options['name'] ?? '';

        if (strpos($dealerName, 'Test') !== false || strpos($dealerName, 'Euroservice Volvo Warszawa') !== false) {
            restore_current_blog();
            return new WP_REST_Response(
                array('error' => 'Dealer not found or excluded'),
                404
            );
        }

        $showroom_posts = get_posts(array(
            'post_type' => 'showroom',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        $showrooms = array();
        foreach ($showroom_posts as $showroom) {
            $showrooms[] = volvo_format_showroom($showroom);
        }

        $dealerIdClean = str_replace('#1', '', $dealerId);
        $is_multisalon = false;

        if (in_array($dealerIdClean, $mSalon)) {
            $is_multisalon = true;
        }
        if (count($showroom_posts) > 3) {
            $is_multisalon = true;
        }

        $booking_settings = $options['booking_engine_settings'] ?? array();
        $booking_partner_code = $booking_settings['parmaPartnerCode'] ?? '';
        $booking_partner_id = $booking_settings['partner_id'] ?? '';
        $booking_api_key = $booking_settings['ttmsapikey'] ?? '';

        $data = array(
            'domain' => $blog['domain'],
            'blog_id' => $blog['blog_id'],
            'dealer_id' => $dealerId,
            'dealer_name' => $dealerName,
            'is_multisalon' => $is_multisalon,
            'booking_partner_code' => $booking_partner_code,
            'booking_partner_id' => $booking_partner_id,
            'bboking_partner_api_key' => $booking_api_key,
            'showrooms' => $showrooms,
        );

        restore_current_blog();

        return new WP_REST_Response($data, 200);
    }

    return new WP_REST_Response(
        array('error' => 'Dealer not found for domain: ' . $requested_domain),
        404
    );
}

function volvo_format_showroom($showroom) {
    $address = get_field('address', $showroom->ID);
    $map_position = get_field('map-position', $showroom->ID);
    $showroom_hours = get_field('showroom-open-hours', $showroom->ID);
    $service_hours = get_field('service-open-hours', $showroom->ID);

    return array(
        'id' => get_field('showroomId', $showroom->ID),
        'name' => get_field('name', $showroom->ID),
        'has_showroom' => (bool) get_field('has-showroom', $showroom->ID),
        'has_service' => (bool) get_field('has-service', $showroom->ID),
        'has_customer_service_office' => (bool) get_field('has-customer-service-office', $showroom->ID),
        'showroom_location' => get_field('showroom-location', $showroom->ID),
        'address' => array(
            'street' => $address['street'] ?? '',
            'zip_code' => $address['zip-code'] ?? '',
            'city' => $address['city'] ?? '',
            'phone' => $address['phone'] ?? '',
        ),
        'map_position' => array(
            'lat' => $map_position['lat'] ?? null,
            'lng' => $map_position['lng'] ?? null,
            'address' => $map_position['address'] ?? '',
        ),
        'showroom_open_hours' => array(
            'monday_friday' => volvo_format_hours($showroom_hours['monday-friday'] ?? array()),
            'saturday' => volvo_format_hours($showroom_hours['saturday'] ?? array()),
            'additional_info' => $showroom_hours['additional-info'] ?? '',
        ),
        'service_open_hours' => array(
            'monday_friday' => volvo_format_hours($service_hours['monday-friday'] ?? array()),
            'saturday' => volvo_format_hours($service_hours['saturday'] ?? array()),
            'additional_info' => $service_hours['additional-info'] ?? '',
        ),
    );
}

function volvo_format_hours($hours) {
    return array(
        'from' => $hours['from'] ?? '',
        'to' => $hours['to'] ?? '',
    );
}

/**
 * Switch to blog based on domain
 *
 * @param string|null $domain Domain to switch to (optional)
 * @return int|null Blog ID that was switched to, or null if not found
 */
function volvo_switch_to_blog_by_domain($domain = null) {
    if (empty($domain)) {
        $domain = $_SERVER['HTTP_HOST'] ?? '';
    }
    $domain = strtolower($domain);

    $blogs = wp_get_sites();
    $exclude_blogs = [3, 38];

    foreach ($blogs as $blog) {
        if (in_array($blog['blog_id'], $exclude_blogs)) {
            continue;
        }

        $blog_domain = strtolower($blog['domain']);

        if ($blog_domain !== $domain) {
            continue;
        }

        switch_to_blog($blog['blog_id']);
        return $blog['blog_id'];
    }

    return null;
}

/**
 * Get employees for contact page (grouped by category)
 * Falls back to direct ACF field grouping when taxonomy doesn't exist
 *
 * @param WP_REST_Request|null $request REST request object (optional, for backwards compatibility)
 */
function volvo_get_employees($request = null) {
    $domain = null;
    if ($request !== null && method_exists($request, 'get_param')) {
        $domain = $request->get_param('domain');
    }

    $blog_id = volvo_switch_to_blog_by_domain($domain);
    if ($blog_id === null) {
        $blog_id = get_current_blog_id();
        switch_to_blog($blog_id);
    }

    // First try to get employee categories from taxonomy
    $employee_categories = get_terms([
        'taxonomy' => 'employee_category',
        'hide_empty' => false,
    ]);

    $showrooms = get_posts([
        'post_type' => 'showroom',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    $showroom_name = '';
    if (!empty($showrooms)) {
        $showroom_name = get_field('name', $showrooms[0]->ID) ?: '';
    }

    // If no categories from taxonomy, query all employees and group by ACF category field
    if (empty($employee_categories)) {
        // Get category names from global wp_terms table (employee_category terms)
        // These are stored in the main global tables (NOT site-specific)
        // IMPORTANT: Use base prefix 'wp_' since terms are global, not per-blog
        global $wpdb;
        $category_names = [];
        
        // After switch_to_blog(), $wpdb->prefix changes to site-specific (e.g., wp_25_)
        // But wp_terms and wp_term_taxonomy are GLOBAL tables (no blog prefix)
        // So we need to use the base prefix explicitly
        $base_prefix = $wpdb->base_prefix ?: 'wp_';
        $terms_result = $wpdb->get_results(
            "SELECT t.term_id, t.name FROM {$base_prefix}terms t 
             JOIN {$base_prefix}term_taxonomy tt ON t.term_id = tt.term_id 
             WHERE tt.taxonomy = 'employee_category'"
        );
        foreach ($terms_result as $term) {
            $category_names[$term->term_id] = $term->name;
        }

        $employees_query = new \WP_Query([
            'post_type'      => 'employee',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        $categories_data = [];

        if ($employees_query->have_posts()) {
            foreach ($employees_query->posts as $employee) {
                $category_id = get_post_meta($employee->ID, 'category', true) ?: 'uncategorized';
                $name = get_post_meta($employee->ID, 'name', true) ?: '';
                $surname = get_post_meta($employee->ID, 'surname', true) ?: '';

                $employee_data = [
                    'name' => $name . ' ' . $surname,
                    'position' => get_post_meta($employee->ID, 'position', true) ?: '',
                    'phone' => get_post_meta($employee->ID, 'phone', true) ?: '',
                    'email' => get_post_meta($employee->ID, 'email', true) ?: '',
                ];

                $category_label = isset($category_names[$category_id]) ? $category_names[$category_id] : 'Category ' . $category_id;

                if (!isset($categories_data[$category_id])) {
                    $categories_data[$category_id] = [
                        'name' => $category_label,
                        'name_id' => sanitize_title($category_label),
                        'hours' => '',
                        'employees' => [],
                    ];
                }
                $categories_data[$category_id]['employees'][] = $employee_data;
            }
        }

        restore_current_blog();

        return new \WP_REST_Response([
            'showroom_name' => $showroom_name,
            'categories' => array_values($categories_data),
        ], 200);
    }

    // Original logic when categories exist in taxonomy
    $categories_data = [];

    foreach ($employee_categories as $category) {
        $query_args = [
            'post_type' => 'employee',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'category',
                    'value' => $category->term_id,
                    'compare' => '='
                ],
            ],
        ];

        $showroom_employees = new \WP_Query($query_args);

        if ($showroom_employees->have_posts()) {
            $week_from = get_field('week_from', 'employee_category_' . $category->term_id) ?: '';
            $week_to = get_field('week_to', 'employee_category_' . $category->term_id) ?: '';
            $saturday_from = get_field('saturday_from', 'employee_category_' . $category->term_id) ?: '';
            $saturday_to = get_field('saturday_to', 'employee_category_' . $category->term_id) ?: '';

            $hours_html = '<p>Poniedziałek - piątek: <strong>' . $week_from . '-' . $week_to . '</strong></p>';
            if (!empty($saturday_from)) {
                $hours_html .= '<p>Sobota: <strong>' . $saturday_from . '-' . $saturday_to . '</strong></p>';
            }

            $employees = [];
            foreach ($showroom_employees->posts as $employee) {
                $employees[] = [
                    'name' => get_field('name', $employee->ID) . ' ' . get_field('surname', $employee->ID),
                    'position' => get_field('position', $employee->ID),
                    'phone' => get_field('phone', $employee->ID),
                    'email' => get_field('email', $employee->ID),
                ];
            }

            $categories_data[] = [
                'name' => $category->name,
                'name_id' => sanitize_title($category->name),
                'hours' => $hours_html,
                'employees' => $employees,
            ];
        }
    }

    restore_current_blog();

    return new \WP_REST_Response([
        'showroom_name' => $showroom_name,
        'categories' => $categories_data,
    ], 200);
}

/**
 * Get contact info for contact page
 *
 * @param WP_REST_Request|null $request REST request object (optional, for backwards compatibility)
 */
function volvo_get_contact_info($request = null) {
    $domain = null;
    if ($request !== null && method_exists($request, 'get_param')) {
        $domain = $request->get_param('domain');
    }

    $blog_id = volvo_switch_to_blog_by_domain($domain);
    if ($blog_id === null) {
        $blog_id = get_current_blog_id();
        switch_to_blog($blog_id);
    }

    $dealer_name = get_field('name', 'options-dealer') ?: '';
    $showrooms = get_posts([
        'post_type' => 'showroom',
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ]);

    $address_html = '';
    $phone = '';
    $salon_hours = '';
    $service_hours = '';

    if (!empty($showrooms)) {
        $showroom = $showrooms[0];
        $address = get_field('address', $showroom->ID);

        if (!empty($address)) {
            $address_parts = [];
            if (!empty($address['street'])) $address_parts[] = $address['street'];
            if (!empty($address['city'])) $address_parts[] = $address['city'];
            if (!empty($address['zip-code'])) $address_parts[] = $address['zip-code'];
            $address_html = '<p>' . implode(' / ', $address_parts) . '</p>';
            $phone = $address['phone'] ?? '';
        }

        $has_showroom = get_field('has-showroom', $showroom->ID);
        $has_service = get_field('has-service', $showroom->ID);

        if ($has_showroom) {
            $showroom_open_hours = get_field('showroom-open-hours', $showroom->ID);
            if ($showroom_open_hours) {
                $salon_hours = '<p>Poniedziałek - Piątek <strong>' . $showroom_open_hours['monday-friday']['from'] . '-' . $showroom_open_hours['monday-friday']['to'] . '</strong></p>';
                if (!empty($showroom_open_hours['saturday']['from'])) {
                    $salon_hours .= '<p>Sobota <strong>' . $showroom_open_hours['saturday']['from'] . '-' . $showroom_open_hours['saturday']['to'] . '</strong></p>';
                }
            }
        }

        if ($has_service) {
            $service_open_hours = get_field('service-open-hours', $showroom->ID);
            if ($service_open_hours) {
                $service_hours = '<p>Poniedziałek - Piątek <strong>' . $service_open_hours['monday-friday']['from'] . '-' . $service_open_hours['monday-friday']['to'] . '</strong></p>';
            }
        }
    }

    restore_current_blog();

    return new \WP_REST_Response([
        'dealer_name' => $dealer_name,
        'address' => $address_html,
        'phone' => $phone,
        'salon_hours' => $salon_hours,
        'service_hours' => $service_hours,
    ], 200);
}

add_filter('rest_pre_serve_request', function ($value, $result, $request, $server_context) {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    if ($origin && preg_match('/\.volvotest\.pl$/', parse_url($origin, PHP_URL_HOST))) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Domain, X-WP-Token, X-Requested-With');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit;
    }

    return $value;
}, 10, 4);

add_action('rest_api_init', function () {
    // Register employees endpoint
    register_rest_route('volvo/v1', '/employees', array(
        'methods'             => 'GET',
        'callback'            => 'volvo_get_employees',
        'permission_callback' => '__return_true',
        'args' => array(
            'domain' => array(
                'required'          => false,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
        ),
    ));

    // Register contact-info endpoint
    register_rest_route('volvo/v1', '/contact-info', array(
        'methods'             => 'GET',
        'callback'            => 'volvo_get_contact_info',
        'permission_callback' => '__return_true',
        'args' => array(
            'domain' => array(
                'required'          => false,
                'validate_callback' => function ($param) {
                    return is_string($param);
                },
            ),
        ),
    ));
});
