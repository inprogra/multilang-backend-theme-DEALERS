<?php
/**
 * Plugin Name: Volvo Global REST API
 * Description: Global REST API endpoints for Volvo frontend apps with multisite support and CORS
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once WPMU_PLUGIN_DIR . '/volvo-global-api/classes/FeaturedCars.class.php';
require_once WPMU_PLUGIN_DIR . '/volvo-global-api/classes/Electrification.class.php';

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

function volvo_global_get_count_terms(array $data, string $term): int
{
    $counter = 0;
    foreach ($data as $k => $d) {

        if ($k[0] !== '_' && strpos($k, $term) > -1) {
            $counter++;
        }
    }
    return $counter;
}

function volvo_global_get_basic_options(string $id): array
{
    switch_to_blog(1);
    $config = wp_load_alloptions(false);

    $response = [];
    foreach ($config as $k => $s) {
        $key = false;
        if ($k[0] == '_') {
            if (strpos($k, '_acf_network_options') !== false) {
                unset($config[$k]);

            } else {
                unset($config[$k]);
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

function volvo_global_prepare_images_render(array $data) {
		
    //$cache = new \VGA\Classes\Cache();
    foreach($data as $key => $value) {
        //$cache_check = $cache->getDatabaseKey( $value['blog_id'].'-'.$value['img_id'].'-'.$value['width'].'-'.$value['height'].'-'.$value['crop']);
         $cache_check = false;
        if (!$cache_check) {
            $data[$key]['src'] = 'https://image-render.cloud/api/renderImage?image='.$value['image'].'&size='.$value['width'].'&height='.($value['height'] ? $value['height'] : false).'&fit='.($value['crop'] ? $value['crop'] : false).'&flip='.($value['crop'] ? 1 : false).'&blog_id='.$value['blog_id'].'&img_id='.$value['img_id'];
        } else {
            $data[$key]['src'] = $cache_check;
        }
        
        $data[$key]['thumb'] = 'https://image-render.cloud/api/renderImage?image='.$value['image'].'&size='.$value['twidth'].'&height='.($value['theight'] ? $value['theight'] : false).'&fit='.($value['tcrop'] ? $value['tcrop'] : false).'&flip='.($value['tcrop'] ? 1 : false).'&blog_id='.$value['blog_id'].'&img_id='.$value['img_id'];
    }

    return $data;
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

    $parse_url = wp_parse_url(ltrim($path, '/'));
    $path_parts = explode('/', trim($parse_url['path'], '/'));
    
    // For homepage (path = /)
    if ($path_parts[0] === '') {
        $response['heroSlider']    = volvo_global_get_hero_slider();
        $response['specialOffer']  = volvo_global_get_special_offer();
        $response['greyBox']       = volvo_global_get_grey_boxes();
        $response['offerCards']    = volvo_global_get_offer_cards();
        $response['offerBox']      = volvo_global_get_offer_box();
        $response['sliderFamily']  = volvo_global_get_slider_family();
        $response['offers']        = volvo_global_get_offers();
        $response['offerCard']     = volvo_global_get_offer_card();

    // For contact pages, get page by path
    } elseif ($path_parts[0] == 'kontakt') {

        $is_volvo_ms_global_page = false;
        $page = get_page_by_path($path_parts[0]);
        if (!$page) {
            $page = volvo_global_get_global_page_by_path($path_parts[0]);
            $is_volvo_ms_global_page = true;
        }

        $showrooms = volvo_global_get_showrooms_blog();
        
        $response['siteHeading']   = array(
            'heading'     => __( 'Contact', 'partners-site_v2' ),
            'description' => __( 'Bądźmy blisko siebie', 'partners-site_v2' ),
        );
        $response['showrooms']          = volvo_global_get_showrooms_data($showrooms);
        $response['showroomFilters']    = volvo_global_get_showrooms_filters($showrooms);
        $response['employeesShowrooms'] = volvo_global_get_showrooms_employees($showrooms);
        $response['partnerName']        = get_field( 'name', 'options-dealer' );
        $response['source']             = volvo_global_get_contact_source($page, $is_volvo_ms_global_page);
        $response['formShowrooms']      = volvo_global_get_showrooms_form($showrooms);
        $response['thankyouImage']      = volvo_global_get_contact_thank_you_image();
        $response['thankYouCode']       = volvo_global_get_contact_thank_you();
        
    // For models pages, get page by path
    } elseif ($path_parts[0] === 'modele') {
        if (count($path_parts) == 3) {

            $category_model = volvo_global_get_model_category_term($path_parts[1]);
            
            if ($category_model) {
                $page = volvo_global_get_model_page($path_parts[2], $category_model);

                if ($page) {
                    global $post;
                    $post = $page;
                    setup_postdata( $post );

                    $response['model'] = volvo_global_get_model_data($page, $blog_id);

                    wp_reset_postdata();
                } else {
                    $response['page_404'] = true;
                }
            } else {
                $response['page_404'] = true;
            }

        } else { // index
            global $post;
            $post = $page;
            setup_postdata( $post );

            $response['data'] = volvo_global_get_models($blog_id);

            wp_reset_postdata();
        }

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

    // Addresses

    // Add social media
    $response['social_media'] = volvo_global_get_footer_social_madia();
    
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

/**
 * Social media
 *
 * @return array
 */
function volvo_global_get_footer_social_madia() {

    $socialMedia = get_field( 'social-media', 'options-dealer' );
    
    return $socialMedia ? array_filter( $socialMedia ) : array();
}

/**
 * Contact
 * 
 * @return string|null
 */
function volvo_global_get_contact_thank_you(): string|null
{
    $thankYouCode  = ( get_field( 'field_thankyou_code', 'options-dealer' ) ? get_field( 'field_thankyou_code', 'options-dealer' ) : null );
    $thankYouCode  = str_replace( '||time||', time(), $thankYouCode );

    return $thankYouCode;
}

/**
 * Image on contact thanku you page
 * 
 * @return array|null
 */
function volvo_global_get_contact_thank_you_image(): array|null
{
    switch_to_blog( 1 );

    $globalFormOptions = get_field( 'form', 'options-global' );
    $image             = volvo_global_prepare_image( $globalFormOptions['thank-you-image'] );

    restore_current_blog();

    return $image;
}

/**
 * Global page
 * 
 * @param string $path
 * @return object|null
 */
function volvo_global_get_global_page_by_path(string $path): object|null
{
    switch_to_blog(1);

    $page = get_page_by_path(ltrim($path));

    restore_current_blog();

    return $page;
}

/**
 * Contact source
 * 
 * @param object $page
 * @param bool $is_ms_page_global
 * @return string
 */
function volvo_global_get_contact_source(object $page, $is_ms_page_global = false): string
{
    
    if ($is_ms_page_global) {
        switch_to_blog(1);
    }

    $source = get_field('source', $page->ID);

    if (!$source || $source == '') {
        $source = $page->post_name;
    }
    if (!$source) {
        $source = '';
    }

    if ($is_ms_page_global) {
        restore_current_blog();
    }

    return $source;
}

/**
 * SHOWROOMS
 * 
 * @return array
 * 
 */
function volvo_global_get_showrooms_blog(): array
{

    $showroomsQuery = new \WP_Query(
        array(
            'post_type'      => 'showroom',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        )
    );
    
    $showroomsAndSerices = [];
    $showrooms = [];
    $services = [];

    foreach ( $showroomsQuery->posts as $showroom ) {
        $isShowroom = get_field( 'has-showroom', $showroom->ID );
        $isService  = get_field( 'has-service', $showroom->ID );

        $showroomsAndSerices[] = $showroom->ID;

        if ( $isShowroom ) {
            $showrooms[] = $showroom->ID;
        }

        if ( $isService ) {
            $services[] = $showroom->ID;
        }
    }

    return [
        'showroomsAndSerices' => $showroomsAndSerices,
        'showrooms' => $showrooms,
        'services' => $services
    ];
}

/**
 * Muilti showroom and service
 * 
 * @param array $showroomsBlog
 * @return bool
 */
function volvo_global_is_multi_showroom_and_service(array $showroomsBlog): bool
{
    return count(array_unique($showroomsBlog['showroomsAndSerices'])) > 1;
}

/**
 * Showroom form
 * 
 * @param array $showroomsBlog
 * @return array
 */
function volvo_global_get_showrooms_form(array $showroomsBlog): array
{
    $formShowrooms = false;
    if ( volvo_global_is_multi_showroom_and_service($showroomsBlog) ) {
        $formShowrooms = array();
        $showroomsIds  = array_unique($showroomsBlog['showroomsAndSerices']);
        foreach ( $showroomsIds as $id ) {
            $formShowrooms[ $id ] = get_field( 'name', $id );
        }
    }

    return $formShowrooms;
}

/**
 * Showrooms data
 * 
 * @param array $showroomsBlog
 * @return array
 */
function volvo_global_get_showrooms_data(array $showroomsBlog): array
{
    $showrooms               = array();
    $showroomsAndServicesIds = array_unique($showroomsBlog['showroomsAndSerices']);

    $googleMapsKey = get_field( 'google-maps-key', 'options-dealer' );

    if ( array_filter( $showroomsAndServicesIds ) ) {
        foreach ( $showroomsAndServicesIds as $showroom ) {
            $address = get_field( 'address', $showroom );

            $newItem = array(
                'name'    => get_post_field( 'post_name', $showroom ),
                'title'   => get_field( 'name', 'options-dealer' ) . ' ' . get_field( 'name', $showroom ),
                'street'  => $address['street'],
                'city'    => $address['city'],
                'zipcode' => $address['zip-code'],
                'phone'   => $address['phone'],
                'map'     => $googleMapsKey ? get_field( 'map-position', $showroom ) : false,
                //'mapPin'  => \VGA\Classes\Cache::getAsset( 'pin.png' ),
            );

            $hasShowroom              = get_field( 'has-showroom', $showroom );
            $hasService               = get_field( 'has-service', $showroom );
            $hasCustomerServiceOffice = get_field( 'has-customer-service-office', $showroom );

            if ( $hasShowroom ) {
                $showroomOpenHours = get_field( 'showroom-open-hours', $showroom );
                $newItem['showroomOpeningHours'] = array(
                    'week'           => array(
                        'from' => $showroomOpenHours['monday-friday']['from'],
                        'to'   => $showroomOpenHours['monday-friday']['to'],
                    ),
                    'saturday'       => array(
                        'from' => $showroomOpenHours['saturday']['from'],
                        'to'   => $showroomOpenHours['saturday']['to'],
                    ),
                    'additionalInfo' => $showroomOpenHours['additional-info'],
                );
            }

            if ( $hasService ) {
                $serviceOpenHours = get_field( 'service-open-hours', $showroom );
                $newItem['serviceOpeningHours'] = array(
                    'week'           => array(
                        'from' => $serviceOpenHours['monday-friday']['from'],
                        'to'   => $serviceOpenHours['monday-friday']['to'],
                    ),
                    'saturday'       => array(
                        'from' => $serviceOpenHours['saturday']['from'],
                        'to'   => $serviceOpenHours['saturday']['to'],
                    ),
                    'additionalInfo' => $serviceOpenHours['additional-info'],
                );
            }

            if ( $hasCustomerServiceOffice ) {
                $customerServiceOfficeOpenHours = get_field( 'customer-service-office-open-hours', $showroom );
                $newItem['customerServiceOfficeOpeningHours'] = array(
                    'week'           => array(
                        'from' => $customerServiceOfficeOpenHours['monday-friday']['from'],
                        'to'   => $customerServiceOfficeOpenHours['monday-friday']['to'],
                    ),
                    'saturday'       => array(
                        'from' => $customerServiceOfficeOpenHours['saturday']['from'],
                        'to'   => $customerServiceOfficeOpenHours['saturday']['to'],
                    ),
                    'additionalInfo' => $customerServiceOfficeOpenHours['additional-info'],
                );
            }

            $showrooms[] = $newItem;
        }
    }

    return $showrooms;
}

/**
 * Showroom employees filters
 * 
 * @param array $showroomsBlog
 * @return array
 */
function volvo_global_get_showrooms_filters(array $showroomsBlog): array
{
    $filters = array();

    if ( !volvo_global_is_multi_showroom_and_service($showroomsBlog) ) {
        return $filters;
    }

    $showrooms = array_unique($showroomsBlog['showroomsAndSerices']);

    if ( array_filter( $showrooms ) ) {
        foreach ( $showrooms as $showroom ) {
            $showroomEmployees = new \WP_Query(
                array(
                    'post_type'      => 'employee',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array(
                            'key'     => 'showroom',
                            'value'   => $showroom,
                            'compare' => '=',
                        ),
                    ),
                )
            );

            if ( $showroomEmployees->have_posts() ) {
                $filters[ get_post_field( 'post_name', $showroom ) ] = get_field( 'name', $showroom );
            }
        }
    }

    return $filters;
}

/**
 * All showrooms employees
 * 
 * @param array $showroomsBlog
 * @return array
 */
function volvo_global_get_showrooms_employees(array $showroomsBlog): array
{

    switch_to_blog( 1 );
    $employeeCategories = get_terms([
        'taxonomy'   => 'employee_category',
        'hide_empty' => false,
    ]);
    restore_current_blog();

    $showroomsEmployees = [];

    $showrooms = array_unique($showroomsBlog['showroomsAndSerices']);

    if ( array_filter( $showrooms ) ) {
        foreach ( $showrooms as $showroom ) {
            $slug = get_post_field( 'post_name', $showroom );
            $showroomsEmployees[ $slug ] = [
                'name'       => get_field( 'name', $showroom ),
                'categories' => [],
            ];

            foreach ( $employeeCategories as $category ) {
                $queryArgs = [
                    'post_type'      => 'employee',
                    'posts_per_page' => -1,
                    'meta_query'     => [
                        [
                            'key' => 'category',
                            'value' => $category->term_id,
                            'compare' => '='
                        ],
                    ],
                ];
            
                if ( volvo_global_is_multi_showroom_and_service($showroomsBlog) ) {
                    $queryArgs['meta_query'][] = [
                        'key'     => 'showroom',
                        'value'   => $showroom,
                        'compare' => '=',
                    ];
                }
            
                $showroomEmployees = new \WP_Query( $queryArgs );
            
                if ( $showroomEmployees->have_posts() ) {
            
                    $departmentsHours = get_field('departments_hours', $showroom);

                    $departmentHoursForCategory = [
                        'week'     => ['from'=>'','to'=>''],
                        'saturday' => ['from'=>'','to'=>'']
                    ];

                    if ($departmentsHours) {
                
                        $categorySlug = strtolower(str_replace('-', '_', sanitize_title($category->name)));
                        foreach ($departmentsHours as $key => $value) {
                            if (str_ends_with($key, '_name')) {
                                $depName = strtolower($value);

                                if ($depName === $categorySlug) {
                                    $departmentHoursForCategory = $departmentsHours['department_hours_' . $depName];
                                    break;
                                }
                            }
                        }
                    }
                    // --- KONIEC DODATKU ---
            
                    $hours = [
                        'week' => [
                            'from' => get_field('week_from', 'employee_category_' . $category->term_id) ?: '',
                            'to'   => get_field('week_to', 'employee_category_' . $category->term_id) ?: '',
                        ],
                        'saturday' => [
                            'from' => get_field('saturday_from', 'employee_category_' . $category->term_id) ?: '',
                            'to'   => get_field('saturday_to', 'employee_category_' . $category->term_id) ?: '',
                        ],
                    ];
            
                    $currentCategory = [
                        'name'             => $category->name,
                        'employees'        => [],
                        'hours'            => $hours,               
                        'department_hours' => $departmentHoursForCategory, 
                    ];
            
                    foreach ( $showroomEmployees->posts as $employee ) {
                        $employeeId = $employee->ID;
                        $currentCategory['employees'][] = [
                            'name'     => get_field('name', $employeeId) . ' ' . get_field('surname', $employeeId),
                            'position' => get_field('position', $employeeId),
                            'phone'    => get_field('phone', $employeeId),
                            'email'    => get_field('email', $employeeId),
                        ];
                    }
            
                    $showroomsEmployees[ $slug ]['categories'][] = $currentCategory;
                }
            }
        }
    }

    return $showroomsEmployees; 
}

/**
 * Model term category
 * 
 * @param string $category_slug
 * @return object
 */
function volvo_global_get_model_category_term(string $category_slug): object
{
    switch_to_blog( 1 );

    $category = get_term_by('slug', $category_slug, 'model_category');

    restore_current_blog();

    return $category;
}

/**
 * Model page
 * 
 * @param string $page_name
 * @param object $category
 * @return object|null
 */
function volvo_global_get_model_page(string $page_name, object $category): object|null
{
    switch_to_blog( 1 );

    $posts = get_posts([
        'name'           => $page_name,
        'post_type'      => 'model',
        'posts_per_page' => 1,
        'tax_query'      => [
            [
                'taxonomy' => 'model_category',
                'field'    => 'term_id',
                'terms'    => $category->term_id,
            ]
        ]
    ]);

    restore_current_blog();
    
    return $posts[0] ?? null;
}

/**
 * Models page
 * 
 * @param int $blog_id
 * @return array|null
 */
function volvo_global_get_models(int $blog_id): array|null
{
    //$cache = new \VGA\Classes\Cache();
    //$cache->get('modele-rest'.$blog_id);
    $data = null;

    if (!$data) {
        $items = array();
		switch_to_blog( 1 );

		$categories = get_terms(
			array(
				'taxonomy' => 'model_category',
			)
		);

		$typesField = get_field_object( 'field_604a1c9f94d09' );
		$types      = $typesField['choices'];

		$categoriesActiveMobile = get_field( 'show-mobile', 'options-models' );
		$categoryActiveDesktop  = get_field( 'show-desktop', 'options-models' );
		
		foreach ( $categories as $category ) {
			$item = array(
				'heading'         => $category->name,
				'subheading'      => get_field( 'description', 'model_category_' . $category->term_id ),
				'isMobileActive'  => in_array( $category->term_id, $categoriesActiveMobile, true ),
				'isDesktopActive' => $category->term_id === $categoryActiveDesktop,
				'types'           => array(),
			);

			$models = new \WP_Query(
				array(
					'post_type'      => 'model',
					'post_parent'    => 0,
					'cache'			 => true,
					'posts_per_page' => -1,
					'tax_query'      => array(
						array(
							'taxonomy' => 'model_category',
							'terms'    => $category->term_id,
						),
					),
				)
			);

			foreach ( $types as $key => $type ) {
				$cars = array();
				
				foreach ( $models->posts as $model ) {
					$imagesDesktop = [];
					$imagesMobile = [];
					$modelId   = $model->ID;
					$modelType = get_field( 'type', $modelId );

					if ( $modelType === $key ) {
						$itemId     = get_field( 'thumbnail', $modelId );
						$img_id = $itemId;
						//$blog_id = get_current_blog_id();
						$itemId = wp_get_attachment_url($itemId);
						$images = [
							[
								'blog_id' => $blog_id,
								'img_id' => $img_id,
								'height' => 180,
								'width' => 320,
								'crop' =>  false,
								'image' => $itemId,
								'query' => 1680
							],
							[
								'blog_id' => $blog_id,
								'img_id' => $img_id,
								'height' => 'false',
								'width' => 300,
								'crop' => 'false',
								'image' => $itemId,
								'query' => 1000
							],
							[
								'blog_id' => $blog_id,
								'img_id' => $img_id,
								'height' => 174,
								'width' => 406,
								'crop' => 'crop',
								'image' => $itemId,
								'query' => 100
							]
						];
						$imagesDesktop = volvo_global_prepare_images_render($images);
						$imagesMobile = [];

						$cars[] = array(
							'name'         => get_field( 'name', $modelId ),
							'short_name'   => get_field( 'short_name_list' , $modelId),
							'hide_price'   => volvo_global_get_model_price_status($modelId),
							'price'        => volvo_global_get_model_price($modelId ,true),
							'imageMobile'  => $imagesMobile,
							'imageDesktop' => $imagesDesktop,
							'url'          => volvo_global_build_link(['url' => get_the_permalink( $modelId )])['url'],
						);
					}
				}
				
				if ( array_filter( $cars ) ) {
					$item['carTypes'][] = array(
						'name' => $type,
						'cars' => $cars,
					);
				}
				// var_dump($item);
			}
			
			$items[] = $item;
		}
		
		$popup         = get_field( 'popup', 'options-models' );
		$popup['link'] = volvo_global_build_link( $popup['link'] );
		$data = [
			'carCategories' => $items,
			'hasPopup'      => array_filter( $popup ),
			'popup'         => $popup,
		];

		restore_current_blog();

        //$cache->set('modele-'.$blog_id,$data,3600);
    }

    return $data;
}

/**
 * Model price status
 * 
 * @param int $modelId
 * @return bool
 */
function volvo_global_get_model_price_status(int $modelId): bool
{
		$hide_price = false;

		$variations = new \WP_Query(
			array(
				'post_type'      => 'model',
				'posts_per_page' => 99,
				'post_parent'    => $modelId,
				'cache_results'  => true,
				'meta_key'       => 'price',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			)
		);

		if ( array_filter( $variations->posts ) ) {
			if ( array_filter( $variations->posts ) ) {
			foreach($variations->posts as $p) {
				$variation = $p;
				$variation = $variations->posts[0];

				$price_status = get_field( 'hide_price', $variation->ID );

				if ( $price_status ) {
					$hide_price = $price_status;
					break;
				}
			}
			
		}
	}

    return $hide_price;
}

/**
 * Model price
 * 
 * @param int $modelId
 * @param bool $custom_price
 * @return string|null
 */
function volvo_global_get_model_price(int $modelId, bool $custom_price = false): string|null
{
    $price = null;

    $variations = new \WP_Query(
        array(
            'post_type'      => 'model',
            'posts_per_page' => 1,
            'post_parent'    => $modelId,
            'cache_results'  => true,
            'meta_key'       => 'price',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        )
    );

    if ($custom_price) {
        $price = get_field('menu_price', $modelId);
        return $price;
    }
    
    if ( array_filter( $variations->posts ) ) {
        $variation = $variations->posts[0];

        $variationPrice = get_field( 'price', $variation->ID );

        if ( $variationPrice ) {
            $price = $variationPrice;
        }
    }

    return $price;
}

/**
 * Model data
 * 
 * @param object $post
 * @param int $blog_id
 * @return array|null
 */
function volvo_global_get_model_data($post, $blog_id): array|null
{
    switch_to_blog( 1 );

    $modelShortName = get_field( 'short-name', $post->ID );
    $model = null;
    if (!$model) {

		$model = array(
			'heading'   => get_field( 'name', $post->ID ),
			'shortName' => $modelShortName,
			'versions'  => array(),
			'content2'   => $post->post_content ? volvo_global_block_prepare_to_view(volvo_global_blocks_prepare_all( $post->post_content, $blog_id ), $blog_id) : '', // parse blocks
			'slug_url' 	=> parse_url(get_the_permalink($post->ID))['path']
		);
		
		$versions = new \WP_Query(
			array(
				'post_type'      => 'model',
				'posts_per_page' => -1,
				'cache'			 => false,
				'post_parent'    => $post->ID,
			)
		);
		$featuredCarsOptions = get_field('featured-cars', 'options-global');
		$Parsedown = new \Parsedown();
		
		foreach ( $versions->posts as $version ) {
			$colors = [];
			$gallery = get_field( 'gallery', $version->ID );
			$gallery_ids = $gallery;
			
			$colors = get_field('field_version_colors_content',$version->ID);
			
			if ($colors['version_interrior_color_tags'] && is_array($colors)) {
                foreach($colors['version_interrior_color_tags'] as $key=>$c) {
                    $term_icon = get_field('model_category_img',$c->taxonomy.'_'.$c->term_id);

                    $remove_fields = ['id', 'icon', 'author', 'status', 'uploaded_to', 'date', 'modified', 'menu_order', 'mime_type', 'type', 'subtype'];
                    foreach($remove_fields as $remove_field) {
                        unset($term_icon[$remove_field]);
                    }
                    $colors['version_interrior_color_tags'][$key]->icon = $term_icon;
                    
                    $remove_fields = ['term_group', 'term_taxonomy_id', 'parent', 'count', 'filter', 'term_order'];
                    foreach($remove_fields as $remove_field) {
                        unset($colors['version_interrior_color_tags'][$key]->{$remove_field});
                    }
                }
			}
			
            if ($colors['version_default_gallery'] && is_array($colors['version_default_gallery'])) {
                $remove_fields = ['term_group', 'term_taxonomy_id', 'taxonomy', 'parent', 'count', 'filter', 'term_order'];
                foreach($colors['version_default_gallery'] as $key=>$c) {
                    foreach($remove_fields as $remove_field) {
                        unset($colors['version_default_gallery'][$key]->{$remove_field});
                    }
                }
            }

			$temp_arr = [];
			$default_gallery = $colors['version_default_gallery'];
			if (!empty($default_gallery)) {
				
				foreach ($default_gallery as $v) {				
					array_push($temp_arr,$v->slug);
				}
				
			}

			if ($colors['cards']) {
                foreach($colors['cards'] as $key=>$s) {
                    if ($colors['cards'][$key]['version_color_tags'][0]) {
                        $icon = get_field('model_category_img',$colors['cards'][$key]['version_color_tags'][0]->taxonomy . '_' . $colors['cards'][$key]['version_color_tags'][0]->term_id);
                        
                        $remove_fields = ['id', 'icon', 'author', 'status', 'uploaded_to', 'date', 'modified', 'menu_order', 'mime_type', 'type', 'subtype'];
                        foreach($remove_fields as $remove_field) {
                            unset($icon[$remove_field]);
                        }

                        $colors['cards'][$key]['version_color_tags'][0]->icon = $icon;


                        $remove_fields = ['term_group', 'term_taxonomy_id', 'taxonomy', 'parent', 'count', 'filter', 'term_order'];
                        foreach($remove_fields as $remove_field) {
                            unset($colors['cards'][$key]['version_color_tags'][0]->{$remove_field});
                        }
                    }

                    $compare_default_gallery = [$s['version_color_tags'][0]->slug,$s['gallery_type']];
                    if (array_diff($temp_arr,$compare_default_gallery) == null && $s['gallery_type'] !== 'auto') {
                        $gallery = $s['version_gallery'];
                    }
                }
            }
				
			$featuredImage = $gallery[0];
			
            //gallery
            list($galleryPictures, $galleryThumbs) = volvo_global_get_model_data_gallery($gallery_ids, $blog_id);
			
			$technicalDataRaw = get_field( 'technical-data', $version->ID );
			$technicalData    = array();

			foreach ( $technicalDataRaw as $key => $value ) {
				$str                   = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $key ) ) );
				$str[0]                = strtolower( $str[0] );
				$technicalData[ $str ] = $value;
			}

			$twoColumnContentComponent        = get_field( 'two-column-content-component', $version->ID );
			$twoColumnContentComponentImageId = $twoColumnContentComponent['image'];
			$video = $twoColumnContentComponent['field_version_video'];
			
            $twoColumnImageSizes = null;
			if ($twoColumnContentComponentImageId) {
				$twoColumnImage = wp_get_attachment_image_url($twoColumnContentComponentImageId);
				$twoColumnImageSizes = ['1200,700,1800,450,900,1350,721,1442,2163,959'];
			}
			
			// version override
            $version_override = volvo_global_get_version_override($version->ID, $featuredCarsOptions, $blog_id);
            $model['featuredCars'] = $version_override['featuredCars'];

			$Parsedown = new \Parsedown();
			$contentParsedown = $twoColumnContentComponent['description'];
			$contentParsedown = $Parsedown->text($contentParsedown);
			$contentParsedown = str_replace('<ul','<ul class="content__list list"',$contentParsedown);
			$contentParsedown = str_replace('<li','<li class="list__item"',$contentParsedown);
			
            $prepare_blocks_level = 0;
			$model['versions'][] = array(
				'name'                      => get_field( 'name', $version->ID ),
				'price'                     => get_field( 'price', $version->ID ),
				'hide_price'				=> get_field('hide_price', $version->ID),
				'description'               => $Parsedown->text(get_field( 'description', $version->ID )),
				'desc_more'					=> $contentParsedown,
				'title'						=> $twoColumnContentComponent['heading'],
				'technicalData'             => $technicalData,
				'erange'                    => $technicalData['erange'],
				'fullTechnicalDataLink'     => volvo_global_build_link( get_field( 'full-technical-data-link', $version->ID ) ),
				'featuredImage'             => $featuredImage,
				'gallery'                   => $galleryPictures,
				'gallery_thumbs'		    => $galleryThumbs,
				'colors'					=> $colors,
				'twoColumnContentComponent' => array(
					'heading'       => $twoColumnContentComponent['heading'],
					'content'       => array(
						array(
							'acf_fc_layout' => 'description',
							'description'   => '',
						),
					),
					'link'          => volvo_global_build_link( $twoColumnContentComponent['link'] ),
					'testDriveLink' => get_home_url($blog_id) . '/jazda-testowa?s_model=' . $modelShortName,
					'video'         => $twoColumnContentComponent['video'],
					'custom_video'  => (is_array($video) ? $video[0] : false),
					'image'         => ($twoColumnImage ? 'https://image-render.cloud/api/renderImage?image='.$twoColumnImage.'&size=800' : false),
					'sizes'			=> $twoColumnImageSizes ?? null
				),
				'content'                   => volvo_global_block_prepare_to_view(volvo_global_blocks_prepare_all( $version->post_content, $blog_id ), $blog_id), // prepare blocks to view
				'overrideContent'           => $version_override['versionOverrideBlocks'],
			);
        }

        restore_current_blog();
    }

    return $model;
}

function volvo_global_get_version_override($version_id, $featuredCarsOptions, $blog_id)
{
    switch_to_blog( $blog_id );
	
    $versionOverride = new \WP_Query(
        array(
            'post_type'      => 'model-override',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'model',
                    'value'   => $version_id,
                    'compare' => '=',
                ),
            ),
        )
    );

    $versionOverrideBlocks = [];
    foreach ( $versionOverride->posts as $override ) {
        $versionOverrideBlocks[] = volvo_global_block_prepare_to_view(volvo_global_blocks_prepare_all( $override->post_content, $blog_id ), $blog_id); // prepare blocks to view
    }
    
    $stock = new \VGA\Classes\FeaturedCars();
    $featuredCars = $stock->get();
    
    if (count($featuredCars) > 0) {
        $featuredCarsHeading = $featuredCarsOptions['all-cars-heading'];
    } else {
        $featuredCarsHeading = $featuredCarsOptions['not-found-heading'];
    }
    
    restore_current_blog();

    return [
        'featuredCars' => [
            'heading' => $featuredCarsHeading,
            'cars' => $featuredCars,
        ],
        'versionOverrideBlocks' => $versionOverrideBlocks
    ];
}

/**
 * Model data gallery
 * 
 * @param array $galleryIds
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_model_data_gallery(array $galleryIds, int $blog_id): array
{
    $galleryPictures = [];
    $galleryThumbs = [];

    foreach ( $galleryIds as $itemId ) {
        
        $img_id = $itemId;
        
        $itemId = wp_get_attachment_url($itemId);

        $images = [
            [
                'blog_id' => $blog_id,
                'img_id' => $img_id,
                'height' => 1080,
                'width' => 1920,
                'crop' =>  false,
                'image' => $itemId,
                'query' => 1680
            ],
            [
                'blog_id' => $blog_id,
                'img_id' => $img_id,
                'height' => 700,
                'width' => 1440,
                'crop' => 'false',
                'image' => $itemId,
                'query' => 1000
            ],
            [
                'blog_id' => $blog_id,
                'img_id' => $img_id,
                'height' => false,
                'width' => 1000,
                'crop' => 'crop',
                'image' => $itemId,
                'query' => 100
            ]
        ];
        $imagesThumbs = [
            [
                'blog_id' => $blog_id,
                'img_id' => $img_id,
                'height' => 200,
                'width' => 500,
                'crop' =>  false,
                'image' => $itemId,
                'query' => 1680
            ],
            [
                'blog_id' => $blog_id,
                'img_id' => $img_id,
                'height' => 200,
                'width' => 300,
                'crop' => 'false',
                'image' => $itemId,
                'query' => 1000
            ],
            [
                'blog_id' => $blog_id,
                'img_id' => $img_id,
                'height' => false,
                'width' => 300,
                'crop' => 'crop',
                'image' => $itemId,
                'query' => 100
            ]
        ];
        $images = volvo_global_prepare_images_render($images);
        $galleryPictures[] = $images;
    
        $images = volvo_global_prepare_images_render($imagesThumbs);
        $galleryThumbs[] = $images;
    }
    
    return [
        $galleryPictures,
        $galleryThumbs
    ];
}

/**
 * Prepare all blocks
 * 
 * @param string|array $post_content_or_blocks
 * @param int $blog_id
 * @return array
 */
function volvo_global_blocks_prepare_all( $post_content_or_blocks, $blog_id ): array
{
    if ( is_string( $post_content_or_blocks ) ) {
        $blocks = parse_blocks( $post_content_or_blocks );
    } else {
        $blocks = $post_content_or_blocks;
    }

    $parsed_content = [];

    foreach ( $blocks as $block ) {
        // empty block
        if ( empty( $block['blockName'] ) ) {
            continue;
        }

        $block_name = $block['blockName'];
        $block_data = [];
        
        // acf blocks
        if ( str_starts_with( $block_name, 'acf/' ) ) {
            $raw_data      = $block['attrs']['data'] ?? [];
            $fields_def = volvo_global_blocks_get_block_fields_def( $block_name );
            
            $block_data = array_merge(
                ['block_type'  => 'acf'],
                volvo_global_blocks_maps( $raw_data, $fields_def, $block_name, $blog_id )
            );
        }
        // wp native blocks
        else {
            $block_data = [
                'block_type' => 'standard',
                'attributes' => $block['attrs'] ?? [],
                'html'       => trim( $block['innerHTML'] )
            ];

            // inner blocks
            if ( ! empty( $block['innerBlocks'] ) ) {
                $block_data['inner_blocks'] = volvo_global_block_prepare_to_view(volvo_global_blocks_prepare_all( $block['innerBlocks'], $blog_id ), $blog_id);
            }
        }

        $parsed_content[] = [
            'block_name' => $block_name,
            'data'       => $block_data
        ];
    }
    
    return $parsed_content;
}

/**
 * Definition block
 * 
 * @param string $block_name
 * @return array
 */
function volvo_global_blocks_get_block_fields_def( string $block_name ): array
{
    if ( ! function_exists( 'acf_get_field_groups' ) ) {
        return [];
    }

    $all_groups = acf_get_field_groups();
    $fields     = [];
    
    foreach ( $all_groups as $group ) {
        if ( ! empty( $group['location'] ) ) {
            foreach ( $group['location'] as $rule_group ) {
                
                foreach ( $rule_group as $rule ) {
                    if ( 'block' === $rule['param'] && '==' === $rule['operator'] && $rule['value'] === $block_name ) {
                        $group_fields = acf_get_fields( $group['key'] );
                        if ( $group_fields ) {
                            $fields = array_merge( $fields, $group_fields );
                        }
                    }
                }
            }
        }
    }
    
    return $fields;
}

// Do usunięcia po testach
global $v_volvo_global_blocks_maps;
$v_volvo_global_blocks_maps = 0;

/**
 * Maps data block
 * 
 * @param array $raw_data
 * @param array $fields_definition
 * @param string $prefix
 * @return array
 */
function volvo_global_blocks_maps( array $raw_data, array $fields_definition, string $block_name, int $blog_id, string $prefix = '' ): array
{
    global $v_volvo_global_blocks_maps;
    $v_volvo_global_blocks_maps++;
    if ($v_volvo_global_blocks_maps > 10) { // zbyt głęboko
        exit;
    }
    $parsed = [];

    foreach ( $fields_definition as $field ) {
        if ( empty( $field['name'] ) ) {
            continue;
        }
        
        $field_name  = $field['name'];
        $current_key = $prefix ? "{$prefix}_{$field_name}" : $field_name;

        // FLEXIBLE CONTENT
        if ( 'flexible_content' === $field['type'] && ! empty( $field['layouts'] ) ) {
            $parsed[ $field_name ] = [];

            // Lista użytych układów
            $layouts_used = isset( $raw_data[ $current_key ] ) ? $raw_data[ $current_key ] : [];
            
            if ( is_string( $layouts_used ) ) {
                $layouts_used = json_decode( $layouts_used, true ) ?: [];
            }

            if ( is_array( $layouts_used ) ) {
                foreach ( $layouts_used as $index => $layout_name ) {
                    $active_layout = null;
                    foreach ( $field['layouts'] as $layout ) {
                        if ( $layout['name'] === $layout_name ) {
                            $active_layout = $layout;
                            break;
                        }
                    }

                    if ( $active_layout && ! empty( $active_layout['sub_fields'] ) ) {
                        // Prefiks dla pól wewnątrz danego wiersza, np: "sekcja_0"
                        $row_prefix = "{$current_key}_{$index}";
                        
                        // Rekurencyjnie
                        $parsed_layout_fields = volvo_global_blocks_maps( $raw_data, $active_layout['sub_fields'], $block_name, $blog_id, $row_prefix );

                        $parsed[ $field_name ][] = $parsed_layout_fields;
                        //array_merge(
                        //    [ 'acf_fc_layout' => $layout_name ], // Klucz identyfikujący layout w ACF
                        //    $parsed_layout_fields
                        //);
                    }
                }
            }
        }
        // REPEATER
        elseif ( 'repeater' === $field['type'] && ! empty( $field['sub_fields'] ) ) {
            $parsed[ $field_name ] = [];
            $row_count = isset( $raw_data[ $current_key ] ) ? (int) $raw_data[ $current_key ] : 0;

            for ( $i = 0; $i < $row_count; $i++ ) {
                $row_prefix = "{$current_key}_{$i}";
                $parsed[ $field_name ][ $i ] = volvo_global_blocks_maps( $raw_data, $field['sub_fields'], $block_name, $blog_id, $row_prefix );
            }
        }
        // GROUP
        elseif ( 'group' === $field['type'] && ! empty( $field['sub_fields'] ) ) {
            $parsed[ $field_name ] = volvo_global_blocks_maps( $raw_data, $field['sub_fields'], $block_name, $blog_id, $current_key );
        }
        // OTHER FIELDS
        else {
            if ( isset( $raw_data[ $current_key ] ) ) {
                $value = $raw_data[ $current_key ];

                if ( is_string( $value ) && ( str_starts_with( $value, '[' ) || str_starts_with( $value, '{' ) ) ) {
                    $decoded = json_decode( $value, true );
                    if ( json_last_error() === JSON_ERROR_NONE ) {
                        $value = $decoded;
                    }
                }
                if ('image' === $field['type']) {
                    $value = volvo_global_block_image_render($value, $block_name, $blog_id);
                }

                if ('contact-info' === $field_name) {
                    //$value = volvo_global_block_contact_person($value, $block_name);
                }

                if ('link' === $field_name && isset($value) && is_array($value)) {
                    //$value = volvo_global_block_link($value, $block_name);
                }

                $parsed[ $field_name ] = is_string($value) ? trim($value) : $value;
            } else {
                $parsed[ $field_name ] = $field['default_value'] ?? null;
            }
        }
    }
    $v_volvo_global_blocks_maps--;
    return $parsed;
}

function volvo_global_block_image_render(int $img_id, string $block_name, int $blog_id, ?array $img_attr = null): array|int
{
    switch ($block_name) {
        case 'acf/two-column-content-component':
            $item_url = wp_get_attachment_url($img_id);
            $item_url = volvo_global_clear_url($item_url, $blog_id);

            $images = [
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 850,				
                    'width' => 1300,
                    'crop' =>  'crop',
                    'image' => $item_url,
                    'query' => 1440,										
                    'theight' => 300,
                    'twidth' => 200,
                    'tcrop' =>  false,																		
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 700,
                    'width' => 1200,
                    'crop' => 'crop',
                    'image' => $item_url,
                    'query' => 1000,										
                    'theight' => 300,
                    'twidth' => 200,
                    'tcrop' =>  false,		
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 500,
                    'width' => 900,
                    'crop' => 'crop',
                    'image' => $item_url,
                    'query' => 100,										
                    'theight' => 300,
                    'twidth' => 200,
                    'tcrop' =>  false,		
                ]
            ];

            return volvo_global_prepare_images_render($images);
        case 'acf/preview-component':
            $item_url = wp_get_attachment_url($img_id);
            $item_url = volvo_global_clear_url($item_url, $blog_id);

            $images = [
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 1024,				
                    'width' => 1562,
                    'crop' =>  'fit',
                    'image' => $item_url,
                    'query' => 1680,										
                    'theight' => 300,
                    'twidth' => 200,
                    'tcrop' =>  false,																		
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 900,				
                    'width' => 1300, 
                    'crop' =>  'fit',
                    'image' => $item_url,
                    'query' => 1440,										
                    'theight' => 300,
                    'twidth' => 200,
                    'tcrop' =>  false,																		
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 800,
                    'width' => 1100, 
                    'crop' => 'crop',
                    'image' => $item_url,
                    'query' => 1000,										
                    'theight' => 300,
                    'twidth' => 200,
                    'tcrop' =>  false,		
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => false,
                    'width' => 1300,
                    'crop' => 'crop',
                    'image' => $item_url,
                    'query' => 100,										
                    'theight' => 300,
                    'twidth' => 200,
                    'tcrop' =>  false,		
                ]
            ];

            return volvo_global_prepare_images_render($images);
        case 'acf/banner-with-content-overlay':
            if (is_null($img_attr)) {
                return $img_id;
                break;
            }
            $item_url = wp_get_attachment_url($img_id);
            
            $img_width = 0;
            $img_height = 0;
            
            if (file_exists('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($item_url)['path'])) {
                $img_width = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($item_url)['path'])[0];
                $img_height = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($item_url)['path'])[1];
            }
            
            $crop_image = $img_attr['crop_image'];
            
            $crop_image = ($crop_image ? $crop_image : 'crop-center');
            
            $count_ratio = ($img_height > 0 ? $img_width/$img_height : false);
        
            $images = [
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => ($count_ratio > 1.7 ? 1000 : 1200),
                    'width' => ($count_ratio > 1.7 ? 2500 : 2500),
                    'crop'  => ($count_ratio > 1.7 ? 'crop' : $crop_image),
                    'image' => $item_url,
                    'query' => 1500
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,	
                    'height' => false,
                    'width' => 1700,
                    'crop'  => false,
                    'image' => $item_url,
                    'query' => 1200
                ],
                [	'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => false,
                    'width' => 1200,
                    'crop'  => false,
                    'image' => $item_url,
                    'query' => 900
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => false,
                    'width' => 900,
                    'crop'  => false,
                    'image' => $item_url,
                    'query' => 700
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 767,
                    'width' => 767,
                    'crop'  => 'crop',
                    'image' => $item_url,
                    'query' => 100
                ]
                
            ];

            return volvo_global_prepare_images_render($images);
        case 'acf/hero-image':
            if (is_null($img_attr)) {
                return $img_id;
                break;
            }
            
            $item_url = wp_get_attachment_url($img_id);
            $item_url = volvo_global_clear_url($item_url, $blog_id);

            $img_width = 0;
            $img_height = 0;
            
            if (file_exists('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($item_url)['path'])) {
                $img_width = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($item_url)['path'])[0];
                $img_height = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($item_url)['path'])[1];
            }

            $crop_image = $img_attr['crop_image'];
            
            $crop_image = ($crop_image ? $crop_image : 'crop-center');

            $count_ratio = ($img_height > 0 ? $img_width/$img_height : false);
            
            $images = [
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => ($count_ratio > 1.7 ? 1024 : 1020),
                    'width' => ($count_ratio > 1.7 ? 1920 : 1920),
                    'crop'  => ($count_ratio > 1.7 ? 'max' : $crop_image),
                    'image' => $item_url,
                    'query' => 1500
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,	
                    'height' => false,
                    'width' => 1500,
                    'crop'  => false,
                    'image' => $item_url,
                    'query' => 1200
                ],
                [	'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => false,
                    'width' => 1200,
                    'crop'  => false,
                    'image' => $item_url,
                    'query' => 900
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => false,
                    'width' => 900,
                    'crop'  => false,
                    'image' => $item_url,
                    'query' => 700
                ],
                [
                    'blog_id' => $blog_id,
                    'img_id' => $img_id,
                    'height' => 767,
                    'width' => 767,
                    'crop'  => 'crop',
                    'image' => $item_url,
                    'query' => 100
                ]
                
            ];
                
            return volvo_global_prepare_images_render($images);
        default:
            return $img_id;
    }

    return volvo_global_prepare_images_render($images);
}

function volvo_global_clear_url(string $url, int $blog_id): string
{
    $domain = get_blogaddress_by_id($blog_id);

    $url = str_replace(
        [
            'https://main.volvocars-partner.pl/',
            'https://main-backend.volvotest.pl/',
        ], $domain, $url);

    return $url;
}

function volvo_global_youtube_link_to_video_id(string $youtube_url): string
{
    $pattern = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

    preg_match($pattern, $youtube_url, $matches);

    if (isset($matches[1])) {
        return $matches[1];
    } else {
        // for backwards compability with videoId
        return $youtube_url;
    }
}

function volvo_global_block_contact_person(int $person_id, string $block): mixed
{
    switch ($block) {
        case 'acf/two-column-content-component':
        case 'acf/preview-component':
            return [
                'name' => get_field('name', $person_id) . ' ' . get_field('surname', $person_id),
                'position' => get_field('position', $person_id),
                'phone' => get_field('phone', $person_id),
                'email' => get_field('email', $person_id),
            ];

            break;
        default:
            return $person_id;
    }
}

function volvo_global_block_link($content_link, $block)
{
    switch ($block) {
        case 'acf/two-column-content-component':
        case 'acf/preview-component':
            if (!is_null($content_link) && !empty($content_link) && is_array($content_link) && !empty($content_link)) {
                $result = volvo_global_build_link($content_link);

                if (strpos($result['url'], '---') !== false) {

                    $rep = explode('---', $result['url']);

                    $result['url'] = '/dostepne-na-miejscu/#' . $rep[1];
                }

                return $result;
            }

            break;
    }

    return $content_link;
}

function volvo_global_block_description(string $description): string
{
    $Parsedown = new \Parsedown();
    return $Parsedown->text($description);
}

function volvo_global_get_post_tags()
{
	$post_id = get_the_ID();
	$tags = wp_get_post_terms($post_id, 'post_tag');

	if ($tags && !is_wp_error($tags)) {
		return $tags;
	}

	return [];
}

function volvo_global_block_prepare_to_view(array $blocks, int $blog_id): array
{
    foreach($blocks as $key_block => $block) {
        switch ($block['block_name']) {
            case 'acf/anchor':
                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'anchor'     => $block['data']['anchor'],
                    ],
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/banner-with-content-overlay':
                
                $link = $block['data']['link'];

                $hasButton = ! empty( $link );
                $button    = array();

                if ( $hasButton ) {
                    $button = volvo_global_build_link( $link );
                    
                    if (strpos($button['url'],'#') !== false) {
                        
                        $rep = explode('#',$button['url']);
                        
                        $button['url'] = '/dostepne-na-miejscu/#'.$rep[1];
                    }
                }
                
		        $crop_image = 'crop-center';
                $img_attr = [
                    'crop_image' => $crop_image
                ];
                
                $images = volvo_global_block_image_render($block['data']['img'], $block['block_name'], $blog_id, $img_attr);

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'image'       => $images,
                        'heading'     => $block['data']['heading'],
                        'description' => $block['data']['description'],
                        'format'      => $block['data']['format_banner'],
                        'hasButton'   => $hasButton,
                        'button'      => $button,
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/cost-map':

                $selectedModel = $block['data']['cost-map-model'];
                $selectedVersion = $block['data']['cost-map-version'];

                $opt    = getBasicOptions(0);
                
                $tmp        = array();
                $engine     = array();
                $chargers   = (int) $opt['chargers'][0];
                
                $addresses  = array();

                for ($i = 0; $chargers > $i; $i++) {
                    $db          = str_replace("\r\n", "\\n", $opt['chargers_' . $i . '_charger_type'][0]);
                    $addresses[] = array(
                        $opt['chargers_' . $i . '_charger_address'][0],
                        $db,
                        '#',
                        ($opt['chargers_' . $i . '_super_charger'][0] !== '' ? true : false),

                    );
                }

                $cars_chargers = (int) $opt['calculator_chargers'][0];

                $electrification = new \VGA\Clasess\Electrification();

                $ranges_cost = array();

                for ($i = 0; $cars_chargers > $i; $i++) {

                    $model        = str_replace(' ', '_', $opt['calculator_chargers_' . $i . '_electric_model_charge'][0]);
                    $motor        = str_replace(' ', '_', $opt['calculator_chargers_' . $i . '_electric_engine_charge'][0]);
                    $charger      = $opt['calculator_chargers_' . $i . '_calculator_charger_address'][0];
                    $charger_time = $opt['calculator_chargers_' . $i . '_calculator_charger_time'][0];
                    $ranges_cost[$model . '_' . $motor . '_' . $charger] = $charger_time;
                }
                
                if ($selectedModel) {
                    $excludedEngines = $electrification->get_version_excluded_engines($selectedModel, $selectedVersion);
                    $engine = $electrification->get_car_engines($selectedModel, $excludedEngines);
                } else {
                    list($tmp, $engine) = $electrification->get_models_and_engines();
                }
                
                $chargers        = array();
                $chargers[]      = array(
                    'value' => $opt['calculator_chargers_0_calculator_charger_address'][0],
                    'text'  => $opt['calculator_chargers_0_calculator_charger_text'][0],
                    'times' => $opt['calculator_chargers_0_calculator_charger_time'],
                );
                $chargers[]      = array(
                    'value' => $opt['calculator_chargers_1_calculator_charger_address'][0],
                    'text'  => $opt['calculator_chargers_1_calculator_charger_text'][0],
                    'times' => $opt['calculator_chargers_1_calculator_charger_time'],
                );
                $chargers[]      = array(
                    'value' => $opt['calculator_chargers_2_calculator_charger_address'][0],
                    'text'  => $opt['calculator_chargers_2_calculator_charger_text'][0],
                    'times' => $opt['calculator_chargers_2_calculator_charger_time'],
                );
                $additional_info = array();

                $additional_info[0]['title'] = $opt['kw_add_info_title'][0];
                $additional_info[0]['desc']  = $opt['kw_add_info_desc'][0];
                $additional_info[1]['title'] = $opt['kw_add_info_title_2'][0];
                $additional_info[1]['desc']  = $opt['kw_add_info_desc_2'][0];

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'chargers'        => $chargers,
                        'models'          => $tmp,
                        'engine'          => $engine,
                        'dataset'         => $electrification->dataSet,
                        'ranges_calc'     => $electrification->range_calc,
                        'ranges_cost'     => $ranges_cost,
                        'additional_info' => $additional_info,
                        'charger_legal'   => (!empty($opt['charger_disclaimer']) ? $opt['charger_disclaimer'][0] : ''),
                        'min_price'       => $opt['min_price_kw'][0],
                        'max_price'       => $opt['max_price_kw'][0],
                        'selectedModel'	  => $block['data']['cost-map-model'],
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/electrification-map':

                $opt     = getBasicOptions(0);

                $chargers  = (int) $opt['chargers'][0];

                $addresses = array();

                for ($i = 0; $chargers > $i; $i++) {
                    $db          = str_replace("\r\n", "\\n", $opt['chargers_' . $i . '_charger_type'][0]);
                    $addresses[] = array(
                        $opt['chargers_' . $i . '_charger_address'][0],
                        $db,
                        '#',
                        ($opt['chargers_' . $i . '_super_charger'][0] !== '' ? true : false),

                    );
                }
                $electrification = new \VGA\Classes\Electrification();

                $selectedModel = $block['data']['electrification-map-model'];
                $selectedVersion = $block['data']['electrification-map-version'];
                
                if ($selectedModel) {
                    $excludedEngines = $electrification->get_version_excluded_engines($selectedModel, $selectedVersion);
                    
                    $engine = $electrification->get_car_engines($selectedModel, $excludedEngines);
                    
                } else {
                    list($tmp, $engine) = $electrification->get_models_and_engines();
                }

                $content     = $tmp;
                $header_size = ($content['header'] ? count($content['header']) : 0);

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'showMap'			=> ($block['data']['electrification-map-show'] ? false : true ),
                        'firstElement'		=> false,
                        'points'            => $addresses,
                        'content'           => $content,
                        'ranges'            => $electrification->range,
                        'models'            => $tmp,
                        'engine'            => $engine,
                        'size'              => $header_size,
                        'combinations'      => $electrification->combinations,
                        'combinations_desc' => $electrification->combinations_desc,
                        'legal_map'         => (!empty($opt['maps_disclaimer']) ? $opt['maps_disclaimer'][0] : ''),
                        'selectedModel'		=> $selectedModel,
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/gallery':

                $gallery      = $block['data']['gallery'];
                $galleryItems = array();

                foreach ( $gallery as $itemId ) {
                    $img_id = $itemId;
                    $itemId = wp_get_attachment_url($itemId);

                    $images = [
                        [
                            'blog_id' => $blog_id,
                            'img_id' => $img_id,
                            'height' => 1080,
                            'width' => 1920,
                            'crop' =>  'crop',
                            'image' => $itemId,
                            'query' => 1680,										
                            'theight' => 300,
                            'twidth' => 200,
                            'tcrop' =>  false,																		
                        ],
                        [
                            'blog_id' => $blog_id,
                            'img_id' => $img_id,
                            'height' => 700,
                            'width' => 1440,
                            'crop' => 'false',
                            'image' => $itemId,
                            'query' => 1000,										
                            'theight' => 300,
                            'twidth' => 200,
                            'tcrop' =>  false,		
                        ],
                        [
                            'blog_id' => $blog_id,
                            'img_id' => $img_id,
                            'height' => false,
                            'width' => 1000,
                            'crop' => 'crop',
                            'image' => $itemId,
                            'query' => 100,										
                            'theight' => 300,
                            'twidth' => 200,
                            'tcrop' =>  false,		
                        ]
                    ];

                    $images = volvo_global_prepare_images_render($images);

                    $galleryItems[] = array(
                        'mobileImage'  => $images,
                        'desktopImage' => $images,
                        'full'         => '',
                        'domain'       => get_site_url(),
                    );
                }
                
                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
        				'gallery' => $galleryItems,
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/hero-image':
                $bigDescription   = $block['data']['bigDescription'];
                $smallDescription = $block['data']['smallDescription'];
                $darkOverlay      = $block['data']['darkOverlay'];

                $crop_image = $block['data']['field_crop'];
                $crop_image = ($crop_image ? $crop_image : 'crop-center');
                $img_attr = [
                    'crop_image' => $crop_image
                ];
                
                $images = volvo_global_block_image_render($block['data']['img'], $block['block_name'], $blog_id, $img_attr);

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'image' => $images,
                        'bigDescription'   => $bigDescription,
                        'smallDescription'  => $smallDescription,
                        'darkOverlay'      => $darkOverlay,
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/html-code':
                $htmlCode = strip_tags($block['data']['html_code_render'],'<script><iframe><img>');

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'htmlCode'	  => $htmlCode,
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/offer-box':
                $offerBoxes = [];

                $boxesField = $block['data']['offer_boxes'];
                $heading = $block['data']['heading'];

                foreach ($boxesField as $box) {
                    $carModel = $box['widget_model'];

                    $query = new \WP_Query([
                        'post_type'      => 'stock-car',
                        'posts_per_page' => 4,
                        'post_status'    => 'publish',
                        'cache_results'  => true,
                        'meta_key'       => 'model',
                        'meta_value'     => $carModel,
                        'orderby'        => 'rand'
                    ]);


                    foreach ($query->posts as $car) {

                        $category = get_field('category', $car->ID);
                        $engine = get_field('engine', $car->ID);
                        $price = get_field('regular-price', $car->ID);
                        $carUrl = get_permalink($car->ID);
                        $images = get_field('images', $car->ID);

                        $getImage = null;
                        if (! empty($images)) {
                            $img_id = $images[0];
                            $itemId = wp_get_attachment_url($img_id);

                            //$image = new ImageBuilder($image);
                            //$image->addSize(array(288, 162));
                            //$image->addSize(array(576, 324));
                            //$image->addSize(array(864, 486));
                            //$image->addMediaQuery(null, '288px', true);
                            $images = [
                                [
                                    'blog_id' => $blog_id,
                                    'img_id' => $img_id,
                                    'height' => 162,
                                    'width' => 288,
                                    'crop' =>  true,
                                    'image' => $itemId,
                                    'query' => 1680
                                ],
                                [
                                    'blog_id' => $blog_id,
                                    'img_id' => $img_id,
                                    'height' => '324',
                                    'width' => 576,
                                    'crop' => 'true',
                                    'image' => $itemId,
                                    'query' => 1000
                                ],
                                [
                                    'blog_id' => $blog_id,
                                    'img_id' => $img_id,
                                    'height' => 486,
                                    'width' => 864,
                                    'crop' => 'crop',
                                    'image' => $itemId,
                                    'query' => 100
                                ]
                            ];
                            $getImage = volvo_global_prepare_images_render($images);
                        }
                        
                        $offerBoxes[] = [
                            'widget_model' => $carModel,
                            'category'     => $category,
                            'engine'       => $engine,
                            'carUrl'       => $carUrl,
                            'price'        => $price,
                            'image'        => $getImage,

                        ];
                    }
                    
                }

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'offerBoxes' => $offerBoxes,
                        'heading'    => $heading,
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/offer-boxes':
                $items = [];
                if (is_array($block['data']['items']) && array_filter($block['data']['items'])) {
                    foreach ( $block['data']['items'] as $box ) {
                        $hasButton = ! empty( $box['link'] );
                        $rewrite = false;
                        
                        if ($hasButton && strpos($box['link']['url'],'#') !== false) {
                            $rewrite = true;
                            $rep = explode('#',$box['link']['url']);
                            
                            $box['link']['url'] = '/dostepne-na-miejscu/#'.$rep[1];
                            
                        }
                        $link = $hasButton ? volvo_global_build_link( $box['link'] ) : null;
                        if ($rewrite) {
                            $box['link']['text'] = $box['link']['title'];
                            $link = $box['link'];
                        }

                        $items[] = array(
                            'icon'        => $box['icon'],
                            'heading'     => $box['heading'],
                            'description' => $box['description'],
                            'hasButton'   => $hasButton,
                            'link'        => $link,
                        );
                    }
                }

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'layout'     => $block['data']['layout'],
                        'pullUp'     => $block['data']['pull_up'],
                        'items'      => $items,
                        'iconView'   => $block['data']['iconview'],
                    ],
                ];

                $blocks[$key_block] = $result;
                break;
            case "acf/offer-cards":
                $cards = [];
                $cardsField = $block['data']['cards'];

                foreach ($cardsField as $key => $card) {
                    $link = '';

                    if ($card['link'] !== "") {
                        $link = volvo_global_build_link($card['link']);
                    }
                    $img_id = $card['image'];
                    $itemId = wp_get_attachment_url($card['image']);
                    $images = [
                        [
                            'blog_id' => $blog_id,
                            'img_id' => $img_id,
                            'height' => 275,
                            'width' => 472,
                            'crop' =>  'crop',
                            'image' => $itemId,
                            'query' => 1200
                        ],
                        [
                            'blog_id' => $blog_id,
                            'img_id' => $img_id,
                            'height' => 175,
                            'width' => 300,
                            'crop' => 'crop',
                            'image' => $itemId,
                            'query' => 100
                        ]
                    ];
                    
                    $images = volvo_global_prepare_images_render($images);

                    $cards[] = [
                        'link' => $link,
                        'heading' => $card['heading'],
                        'description' => $card['description'],
                        'image' => $images,
                        'ctaText' => $card['cta-text'],
                    ];
                }



                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'heading' => $block['data']['heading'],
                        'cards' => $cards
                    ],
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/preview-component':
                $reverse = $block['data']['image-position'] ?? false;
                
                $allowed_tags = ['h1','h2','h3','h4','h5','h6'];
                $heading_tag = $block['data']['heading_tag'];

                if (!in_array($heading_tag, $allowed_tags)) {
                    $heading_tag = 'h2';
                }
                
                foreach ( $block['data']['content'] as $k => &$contentItem ) {
                    if (array_key_exists('contact-info', $contentItem)) {
                        $contentItem['contactPerson'] = volvo_global_block_contact_person($contentItem['contact-info'], $block['block_name']);
                    }
                    
                    if (array_key_exists('link', $contentItem)) {
                        $contentItem['link'] = volvo_global_block_link($contentItem['link'], $block['block_name']);
                    }
                }
                
                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'reverse'      => $reverse == 'right',
                        'image'        => $block['data']['img'],
                        'heading_tag'  => $heading_tag,
                        'heading'      => $block['data']['heading'],
                        'content'      => $block['data']['content'],
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/site-heading';
                $version = $block['data']['field_quick_header'];
		
                if ( $version && $version == 'tak' ) {
                    $template = 'grey-heading';
                } else if ($version && $version == 'blog' ) {
                    $template = 'site-heading-blog';
                } else {
                    $template = 'site-heading';
                }
                
                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'template'        => $template,
                        'heading'         => $block['data']['heading'],
                        'header_type'     => $block['data']['header_type'],
                        'description'     => $block['data']['description'],
                        'date'            => get_the_date('d.m.Y'),
                        'tags'	          => volvo_global_get_post_tags(),
                        'current_blog_id' => $blog_id
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case 'acf/two-column-content-component':
                $reverse = $block['data']['image-position'] ?? false;
                $video = $block['data']['video'];

                if (array_key_exists('description', $block['data']['content'][0])) {
                    $contentParsedown = volvo_global_block_description($block['data']['content'][0]['description']);
                    $block['data']['content'][0]['description'] = $contentParsedown;
                }

                foreach ( $block['data']['content'] as &$contentItem ) {
                    if (array_key_exists('contact-info', $contentItem)) {
                        $contentItem['contactPerson'] = volvo_global_block_contact_person($contentItem['contact-info'], $block['block_name']);
                    }

                    if (array_key_exists('link', $contentItem) && !empty($contentItem['link'])) {
                        $contentItem['link'] = volvo_global_block_link($contentItem['link'], $block['block_name']);
                    }
                }

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'reverse' => 'right' == $reverse,
                        'custom_reverse' => $reverse, 
                        'image' => $block['data']['img'],
                        'subheading' => $block['data']['subheading'],
                        'heading' => $block['data']['heading'],
                        'content' => $block['data']['content'],
                        'left_content' => $block['data']['single_opt_column'],
                        'equal_columns' => $block['data']['imgage-width'],
                        'video' => ($video ? volvo_global_youtube_link_to_video_id($video) : false),
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            case "acf/two-image":
                $firstImageId = $block['data']['firstPicture'];
                $secondImageId = $block['data']['secondPicture'];

                $img_id_first = $firstImageId;
                $img_id_second = $secondImageId;

                $itemId_first = wp_get_attachment_url($firstImageId);
                $itemId_second = wp_get_attachment_url($img_id_second);

                $image_first = [
                    [
                        'blog_id' => $blog_id,
                        'img_id' => $img_id_first,
                        'height' => 800,				
                        'width' => 1200,
                        'crop' =>  'crop',
                        'image' => $itemId_first,
                        'query' => 1680,										
                        'theight' => 300,
                        'twidth' => 200,
                        'tcrop' =>  false,																		
                    ],
                    [
                        'blog_id' => $blog_id,
                        'img_id' => $img_id_first,
                        'height' => 600,
                        'width' => 800,
                        'crop' => 'crop',
                        'image' => $itemId_first,
                        'query' => 1000,										
                        'theight' => 300,
                        'twidth' => 200,
                        'tcrop' =>  false,		
                    ],
                    [
                        'blog_id' => $blog_id,
                        'img_id' => $img_id_first,
                        'height' => 450,
                        'width' => 450,
                        'crop' => 'crop',
                        'image' => $itemId_first,
                        'query' => 100,										
                        'theight' => 300,
                        'twidth' => 200,
                        'tcrop' =>  false,		
                    ]
                ];
                $image_second = [
                    [
                        'blog_id' => $blog_id,
                        'img_id' => $img_id_second,
                        'height' => 800,				
                        'width' => 1200,
                        'crop' =>  'crop',
                        'image' => $itemId_second,
                        'query' => 1680,										
                        'theight' => 300,
                        'twidth' => 200,
                        'tcrop' =>  false,																		
                    ],
                    [
                        'blog_id' => $blog_id,
                        'img_id' => $img_id_second,
                        'height' => 600,
                        'width' => 800,
                        'crop' => 'crop',
                        'image' => $itemId_second,
                        'query' => 1000,										
                        'theight' => 300,
                        'twidth' => 200,
                        'tcrop' =>  false,		
                    ],
                    [
                        'blog_id' => $blog_id,
                        'img_id' => $img_id_second,
                        'height' => 450,
                        'width' => 450,
                        'crop' => 'crop',
                        'image' => $itemId_second,
                        'query' => 100,										
                        'theight' => 300,
                        'twidth' => 200,
                        'tcrop' =>  false,		
                    ]
                ];
                $image_first = volvo_global_prepare_images_render($image_first);
                $image_second = volvo_global_prepare_images_render($image_second);

                $result = [
                    'block_name' => $block['block_name'],
                    'data' => [
                        'firstImage' => $image_first,
                        'secondImage' => $image_second,
                    ]
                ];

                $blocks[$key_block] = $result;
                break;
            default:
                //var_dump($block['block_name']);exit;
        }
    }

    return $blocks;
}
