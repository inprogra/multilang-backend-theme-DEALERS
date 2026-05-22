<?php
/**
 * Plugin Name: Volvo Global REST API
 * Description: Global REST API endpoints for Volvo frontend apps with multisite support and CORS
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add CORS headers for volvo/v1 namespace
 */
add_action('rest_api_init', function () {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
}, 15);

add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
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
    
    return $served;
}, 10, 4);

/**
 * Register custom REST API endpoints
 */
add_action('rest_api_init', function () {
    // Global page endpoint - returns page data for any path
    register_rest_route('volvo/v1', '/page', array(
        'methods'             => 'GET',
        'callback'            => 'volvo_global_get_page',
        'permission_callback' => '__return_true',
        'args'                => array(
            'path'   => array(
                'required'          => false,
                'default'           => '/',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'domain' => array(
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
});

/**
 * Frontend domain to WordPress domain mapping
 *
 * @var array
 */
$volvo_frontend_domain_map = array(
    'czach.volvotest.pl'  => 'czach-backend.volvotest.pl',
    'karlik.volvotest.pl' => 'karlik-backend.volvotest.pl',
);

/**
 * Resolve domain to blog ID
 *
 * @param string $domain
 * @return int|false
 */
function volvo_global_resolve_blog_id($domain) {
    global $volvo_frontend_domain_map;
    
    $domain = strtolower(trim($domain));
    
    if (empty($domain)) {
        $domain = strtolower($_SERVER['HTTP_HOST'] ?? '');
    }
    
    // Map frontend domain to WordPress domain
    $wp_domain = isset($volvo_frontend_domain_map[$domain]) 
        ? $volvo_frontend_domain_map[$domain] 
        : $domain;
    
    $blogs = get_sites(array('number' => 0));
    
    foreach ($blogs as $blog) {
        $blog_domain = strtolower(is_array($blog) ? $blog['domain'] : $blog->domain);
        if ($blog_domain === $wp_domain) {
            return is_array($blog) ? (int) $blog['blog_id'] : (int) $blog->blog_id;
        }
    }
    
    return false;
}

/**
 * Get WordPress image data for REST response
 *
 * @param int|array $image Image ID or ACF image array
 * @param array $sizes Additional size names to include
 * @return array|null
 */
function volvo_global_prepare_image($image, $sizes = array('full', 'large', 'medium', 'thumbnail')) {
    if (empty($image)) {
        return null;
    }
    
    $attachment_id = is_array($image) ? ($image['ID'] ?? $image['id'] ?? 0) : (int) $image;
    
    if (!$attachment_id) {
        // If it's a URL string
        if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
            return array(
                'url'    => $image,
                'alt'    => '',
                'width'  => null,
                'height' => null,
                'sizes'  => array(),
            );
        }
        return null;
    }
    
    $attachment = get_post($attachment_id);
    $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    
    $result = array(
        'url'    => wp_get_attachment_url($attachment_id),
        'alt'    => $alt,
        'width'  => null,
        'height' => null,
        'sizes'  => array(),
    );
    
    // Get full size dimensions
    $full_size = wp_get_attachment_image_src($attachment_id, 'full');
    if ($full_size) {
        $result['width']  = $full_size[1];
        $result['height'] = $full_size[2];
    }
    
    // Get all requested sizes
    foreach ($sizes as $size) {
        $size_data = wp_get_attachment_image_src($attachment_id, $size);
        if ($size_data) {
            $result['sizes'][$size] = array(
                'url'    => $size_data[0],
                'width'  => $size_data[1],
                'height' => $size_data[2],
            );
        }
    }
    
    return $result;
}

/**
 * Build link array for REST response
 *
 * @param array $link ACF link field
 * @return array|null
 */
function volvo_global_build_link($link) {
    if (empty($link) || !is_array($link)) {
        return null;
    }
    
    $result = array(
        'url'    => $link['url'] ?? '',
        'title'  => $link['title'] ?? '',
        'target' => $link['target'] ?? '_self',
    );
    
    // Check if external
    $home_url = home_url();
    if (!empty($result['url']) && strpos($result['url'], parse_url($home_url, PHP_URL_HOST)) === false) {
        $result['nofollow'] = true;
    }
    
    if (empty($result['url'])) {
        return null;
    }
    
    return $result;
}

/**
 * Format campaign slide data
 *
 * @param WP_Post $slide
 * @return array
 */
function volvo_global_format_slide($slide) {
    if (!$slide || is_wp_error($slide)) {
        return array();
    }
    
    $slide_id = is_object($slide) ? $slide->ID : $slide;
    
    $title = get_field('title', $slide_id);
    $subtitle = get_field('subtitle', $slide_id);
    $link = get_field('link', $slide_id);
    $image_id = get_field('image', $slide_id);
    
    $formatted_link = volvo_global_build_link($link);
    
    if (!$formatted_link || !array_filter($formatted_link)) {
        $formatted_link = array(
            'url'   => get_the_permalink($slide_id),
            'title' => 'Dowiedz się więcej',
        );
    }
    
    if (empty($formatted_link['title'])) {
        $formatted_link['title'] = 'Dowiedz się więcej';
    }
    
    return array(
        'title'     => $title,
        'subtitle'  => $subtitle,
        'link'      => $formatted_link,
        'image'     => volvo_global_prepare_image($image_id),
        'thumbnail' => null,
    );
}

/**
 * Get hero slider data
 *
 * @return array
 */
function volvo_global_get_hero_slider() {
    $slider_options = get_field('slider', 'options-homepage');
    $slides = array();
    $slide_posts = array();
    
    // Collect manually selected slides
    if (!empty($slider_options['slides'])) {
        foreach ($slider_options['slides'] as $item) {
            $slide_post = false;
            
            if ($item['type'] === 'local' && !empty($item['local-campaign'])) {
                $slide_post = get_post($item['local-campaign']);
            } elseif ($item['type'] === 'global' && !empty($item['global-campaign'])) {
                switch_to_blog(1);
                $slide_post = get_post($item['global-campaign']);
                if ($slide_post) {
                    $slide_post->site_ID = 1;
                }
                restore_current_blog();
            }
            
            if ($slide_post && $slide_post->post_status === 'publish') {
                $slide_posts[] = $slide_post;
            }
        }
    }
    
    // Fill with latest campaigns if less than 3
    if (count($slide_posts) < 3) {
        $existing_ids = wp_list_pluck($slide_posts, 'ID');
        
        $latest_query = new WP_Query(array(
            'post_type'      => 'campaign',
            'post_status'    => 'publish',
            'posts_per_page' => 3 - count($slide_posts),
            'post__not_in'   => $existing_ids,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        
        foreach ($latest_query->posts as $post) {
            $post->site_ID = get_current_blog_id();
            $slide_posts[] = $post;
        }
    }
    
    // Format slides
    foreach ($slide_posts as $slide) {
        if (!empty($slide->site_ID) && $slide->site_ID !== get_current_blog_id()) {
            switch_to_blog($slide->site_ID);
            $formatted_slide = volvo_global_format_slide($slide);
            restore_current_blog();
        } else {
            $formatted_slide = volvo_global_format_slide($slide);
        }
        
        if (!empty($formatted_slide)) {
            $slides[] = $formatted_slide;
        }
    }
    
    return array('slides' => $slides);
}

/**
 * Get special offer data
 *
 * @return array
 */
function volvo_global_get_special_offer() {
    $show_offer = get_field('offerBtn', 'options-homepage');
    $title = get_field('offerTitle', 'options-homepage');
    $link = get_field('offerLinks', 'options-homepage');
    
    $normalized_link = volvo_global_build_link($link);
    
    // Validate
    if (empty($title) || empty($normalized_link['url']) || empty($normalized_link['title'])) {
        $show_offer = false;
    }
    
    if (!$show_offer) {
        return array(
            'showOffer' => false,
            'title'     => '',
            'link'      => null,
        );
    }
    
    return array(
        'showOffer' => (bool) $show_offer,
        'title'     => $title,
        'link'      => $normalized_link,
    );
}

/**
 * Get grey boxes data
 *
 * @return array
 */
function volvo_global_get_grey_boxes() {
    $use_global = get_field('global_content_checkbox', 'options-homepage');
    
    if ($use_global) {
        switch_to_blog(1);
    }
    
    $boxes = array(
        get_field('greyBox1', 'options-homepage'),
        get_field('greyBox2', 'options-homepage'),
        get_field('greyBox3', 'options-homepage'),
        get_field('greyBox4', 'options-homepage'),
    );
    
    $items = array();
    $field_map = array(
        0 => array('heading' => 'heading', 'description' => 'description', 'link' => 'link'),
        1 => array('heading' => 'heading2', 'description' => 'description2', 'link' => 'link2'),
        2 => array('heading' => 'heading3', 'description' => 'description3', 'link' => 'link3'),
        3 => array('heading' => 'heading4', 'description' => 'description4', 'link' => 'link4'),
    );
    
    foreach ($boxes as $index => $box) {
        if (!empty($box)) {
            $map = $field_map[$index];
            $items[] = array(
                'title'       => $box[$map['heading']] ?? '',
                'description' => nl2br(esc_html($box[$map['description']] ?? '')),
                'link'        => volvo_global_build_link($box[$map['link']] ?? null),
            );
        }
    }
    
    if ($use_global) {
        restore_current_blog();
    }
    
    return array('items' => $items);
}

/**
 * Get offer cards data
 *
 * @return array
 */
function volvo_global_get_offer_cards() {
    $offer1 = get_field('offer1', 'options-homepage');
    $offer2 = get_field('offer2', 'options-homepage');
    
    // Fallback to global site if empty
    if (empty($offer1['imageCard']) && empty($offer2['imageCard2'])) {
        switch_to_blog(1);
        $offer1 = get_field('offer1', 'options-homepage');
        $offer2 = get_field('offer2', 'options-homepage');
        restore_current_blog();
    }
    
    $items = array();
    
    if (!empty($offer1['imageCard'])) {
        $items[] = array(
            'image'       => volvo_global_prepare_image($offer1['imageCard']),
            'option'      => $offer1['select_option'] ?? null,
            'title'       => $offer1['heading'] ?? '',
            'description' => nl2br($offer1['descriptionCard'] ?? ''),
            'link'        => volvo_global_build_link($offer1['link'] ?? null),
        );
    }
    
    if (!empty($offer2['imageCard2'])) {
        $items[] = array(
            'image'       => volvo_global_prepare_image($offer2['imageCard2']),
            'option'      => $offer2['select_option2'] ?? null,
            'title'       => $offer2['heading2'] ?? '',
            'description' => nl2br($offer2['descriptionCard2'] ?? ''),
            'link'        => volvo_global_build_link($offer2['link2'] ?? null),
        );
    }
    
    return array('items' => $items);
}

/**
 * Get offer box data (3 small boxes)
 *
 * @return array
 */
function volvo_global_get_offer_box() {
    $box1 = get_field('offerBox1', 'options-homepage');
    $box2 = get_field('offerBox2', 'options-homepage');
    $box3 = get_field('offerBox3', 'options-homepage');
    $main_heading = get_field('mainHeading', 'options-homepage');
    
    // Fallback to global
    if (empty($box1['imageBox']) && empty($box2['imageBox2']) && empty($box3['imageBox3'])) {
        switch_to_blog(1);
        $box1 = get_field('offerBox1', 'options-homepage');
        $box2 = get_field('offerBox2', 'options-homepage');
        $box3 = get_field('offerBox3', 'options-homepage');
        restore_current_blog();
    }
    
    $items = array();
    
    if (!empty($box1['imageBox'])) {
        $items[] = array(
            'image'       => volvo_global_prepare_image($box1['imageBox']),
            'title'       => $box1['heading'] ?? '',
            'description' => nl2br(esc_html($box1['description'] ?? '')),
            'link'        => volvo_global_build_link($box1['link'] ?? null),
        );
    }
    
    if (!empty($box2['imageBox2'])) {
        $items[] = array(
            'image'       => volvo_global_prepare_image($box2['imageBox2']),
            'title'       => $box2['heading2'] ?? '',
            'description' => nl2br(esc_html($box2['description2'] ?? '')),
            'link'        => volvo_global_build_link($box2['link2'] ?? null),
        );
    }
    
    if (!empty($box3['imageBox3'])) {
        $items[] = array(
            'image'       => volvo_global_prepare_image($box3['imageBox3']),
            'title'       => $box3['heading3'] ?? '',
            'description' => nl2br(esc_html($box3['description3'] ?? '')),
            'link'        => volvo_global_build_link($box3['link3'] ?? null),
        );
    }
    
    return array(
        'mainHeading' => $main_heading,
        'items'       => $items,
    );
}

/**
 * Get slider family data
 *
 * @return array
 */
function volvo_global_get_slider_family() {
    $slider_family_box = get_field('sliderFamilyBox', 'options-homepage');
    $slider_title = get_field('sliderTitle', 'options-homepage');
    
    // Fallback to global site if empty
    if (empty($slider_family_box)) {
        switch_to_blog(1);
        $slider_family_box = get_field('sliderFamilyBox', 'options-homepage');
        $slider_title = get_field('sliderTitle', 'options-homepage');
        restore_current_blog();
    }
    
    $items = array();
    
    if (!empty($slider_family_box)) {
        foreach ($slider_family_box as $box) {
            $items[] = array(
                'option' => $box['choiceFamily'] ?? null,
                'image'  => volvo_global_prepare_image($box['imageSlider'] ?? null),
                'model'  => $box['nameCar'] ?? '',
                'price'  => $box['priceCar'] ?? '',
                'link'   => volvo_global_build_link($box['linksSlide'] ?? null),
            );
        }
    }
    
    return array(
        'title' => $slider_title,
        'items' => $items,
    );
}

/**
 * Get offer card (quotation cars) data
 *
 * @return array
 */
function volvo_global_get_offer_card() {
    $offer_card = get_field('OfferCard', 'options-homepage');
    
    // Fallback to global
    if (empty($offer_card['image'])) {
        switch_to_blog(1);
        $offer_card = get_field('OfferCard', 'options-homepage');
        restore_current_blog();
    }
    
    if (empty($offer_card)) {
        return array();
    }
    
    return array(
        'image'       => volvo_global_prepare_image($offer_card['image'] ?? null),
        'title'       => $offer_card['headingOffer'] ?? '',
        'description' => nl2br(esc_html($offer_card['description'] ?? '')),
        'link'        => volvo_global_build_link($offer_card['link'] ?? null),
    );
}

/**
 * Get offers (sales section) data
 *
 * @return array
 */
function volvo_global_get_offers() {
    $offers_options = get_field('offers', 'options-homepage');
    
    // Fallback to global site if empty
    if (empty($offers_options['heading']) && empty($offers_options['offer-boxes'])) {
        switch_to_blog(1);
        $offers_options = get_field('offers', 'options-homepage');
        restore_current_blog();
    }
    
    $offer_boxes_options = $offers_options['offer-boxes'] ?? array();
    $offer_boxes = array('items' => array());
    
    if (!empty($offer_boxes_options['items'])) {
        foreach ($offer_boxes_options['items'] as $box) {
            $has_button = !empty($box['link']);
            $offer_boxes['items'][] = array(
                'icon'        => $box['icon'] ?? '',
                'heading'     => $box['heading'] ?? '',
                'description' => $box['description'] ?? '',
                'hasButton'   => $has_button,
                'link'        => $has_button ? volvo_global_build_link($box['link']) : null,
            );
        }
    }
    
    $preview_options = $offers_options['preview-component'] ?? array();
    $content = array();
    
    if (!empty($preview_options['description'])) {
        $content[] = array(
            'acf_fc_layout' => 'description',
            'description'   => $preview_options['description'],
        );
    }
    
    if (!empty($preview_options['link'])) {
        $content[] = array(
            'acf_fc_layout' => 'link',
            'link'          => volvo_global_build_link($preview_options['link']),
        );
    }
    
    $preview_image = null;
    if (!empty($preview_options['image'])) {
        $preview_image = volvo_global_prepare_image($preview_options['image']);
    }
    
    return array(
        'heading'            => $offers_options['heading'] ?? '',
        'showPreviewComponent' => $offers_options['enable-preview-component'] ?? false,
        'previewComponent'   => array(
            'reverse' => true,
            'image'   => $preview_image,
            'heading' => $preview_options['heading'] ?? null,
            'content' => $content,
        ),
        'offerBoxes'         => $offer_boxes,
    );
}

/**
 * Get dealer info (auth protected)
 *
 * @param bool $authenticated
 * @return array
 */
function volvo_global_get_dealer_info($authenticated = false) {
    $dealer_info = array(
        'name'    => get_field('name', 'options-dealer') ?: '',
        'logo'    => volvo_global_prepare_image(get_field('logo', 'options-dealer') ?: null),
        'address' => '',
    );
    
    $indicata = get_field('indicata_setup_settings', 'options-dealer');
    if (!empty($indicata['indicata_address'])) {
        $dealer_info['address'] = $indicata['indicata_address'];
    }
    
    // Social media - only include if authenticated
    if ($authenticated) {
        $social = get_field('social-media', 'options-dealer');
        $dealer_info['social_media'] = $social ?: array();
        
        // YouLead config
        $youlead = get_field('youlead', 'options-dealer');
        $dealer_info['youlead'] = $youlead ?: array();
    }
    
    return $dealer_info;
}

/**
 * Check if request is authenticated
 *
 * @return bool
 */
function volvo_global_is_authenticated() {
    $token = isset($_SERVER['HTTP_X_WP_TOKEN']) ? sanitize_text_field($_SERVER['HTTP_X_WP_TOKEN']) : '';
    
    if (empty($token)) {
        return false;
    }
    
    // Simple token validation - check against a stored token or nonce
    // For now, accept any non-empty token as "authenticated" 
    // In production, implement proper JWT or API key validation
    return true;
}

/**
 * Main page endpoint callback
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function volvo_global_get_page($request) {
    $path = $request->get_param('path') ?: '/';
    
    // Get domain from header or query param
    $domain = isset($_SERVER['HTTP_X_WP_DOMAIN']) ? sanitize_text_field($_SERVER['HTTP_X_WP_DOMAIN']) : '';
    if (empty($domain)) {
        $domain = $request->get_param('domain');
    }
    if (empty($domain)) {
        $domain = $_SERVER['HTTP_HOST'] ?? '';
    }
    
    $blog_id = volvo_global_resolve_blog_id($domain);
    
    // Fallback to main blog if domain not found
    if (!$blog_id) {
        $blog_id = 1;
    }
    
    // Switch to target blog
    switch_to_blog($blog_id);
    
    // Check authentication
    $authenticated = volvo_global_is_authenticated();
    
    // Build response based on path
    $response = array(
        'path'   => $path,
        'domain' => $domain,
        'site'   => array(
            'name' => get_bloginfo('name'),
            'url'  => get_bloginfo('url'),
        ),
    );
    
    // For homepage (path = /)
    if ($path === '/' || $path === '') {
        $response['heroSlider']    = volvo_global_get_hero_slider();
        $response['specialOffer']  = volvo_global_get_special_offer();
        $response['greyBox']       = volvo_global_get_grey_boxes();
        $response['offerCards']    = volvo_global_get_offer_cards();
        $response['offerBox']      = volvo_global_get_offer_box();
        $response['sliderFamily']  = volvo_global_get_slider_family();
        $response['offers']        = volvo_global_get_offers();
        $response['offerCard']     = volvo_global_get_offer_card();
    } else {
        // For other pages, get page by path
        $page = get_page_by_path(ltrim($path, '/'));
        
        if ($page) {
            $response['page'] = array(
                'id'             => $page->ID,
                'title'          => get_the_title($page->ID),
                'content'        => apply_filters('the_content', $page->post_content),
                'featured_image' => volvo_global_prepare_image(get_post_thumbnail_id($page->ID)),
                'acf'            => function_exists('get_fields') ? get_fields($page->ID) : array(),
            );
        } else {
            $response['page'] = null;
        }
    }
    
    // Add dealer info (partially auth-protected)
    $response['dealer'] = volvo_global_get_dealer_info($authenticated);
    
    // Add menus
    $response['menus'] = volvo_global_get_menus();
    
    restore_current_blog();
    
    return new WP_REST_Response($response, 200);
}

/**
 * Get menus data
 *
 * @return array
 */
function volvo_global_get_menus() {
    $menus = array();
    $locations = get_nav_menu_locations();
    
    if (isset($locations['header'])) {
        $menu_items = wp_get_nav_menu_items($locations['header']);
        if ($menu_items) {
            foreach ($menu_items as $key => $value) {
                if ($value->post_title == 'Elektromobilność') {
                    unset($menu_items[$key]);
                }
            }
            $menus['header'] = array_values(array_map('volvo_global_format_menu_item', $menu_items));
        }
    }
    
    if (isset($locations['side-nav'])) {
        $menu_items = wp_get_nav_menu_items($locations['side-nav']);
        if ($menu_items) {
            $menus['side_nav'] = array_map('volvo_global_format_menu_item', $menu_items);
        }
    }
    
    if (isset($locations['footer'])) {
        $menu_items = wp_get_nav_menu_items($locations['footer']);
        if ($menu_items) {
            $menus['footer'] = array_map('volvo_global_format_menu_item', $menu_items);
        }
    }
    
    return $menus;
}

/**
 * Format menu item
 *
 * @param object $item
 * @return array
 */
function volvo_global_format_menu_item($item) {
    return array(
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
