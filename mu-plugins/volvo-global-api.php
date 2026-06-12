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
require_once WPMU_PLUGIN_DIR . '/volvo-global-api/classes/CarSpecificationDataImporter.class.php';
require_once WPMU_PLUGIN_DIR . '/volvo-global-api/components/blocks.php';


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

add_action('rest_api_init', function () {
    // Global page endpoint - returns page data for any path
    register_rest_route('volvo/v1', '/page', array(
        'methods'             => 'POST',
        'callback'            => 'volvo_global_post_page',
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

$volvo_global_years = array(
    'Y' => 2000,
    '1' => 2001,
    '2' => 2002,
    '3' => 2003,
    '4' => 2004,
    '5' => 2005,
    '6' => 2006,
    '7' => 2007,
    '8' => 2008,
    '9' => 2009,
    'A' => 2010,
    'B' => 2011,
    'C' => 2012,
    'D' => 2013,
    'E' => 2014,
    'F' => 2015,
    'G' => 2016,
    'H' => 2017,
    'J' => 2018,
    'K' => 2019,
    'L' => 2020,
    'M' => 2021,
    'N' => 2022,
    'P' => 2023,
    'R' => 2024,
    'S' => 2025,
    'T' => 2026,
    'V' => 2027,
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
        if (empty($value['img_id']) || empty($value['image'])) {
            unset($data[$key]);
            continue;
        }
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

function volvo_global_prepare_image_for_render(
    int $blog_id,
    int|string|null $img_id,
    ?int $width,
    ?int $height,
    ?string $img_url,
    ?string $crop,
    ?int $twidth = null,
    ?int $theight = null,
    ?string $tcrop = null,
) {
    return [
        'blog_id'   => $blog_id,
        'img_id'    => $img_id,
        'height'    => $height,
        'width'     => $width,
        'crop'      => $crop,
        'image'     => $img_url,
        'twidth'    => $twidth,
        'theight'   => $theight,
        'tcrop'     => $tcrop,
    ];
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
        
        $latest_query = volvo_global_get_latest_campaign(3 - count($slide_posts), $existing_ids);
        
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

function volvo_global_get_latest_campaign(int $count, array $exclude_ids = [])
{
    return new WP_Query(array(
        'post_type'      => 'campaign',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'post__not_in'   => $exclude_ids,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
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
    
    $main_site = false;
    // Fallback to global site if empty
    if (empty($offer1['imageCard']) && empty($offer2['imageCard2'])) {
        switch_to_blog(1);
        $main_site = true;

        $offer1 = get_field('offer1', 'options-homepage');
        $offer2 = get_field('offer2', 'options-homepage');
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
    
    if ($main_site) {
        restore_current_blog();
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
    
    $main_site = false;
    // Fallback to global
    if (empty($box1['imageBox']) && empty($box2['imageBox2']) && empty($box3['imageBox3'])) {
        switch_to_blog(1);
        $main_site = true;

        $box1 = get_field('offerBox1', 'options-homepage');
        $box2 = get_field('offerBox2', 'options-homepage');
        $box3 = get_field('offerBox3', 'options-homepage');
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
    
    if ($main_site) {
        restore_current_blog();
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
    
    $main_site = false;
    // Fallback to global site if empty
    if (empty($slider_family_box)) {
        switch_to_blog(1);
        $main_site = true;

        $slider_family_box = get_field('sliderFamilyBox', 'options-homepage');
        $slider_title = get_field('sliderTitle', 'options-homepage');
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

    if ($main_site) {
        restore_current_blog();
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
    
    $main_site = false;
    // Fallback to global
    if (empty($offer_card['image'])) {
        switch_to_blog(1);
        $main_site = true;

        $offer_card = get_field('OfferCard', 'options-homepage');
        
    }
    
    if (empty($offer_card)) {
        if ($main_site) {
            restore_current_blog();
        }
    
        return array();
    }

    $result = array(
        'image'       => volvo_global_prepare_image($offer_card['image'] ?? null),
        'title'       => $offer_card['headingOffer'] ?? '',
        'description' => nl2br(esc_html($offer_card['description'] ?? '')),
        'link'        => volvo_global_build_link($offer_card['link'] ?? null),
    );

    if ($main_site) {
        restore_current_blog();
    }
    
    return $result;
}

/**
 * Get offers (sales section) data
 *
 * @return array
 */
function volvo_global_get_offers() {
    $offers_options = get_field('offers', 'options-homepage');
    
    $main_site = false;
    // Fallback to global site if empty
    if (empty($offers_options['heading']) && empty($offers_options['offer-boxes'])) {
        switch_to_blog(1);
        $main_site = true;

        $offers_options = get_field('offers', 'options-homepage');
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
    
    if ($main_site) {
        restore_current_blog();
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
    
    $time_start = microtime(true);

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
    
    global $is_volvo_ms_global_page;
    $is_volvo_ms_global_page = false;
    
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
    } elseif (in_array($path_parts[0], ['kontakt', 'contact'])) {

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
        $response['source']             = volvo_global_get_page_source($page, $is_volvo_ms_global_page);
        $response['formShowrooms']      = volvo_global_get_showrooms_form($showrooms);
        $response['thankyouImage']      = volvo_global_get_contact_thank_you_image();
        $response['thankYouCode']       = volvo_global_get_contact_thank_you();
        
    // For models pages, get page by path
    } elseif (in_array($path_parts[0], ['modele', 'models'])) {
        if (count($path_parts) == 3) {

            $response = array_merge(
                $response,
                volvo_global_get_model_page_item($path_parts, $blog_id)
            );

        } else { // index

            $response = array_merge(
                $response,
                volvo_global_get_model_index($path_parts, $blog_id)
            );
            
        }
    } elseif (in_array($path_parts[0], ['blog'])) {

        if (count($path_parts) == 1 || ((count($path_parts) === 3) && $path_parts[1] === 'page')) { // index
            volvo_global_set_paged($path_parts);

            $response = array_merge(
                $response,
                volvo_global_get_blog_index($path_parts, $blog_id)
            );
        
        } else { // item

            $post = get_page_by_path($path_parts[1], OBJECT, 'blog');

            if ($post) {
                $response['id']         = $post->ID;
                $response['title']      = get_the_title($post->ID);
                $response['content']    = volvo_global_prepare_content_block($post->post_content, $blog_id);

            } else {
                $response['page_404'] = true;
            }
        }

    } elseif (in_array($path_parts[0], ['serwis', 'service'])) {

        $post = get_page_by_path($path_parts[0]);
        if (!$post) {
            $post = volvo_global_get_global_page_by_path($path_parts[0]);
            $is_volvo_ms_global_page = true;
        }

        if ($post) {
            $response['id']                 = $post->ID;
            if ($is_volvo_ms_global_page) {
                switch_to_blog(1);
            }
            $response['title']              = get_the_title($post->ID);
            if ($is_volvo_ms_global_page) {
                restore_current_blog();
            }

            $response = array_merge($response, volvo_global_get_service_page_index($blog_id));

        } else {
            $response['page_404'] = true;
        }

    } elseif (in_array($path_parts[0], ['kampanie', 'campaigns'])) {

        if (!isset($path_parts[1]) || count($path_parts) > 2) {
            $response['page_404'] = true;
        } else {
            $post = get_page_by_path($path_parts[1], OBJECT, 'campaign');
            
            if (!$post) {
                $post = volvo_global_get_global_page_by_path($path_parts[1], 'campaign');
                $is_volvo_ms_global_page = true;
            }

            if ($post) {
                $response = array_merge($response, volvo_global_get_campaing_page($post, $blog_id));
            } else {
                $response['page_404'] = true;
            }
        }

    } elseif ($path_parts[0] === 'jazda-testowa') {
        $post = get_page_by_path($path_parts[0]);

        $response = array_merge($response, volvo_global_get_test_drive_index($blog_id));

    } elseif (strpos($path_parts[0], 'jazda-testowa-model-') !== false) {
        
        $slug = str_replace('jazda-testowa-model-', '', $path_parts[0]);
        $response = array_merge($response, volvo_global_get_test_drive_model($slug, $blog_id));

    } elseif (in_array($path_parts[0], ['wydarzenia', 'events'], true)) {
        $page = get_page_by_path($path_parts[0]);

        if (!$page) {
            $response['page'] = null;
        } else {

            if ($blog_id == 36) {
                $blog_id = 37;
            }
            
            $instance_id = get_fields('options-dealer')['event_instance'];
            if ($instance_id) {
                $blog_id = $instance_id;
            }

            $response['page'] = array(
                'instance_id'    => $blog_id,
                'id'             => $page->ID,
                'title'          => get_the_title($page->ID),
                'content'        => volvo_global_prepare_content_block($page->post_content, $blog_id),
                'featured_image' => volvo_global_prepare_image(get_post_thumbnail_id($page->ID)),
                'acf'            => function_exists('get_fields') ? get_fields($page->ID) : array(),
            );
        }

    } else {
        // For other pages, get page by path
        $page = get_page_by_path(ltrim($path, '/'));
        
        $is_volvo_ms_global_page = false;
        if (!$page) {
            $page = volvo_global_get_global_page_by_path(ltrim($path, '/'));
            $is_volvo_ms_global_page = true;
        }

        if ($page) {
            if ($is_volvo_ms_global_page) {
                switch_to_blog(1);
            }
            $response['page'] = array(
                'id'             => $page->ID,
                'title'          => get_the_title($page->ID),
                'content'        => volvo_global_prepare_content_block($page->post_content, get_current_blog_id()),
                'featured_image' => volvo_global_prepare_image(get_post_thumbnail_id($page->ID)),
                'acf'            => function_exists('get_fields') ? get_fields($page->ID) : array(),
            );
            if ($is_volvo_ms_global_page) {
                restore_current_blog();
            }
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

    $time_end = microtime(true);
    if ($_GET['api-test']) {
        $response['time'] = $time_end - $time_start;
        if (defined('APP_START_TIME')) {
            $response['time_app'] = $time_end - APP_START_TIME;
        }
    }
    return new WP_REST_Response($response, 200);
}

/**
 * POST page endpoint callback
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function volvo_global_post_page($request) {
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
    
    if (in_array($path_parts[0], ['serwis', 'service'])) {
        $checkYear = $request->get_param('checkYear');
        if ($checkYear == '1') {

            $response = array_merge($response, volvo_global_post_service_check_vin($request, $blog_id));

        } else {
            return new \WP_Error( 'invalid_request', __( 'Page not found.', 'webinar-plugin' ), [ 'status' => 404 ] );
        }

    } else {
        return new \WP_Error( 'invalid_request', __( 'Page not found.', 'webinar-plugin' ), [ 'status' => 404 ] );
    }

    restore_current_blog();
    
    return new WP_REST_Response($response, 200);
}

// PAGES

/**
 * Model index
 * 
 */
function volvo_global_get_model_index(array $path, int $blog_id) {
    $response = [];

    switch_to_blog(1);
    
    $page = get_page_by_path($path[0]);

    if (!$page) {
        $response['page_404'] = true;
        return $response;
    }
    
    global $post;
    $post = $page;
    setup_postdata( $post );

    $response['id']     = $page->ID;
    $response['title']  = get_the_title($page->ID);
    $response['data']   = volvo_global_get_models($blog_id);

    wp_reset_postdata();

    restore_current_blog();

    return $response;
}

/**
 * Model page
 * 
 */
function volvo_global_get_model_page_item(array $path, int $blog_id) {

    switch_to_blog(1);

    $category_model = volvo_global_get_model_category_term($path[1]);
    $response = [];

    if (!$category_model) {
        $response['page_404'] = true;
        return $response;
    }

    $page = volvo_global_get_model_page_by_category($path[2], $category_model);

    if (!$page) {
        $response['page_404'] = true;
        return $response;
    }

    global $post;
    $post = $page;
    setup_postdata( $post );

    volvo_global_set_paged();
    
    $response['id']     = $page->ID;
    $response['title']  = get_the_title($page->ID);
    $response['model']  = volvo_global_get_model_data($page, $blog_id);

    wp_reset_postdata();

    restore_current_blog();

    return $response;
}

/**
 * Blog index
 * 
 */
function volvo_global_get_blog_index(array $path, int $blog_id)
{
    $page = get_page_by_path($path[0]);
            
    if ($page) {
        global $post;
        $post = $page;
        setup_postdata( $post );

        $response = [];
        $response['id']         = $page->ID;
        $response['title']      = get_the_title($page->ID);

        $response['title_1']    = get_field('title_1');
        $response['title_2']    = get_field('title_2');

        $response = array_merge($response, volvo_global_get_blog_pages($blog_id));

        wp_reset_postdata();
    } else {
        $response['page_404'] = true;
    }

    return $response;
}

/**
 * Test drive index - list of models available for test drive
 *
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_test_drive_index(int $blog_id): array
{
    $response = [];
    $items = [];

    switch_to_blog(1);

    $categories = get_terms([
        'taxonomy' => 'model_category',
        'hide_empty' => false,
    ]);

    $typesField = get_field_object('field_604a1c9f94d09');
    $types = $typesField['choices'] ?? [];

    $blog_id_current = get_current_blog_id();

    foreach ($categories as $category) {
        $item = [
            'heading' => $category->name,
            'carTypes' => [],
        ];

        $modelsQuery = new \WP_Query([
            'post_type' => 'model',
            'post_parent' => 0,
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'model_category',
                    'terms' => $category->term_id,
                ],
            ],
        ]);

        $carsByType = [];

        foreach ($modelsQuery->posts as $model) {
            $modelId = $model->ID;

            $testDriveEnabled = get_field('testDrive', $modelId);
            if (!$testDriveEnabled) {
                continue;
            }

            $modelTypeKey = get_field('type', $modelId);
            if (!$modelTypeKey || !isset($types[$modelTypeKey])) {
                continue;
            }

            $img_id = get_field('thumbnail', $modelId);
            $img_url = wp_get_attachment_url($img_id);

            $images = [
                volvo_global_prepare_image_for_render($blog_id_current, $img_id, 500, null, $img_url, true),
            ];

            $images = volvo_global_prepare_images_render($images);

            $url = get_the_permalink($modelId);
            $slug = basename(parse_url($url, PHP_URL_PATH));

            $carData = [
                'name' => get_field('name', $modelId),
                'short_name' => get_field('short_name_list', $modelId),
                'imageDesktop' => $images,
                'url' => $url,
                'slug' => str_replace('-electric', '', $slug),
            ];

            $carsByType[$modelTypeKey][] = $carData;
        }

        foreach ($types as $typeKey => $typeName) {
            if (!empty($carsByType[$typeKey])) {
                $item['carTypes'][] = [
                    'name' => $typeName,
                    'cars' => $carsByType[$typeKey],
                ];
            }
        }

        $items[] = $item;
    }

    restore_current_blog();

    $response['testDrive'] = $items;

    return $response;
}

/**
 * Test drive single model page
 *
 * @param string $slug
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_test_drive_model(string $slug, int $blog_id): array
{
    $response = [];

    switch_to_blog(1);

    $page_slug = 'jazda-testowa-model-' . $slug;
    $args = [
        'name'           => $page_slug,
        'post_type'      => 'page',
        'post_status'    => 'any',
        'posts_per_page' => 1,
    ];
    $test_car = get_posts($args);

    $car_title = strtoupper(str_replace(['model-', '-electric'], ['', ''], $slug));
    $versions = new \WP_Query([
        'post_type'      => 'model',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'title'          => $car_title,
    ]);
    
    $galleryPictures = [];

    if (!empty($versions->posts)) {
        $children = get_children(['post_parent' => $versions->posts[0]->ID]);
        $posts = array_merge($versions->posts, $children);

        $blog_id_current = get_current_blog_id();
        $x = 0;

        foreach ($posts as $version) {
            if ($x >= 1) {
                break;
            }

            $gallery = get_field('gallery', $version->ID);

            if ($gallery) {
                $gallery = array_splice($gallery, 0, 5);
            }

            if (empty($gallery)) {
                continue;
            }

            foreach ($gallery as $itemId) {
                $img_id = $itemId;
                $img_url = wp_get_attachment_url($itemId);

                $images = [
                    volvo_global_prepare_image_for_render($blog_id_current, $img_id, 1024, 600, $img_url, true),
                ];

                $images = volvo_global_prepare_images_render($images);
                $galleryPictures[] = $images;
            }

            $x++;
        }
    }

    $content = '';
    $title = '';
    
    if (!empty($test_car)) {
        $title = $test_car[0]->post_title;
        $content = volvo_global_prepare_content_block($test_car[0]->post_content, 1);
    }

    $source = '';
    if (!empty($test_car)) {
        $source = get_field('source', $test_car[0]->ID);
        if (!$source || $source === '') {
            $source = $test_car[0]->post_name;
        }
    }

    restore_current_blog();

    // Get dealer options from current blog
    $partnerName = get_field('name', 'options-dealer');
    $thanksCode = get_field('code_test_drive', 'options-dealer');
    $thanksCode = str_replace('||time||', time(), $thanksCode);

    // Showrooms
    $showrooms = false;
    $showroomsBlog = volvo_global_get_showrooms_blog();
    if (volvo_global_is_multi_showroom_and_service($showroomsBlog)) {
        $showrooms = [];
        $showroomsIds = array_unique($showroomsBlog['showroomsAndSerices']);
        foreach ($showroomsIds as $id) {
            $showrooms[$id] = get_field('name', $id);
        }
    }

    $today = new \DateTime();

    $response['content_title'] = $title;
    $response['content'] = $content;
    $response['source'] = $source;
    $response['gallery'] = $galleryPictures;
    $response['thanksCode'] = $thanksCode ? $thanksCode : '';
    $response['destination'] = 'new-cars';
    $response['showrooms'] = $showrooms;
    $response['partnerName'] = $partnerName;
    $response['thankyouImage'] = get_template_directory_uri() . '/assets/public/formThanksImage.png';
    $response['todayDate'] = $today->format('Y-m-d');

    return $response;
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
function volvo_global_get_global_page_by_path(string $path, $type = 'page'): object|null
{
    switch_to_blog(1);

    $page = get_page_by_path(ltrim($path), OBJECT, $type);

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
function volvo_global_get_page_source(object $page, $is_ms_page_global = false): string
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

function volvo_global_is_has_any_service(array $showroomsBlog): bool
{
    return count(array_unique($showroomsBlog['services'])) > 0;
}

/**
 * Showroom form
 * 
 * @param array $showroomsBlog
 * @return array|bool
 */
function volvo_global_get_showrooms_form(array $showroomsBlog): array|bool
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
function volvo_global_get_model_page_by_category(string $page_name, object $category): object|null
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
						$img_url = wp_get_attachment_url($itemId);
						$images = [
                            volvo_global_prepare_image_for_render($blog_id, $img_id, 320, 180, $img_url, false),
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
 * @param int $model_id
 * @param bool $custom_price
 * @return string|null
 */
function volvo_global_get_model_price(int $model_id, bool $custom_price = false): string|null
{
    $price = null;

    $variations = new \WP_Query(
        array(
            'post_type'      => 'model',
            'posts_per_page' => 1,
            'post_parent'    => $model_id,
            'cache_results'  => true,
            'meta_key'       => 'price',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        )
    );

    if ($custom_price) {
        $price = get_field('menu_price', $model_id);
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
			'content'   => $post->post_content ? volvo_global_prepare_content_block( $post->post_content, $blog_id ) : '', // parse blocks
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

			$contentParsedown = $twoColumnContentComponent['description'];
			$contentParsedown = volvo_global_block_description_parsedown($contentParsedown);
			$contentParsedown = str_replace('<ul','<ul class="content__list list"',$contentParsedown);
			$contentParsedown = str_replace('<li','<li class="list__item"',$contentParsedown);
			
            $prepare_blocks_level = 0;
			$model['versions'][] = array(
				'name'                      => get_field( 'name', $version->ID ),
				'price'                     => get_field( 'price', $version->ID ),
				'hide_price'				=> get_field('hide_price', $version->ID),
				'description'               => volvo_global_block_description_parsedown(get_field( 'description', $version->ID )),
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
				'content'                   => ($version->post_content) ? volvo_global_prepare_content_block( $version->post_content, $blog_id ) : '', // prepare blocks to view
				'overrideContent'           => $version_override['versionOverrideBlocks'],
			);
        }
    }

    restore_current_blog();

    return $model;
}

function volvo_global_get_version_override(int $version_id, array $featuredCarsOptions, int $blog_id)
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
        $versionOverrideBlocks[] = ($override->post_content) ? volvo_global_prepare_content_block( $override->post_content, $blog_id ) : ''; // prepare blocks to view
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
 * @param array $gallery_ids
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_model_data_gallery(array $gallery_ids, int $blog_id): array
{
    $galleryPictures = [];
    $galleryThumbs = [];

    foreach ( $gallery_ids as $img_id ) {
        
        $img_url = wp_get_attachment_url($img_id);

        $images = [
            volvo_global_prepare_image_for_render($blog_id, $img_id, 1920, 1080, $img_url, false),
        ];
        $imagesThumbs = [
            volvo_global_prepare_image_for_render($blog_id, $img_id, 500, 200, $img_url, false),
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
 * @param string $url
 * @param ?int $blog_id
 * @return string
 */
function volvo_global_clear_url(string $url, ?int $blog_id = null): string
{
    $domain = $blog_id ? get_blogaddress_by_id($blog_id) : '';

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

function volvo_global_block_contact_person(int $person_id): array
{
    if (!$person_id || $person_id < 0) {
        return [];
    }

    return [
        'name'      => trim(get_field('name', $person_id) . ' ' . get_field('surname', $person_id)),
        'position'  => get_field('position', $person_id),
        'phone'     => get_field('phone', $person_id),
        'email'     => get_field('email', $person_id),
    ];
}

function volvo_global_block_link(?array $content_link)
{
    if (!is_null($content_link) && !empty($content_link) && is_array($content_link) && !empty($content_link)) {
        $result = volvo_global_build_link($content_link);

        if (strpos($result['url'], '---') !== false) {

            $rep = explode('---', $result['url']);

            $result['url'] = '/dostepne-na-miejscu/#' . $rep[1];
        }

        return $result;
    }

    return $content_link;
}

function volvo_global_block_description_parsedown(string $description): string
{
    $Parsedown = new \Parsedown();
    return $Parsedown->text($description);
}

function volvo_global_get_post_tags(int $post_id)
{
	$tags = wp_get_post_terms($post_id, 'post_tag');

	if ($tags && !is_wp_error($tags)) {
		return $tags;
	}

	return [];
}

function volvo_global_prepare_content_block(string $content, int $blog_id)
{
    return volvo_global_block_prepare_to_view(volvo_global_blocks_prepare_all( $content, $blog_id ), $blog_id);
}

function volvo_global_get_blog_pages(int $blog_id)
{
    $page_limit = get_field('limit');
    $tags = get_field('tags');
    $current_page = max( 1, (int) get_query_var( 'paged' ) );
    
    $args = [
        'post_type' => 'blog',
        'posts_per_page' => $page_limit,
        'paged' => $current_page,
        'tag__in' => $tags
    ];

    $posts_query = new \WP_Query($args);
    $posts_array = [];
    $total_pages = 0;

    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()) {
            $posts_query->the_post();

            $imagesDesktop = [];

            $img_id = get_field('blog_image', get_the_ID());
            if (!$img_id) {
                $img_id = get_post_thumbnail_id(get_the_ID());
            }
            
            if ($img_id) {
                $imagesDesktop[] = array(
                    'image' => volvo_global_prepare_image($img_id),
                );
            }
            $post_data = [
                'heading'       => get_the_title(),
                'image'         => $imagesDesktop,
                'blog_desc'     => trim(get_field('blog_desc', get_the_ID())),
                'link'          => ['url' => get_permalink()],
                'date'          => get_the_date('d.m.Y'),
                'description'   => get_the_excerpt(),
                'ctaText'       => __('Read', 'partners-site_v2'), //'PRZECZYTAJ'
            ];

            if (empty($post_data['description'])) {
                $post_data['description'] = wp_trim_words(get_the_content(), 30, '...');
            }

            $posts_array[] = $post_data;
        }

        $total_pages = $posts_query->max_num_pages;

        wp_reset_postdata();
    }

    return [
        'posts'         => $posts_array,
        'pagination'    => [
            'currentPage'   => $args['paged'],
            'maxPages'      => $total_pages
        ],
    ];
}

function volvo_global_set_paged(array $page_parts = [])
{
    if (isset($_GET['page']) && ($paged = (int) $_GET['page']) > 1 ) {
        set_query_var('paged', $paged);
    } elseif (($paged = (int) $page_parts[2]) > 1) {
        set_query_var('paged', $paged);
    }
}

function volvo_global_get_acf_fields(int $post_id)
{
    $acf = function_exists('get_fields') ? get_fields($post_id) : array();
    
    if ($_GET['acf-test']) {
        echo '<pre>' . PHP_EOL;
        print_r($acf);
        echo PHP_EOL . '</pre>' . PHP_EOL;
        exit;
    }
    return $acf;

}

/**
 * Page service
 */
function volvo_global_get_service_page_index(int $blog_id)
{
    $dealersData = volvo_global_get_service_dealers_data($blog_id);

    $options = volvo_global_get_basic_options( 0 );

    $news = volvo_global_get_service_news($options, $blog_id);

    $globalCampaign = false;
    $special_service = false;
    
    if ( 1 == $blog_id ) {
        $globalCampaign = $dealersData;
        $special_service = true;
    }

    return array(
        'global_form'       => $globalCampaign,
        'global_service'    => $special_service,
        'newsBox'           => array_reverse( $news ),
        'result'            => null,
        'year'              => null,
        'vin'               => null,
        'towColumnsList'    => volvo_global_get_service_two_columns_list(),
        'formService'       => volvo_global_get_service_form( $blog_id, 'vinomat' ),
        'accordionSection'  => volvo_global_get_service_accordion_section($blog_id),
        'contactSection'    => volvo_global_get_service_get_contact_section(),
    );
}

/**
 * Page service - Check VIN
 * 
 * @param WP_REST_Request $request
 * @param int $blog_id
 * @return array
 */
function volvo_global_post_service_check_vin($request, int $blog_id): array
{
    $vin = trim($request->get_param('vinomat-search'));

    $vin = strtoupper($vin);
    
    $date_year = volvo_global_get_vin_date_year($vin);

    $options = volvo_global_get_basic_options( 0 );

    if ($date_year == -1) {
        $date_year = 0;
    }

    $result = ($date_year) ? volvo_global_get_service_box_years_info($options, $date_year) : [];

    return array(
        'result'           => $result,
        'year'             => $date_year,
        'vin'              => ( $vin ? $vin : null ),
    );
}

function volvo_global_get_vin_date_year(string $vin): ?int
{
    global $volvo_global_years;

    $vin = strtoupper($vin);
    if (empty($vin) || !preg_match('/^[A-Z0-9]+$/', $vin)) {
        return null;
    }

    $client = new \GuzzleHttp\Client();
    $check_db = new VGA\Classes\CarSpecificationDataImporter($client);
    
    $vin_data = $check_db->getVinomatDol($vin);
    
    $date_year = null;

    if (!is_array($vin_data)) {
        $registerDate = new \DateTime($vin_data);
        
        $today     = new \DateTime();
        $interval  = $today->diff($registerDate);
        $date_year = $interval->format('%y');
    }

    if ($vin_data && is_array($vin_data) && array_key_exists('productionYear', $vin_data)) {
        $date_year = (int) date('Y') - $vin_data['productionYear'];             
    }
    
    $y = $vin[9];
    if (!$date_year && isset($y) && array_key_exists($y, $volvo_global_years)) {
        $date_year = (int) date('Y') - (int) $volvo_global_years[$y];
    }

    return $date_year;
}

function volvo_global_get_service_dealers_data(int $blog_id)
{
    $dealersData = [];
    
    if ( 1 == $blog_id ) {
        $dealers = volvo_global_get_service_blog_ids($blog_id);
        
        foreach ($dealers as $d) {
            foreach($d['address'] as $a) {
                if (!empty($a)) {
                    $dealersData[] = [
                        'key' => $d['dealerId'],
                        'value' => $a,
                        'sorter' => str_replace(['Ć', 'Ł', 'Ś', 'Ź', 'Ż'], ['C', 'L', 'S', 'Z', 'Z'], $a)
                    ];
                }
            }
        }

        $arr2 = volvo_global_array_msort($dealersData, array('sorter' => SORT_ASC));
        
        $dealersData = $arr2;
        array_pop($dealersData);
    }

    return $dealersData;
}

function volvo_global_get_service_news(array $options, int $blog_id): array
{
    $news = [];

    $admin_news = $options['vinomat_news'][0];

    switch_to_blog( 1 );
    
    for ( $i = 0; $i < (int) $admin_news; $i++ ) {
        $url = ($options[ 'vinomat_news_' . $i . '_vinomat_box_link' ][0] ? unserialize($options[ 'vinomat_news_' . $i . '_vinomat_box_link' ][0]) : null);
        $link = $options['options-service_vinomat-section_vinomat_news_' . $i . '_slides_0_type'][0];

        if (!$url) {
            $url_type = ($link && in_array($link, ['global', 'link'], true)) ? $link : null;
            
            if ($url_type) {
                $url_id = $options['options-service_vinomat-section_vinomat_news_' . $i . '_slides_0_' . $url_type . '-campaign'][0];
                $url = get_permalink($url_id);
            }
        }
        
        if (is_array($url) && isset($url['url'])) {
            $url['url'] = volvo_global_clear_url($url['url']);
        } elseif (!is_null($url) && is_string($url)) {
            $url = volvo_global_clear_url($url);
        }
        
        $news[ $i ] = array(
            'title' => $options[ 'vinomat_news_' . $i . '_vinomat_news_title' ][0],
            'desc'  => trim($options[ 'vinomat_news_' . $i . '_vinomat_news_desc' ][0]),
            'image' => wp_get_attachment_image_src( $options[ 'vinomat_news_' . $i . '_vinomat_news_image' ][0] )[0],
            'link'  => $url,
        );
    }

    $news = array_reverse($news);

    foreach ($news as $key => $value) {
        if (!empty($news[$key]['link']) && is_array($news[$key]['link']) && isset($news[$key]['link']['url'])) {
            $news[$key]['link']['url'] = volvo_global_clear_url($news[$key]['link']['url'], $blog_id);
        }
    }
    
    restore_current_blog();

    // update dealers news
    $dealer_news = get_field( 'vinomat-section', 'options-service' );

    $x = 0;
    foreach ( $dealer_news['vinomat_news'] as $n ) {
        $news[$x] = array(
            'title' => $n['vinomat_news_title'],
            'desc'  => $n['vinomat_news_desc'],
            'image' => wp_get_attachment_image_src( $n['vinomat_news_image'] )[0],
            'link'  => $n['vinomat_box_link'],
        );
        $x++;
    }

    return $news;
}

function volvo_global_get_service_vinomat_box_templates(array $options): array
{
    $templates_count = $options['vinomat_box_templates'][0];
    
    $templates = [];
    
    for ( $i = 0; $i < $templates_count; $i++ ) {
        $info = array(
            'title' => $options[ 'vinomat_box_templates_' . $i . '_vinomat_box_title_template' ][0],
            'desc'  => trim($options[ 'vinomat_box_templates_' . $i . '_vinomat_desc_template' ][0]),
        );
        
        $templates[$options[ 'vinomat_box_templates_' . $i . '_vinomat_box_title_template' ][0]][] = $info;
    }

    return $templates;
}

function volvo_global_get_service_box_years_info(array $options, ?int $date_year): array
{
    $box_years = $options['vinomat_box'][0];
    
    $box_years_info = array();
    
    $year_plural = _n_noop(
        'year',
        'years',
        'partners-site_v2'
    );

    $year_label_main = translate_nooped_plural(
        $year_plural,
        2,
        'partners-site_v2'
    ); // lata

    $year_label = translate_nooped_plural(
        $year_plural,
        $date_year,
        'partners-site_v2'
    );
    /*
    $year_label = $year_label_main = _x('years', 'no more than 5', 'partners-site_v2'); // lata
    if ((int) $date_year > 5) {
        $year_label = _x('years', 'more than 5', 'partners-site_v2'); // lat
    }
    */

    $templates = volvo_global_get_service_vinomat_box_templates($options);

    for ( $i = 0; $i < $box_years; $i++ ) {
        
        $t = unserialize( $options[ 'vinomat_box_' . $i . '_vinomat_box_years' ][0] );
        
        foreach($templates as $key => $value) {
            foreach ( $t as $a ) {
                if ((int)$a <= (int) $date_year && $key == $options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0] ) {
                    
                    $label = $templates[$options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0]][0]['title'];
                
                    $desc = trim($templates[$options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0]][0]['desc']);
                    
                    $desc =  str_replace(
                        [
                            '||Opis1||',
                            '||Desc1||'
                        ],
                        '<b>'.$options[ 'vinomat_box_' . $i . '_vinomat_desc_desc_1' ][0].'</b>',
                        $desc
                    );
                    $desc =  str_replace(
                        [
                            '||Rok||',
                            '||Year||'
                        ],
                        $date_year,
                        $desc
                    );
                    
                    $desc = str_replace($year_label_main, $year_label, $desc);
                    
                    $desc = str_replace(
                        [
                            '||Opis||',
                            '||Desc||'
                        ],
                        '<b>'.$options[ 'vinomat_box_' . $i . '_vinomat_desc_desc' ][0].'</b>',
                        $desc
                    );

                    $url = unserialize($options[ 'vinomat_box_' . $i . '_vinomat_box_link' ][0]);
                    //$url['url'] = str_replace('https://main.volvocars-partner.pl', '' ,$url['url']);
                
                    $info = array(
                        'title' => $options[ 'vinomat_box_' . $i . '_vinomat_box_title' ][0],
                        'desc'  => $desc,
                        'icon'  => $options[ 'vinomat_box_' . $i . '_vinomat_box_image' ][0],
                        'link' => volvo_global_clear_url($url['url']),
                    );
                    
                    $box_years_info[ $label ] = $info;
                }
            }
        }
    }

    return $box_years_info;
}

function volvo_global_get_service_hero_slider(int $blog_id): array
{
    $heroSlider = array(
        'slides' => array(),
    );

    $slides = volvo_global_get_service_dealer_slides( $blog_id );
    $slides = volvo_global_add_service_global_slides( $slides );

    foreach ( $slides as $slide ) {
        if ( $slide->site_ID !== $blog_id ) {
            switch_to_blog( $slide->site_ID );
        }

        $title   = get_field( 'title', $slide->ID );
        $img_id = get_field( 'image', $slide->ID );

        $img_url = wp_get_attachment_url($img_id);

        $images = [
            volvo_global_prepare_image_for_render($blog_id, $img_id, 3840, 1614, $img_url, true),
        ];
        $image = volvo_global_prepare_images_render($images);

        $images_thumb = [
            volvo_global_prepare_image_for_render($blog_id, $img_id, 56, 104, $img_url, true),
        ];
        $image_thumb = volvo_global_prepare_images_render($images_thumb);

        $linkField = get_field( 'link', $slide->ID );

        if ( ! $linkField || ! array_filter( $linkField ) ) {
            $linkField = array(
                'url' => get_the_permalink( $slide->ID ),
            );
        }

        if ( ! $linkField['title'] ) {
            $linkField['title'] = __('Learn more', 'partners-site_v2');
        }

        $heroSlider['slides'][] = array(
            'title'     => $title,
            'subtitle'  => get_field( 'subtitle', $slide->ID ),
            'link'      => volvo_global_build_link( $linkField ),
            'image'     => $image,
            'thumbnail' => $image_thumb,
        );

        if ( $slide->site_ID !== $blog_id ) {
            restore_current_blog();
        }
    }

    return $heroSlider;
}

function volvo_global_get_service_dealer_slides(int $blog_id): array
{
    $slides = array();

    $sliderOptions = get_field( 'service-slider', 'options-service' );

    if ( ! empty( $sliderOptions ) ) {
        foreach ( $sliderOptions['slides'] as $item ) {
            $slidePost = false;
            if ( $item['type'] === 'local' && $item['local-campaign'] ) {
                $slidePost          = get_post( $item['local-campaign'] );
                if ($slidePost) {
                    $slidePost->site_ID = $blog_id;
                }
            } elseif ( $item['type'] === 'global' && $item['global-campaign'] ) {
                switch_to_blog( 1 );
                $slidePost          = get_post( $item['global-campaign'] );
                $slidePost->site_ID = 1;
                restore_current_blog();
            }
            if ( $slidePost && $slidePost->post_status === 'publish' && count( $slides ) < 3 ) {
                $slides[] = $slidePost;
            }
        }
    }

    return $slides;
}

function volvo_global_add_service_global_slides( array $slides ): array {
    switch_to_blog( 1 );

    $sliderOptions = get_field( 'global-service-slider', 'options-service' );

    if ( ! empty( $sliderOptions ) ) {
        foreach ( $sliderOptions['slides'] as $item ) {
            $slidePost = false;
            if ( $item['campaign'] ) {
                $slidePost          = get_post( $item['campaign'] );
                $slidePost->site_ID = 1;
            }
            if ( $slidePost && $slidePost->post_status === 'publish' && count( $slides ) < 3 ) {
                $slides[] = $slidePost;
            }
        }
    }

    if ( count( $slides ) < 3 ) {
        $existing_ids = wp_list_pluck($slides, 'ID');
        
        $latest_query = volvo_global_get_latest_campaign(3 - count($slides), $existing_ids);
        
        foreach ($latest_query->posts as $post) {
            $post->site_ID = get_current_blog_id();
            $slides[] = $post;
        }
    }

    restore_current_blog();

    return $slides;
}

function volvo_global_get_service_two_columns_list(): array
{
    switch_to_blog( 1 );
    $advantagesListOptions = get_field( 'advantages-list', 'options-service' );
    restore_current_blog();

    if ( is_array( $advantagesListOptions['list1'] ) ) {
        $advantagesListOptions['list1'] = volvo_global_sort_list( $advantagesListOptions['list1'] );
    }
    if ( is_array( $advantagesListOptions['list2'] ) ) {
        $advantagesListOptions['list2'] = volvo_global_sort_list( $advantagesListOptions['list2'] );
    }

    return array(
        'heading'     => $advantagesListOptions['heading'],
        'description' => $advantagesListOptions['description'],
        'moreText'    => $advantagesListOptions['more-text'],
        'list1'       => $advantagesListOptions['list1'] ?? [],
        'list2'       => $advantagesListOptions['list2'] ?? [],
    );
}

function volvo_global_get_service_form( int $blog_id, $type = null ): array
{
    $showroomsBlog = volvo_global_get_showrooms_blog();

    if ( (1 !== $blog_id) && !volvo_global_is_has_any_service($showroomsBlog)) {
        return array();
    }

    switch_to_blog( 1 );
    
    $globalFormOptions = get_field( 'form', 'options-global' );
    $img_id = $globalFormOptions['thank-you-image'];

    $img_url = wp_get_attachment_url($img_id);

    $images = [
        volvo_global_prepare_image_for_render($blog_id, $img_id, 392, false, $img_url, true),
    ];
    $image = volvo_global_prepare_images_render($images);


    $formOptions = get_field( 'form', 'options-service' );
    $models = array();

    if ( is_array( $formOptions['models'] ) ) {
        foreach ( $formOptions['models'] as $model ) {
            $models[] = $model['name'];
        }
    }

    restore_current_blog();

    switch_to_blog( $blog_id );

    $thanksRequest = get_field('service_additional_section', 'options-service');
    $thanksData = $thanksRequest['field_thankyou_code'];
    
    $partnerName = get_field( 'name', 'options-dealer' );

    $heading_more = '';

    if ( $type ) {
        $formOptions['heading'] = __('Aby skorzystać z innych usług,', 'partners-site_v2');
        $heading_more           = __('umów się w Autoryzowanym Serwisie Volvo', 'partners-site_v2');
    }
    $formService = array(
        'destination'   => 'service',
        'heading'       => $formOptions['heading'] ?? '',
        'heading_more'  => $heading_more,
        'models'        => $models,
        'categories'    => $formOptions['services'] ?? [],
        'services'      => $formOptions['services'] ?? [],
        'partnerName'   => $partnerName,
        'thankyouImage' => $image,
        'thankYouCode'  => $thanksData
    );

    if ( volvo_global_is_multi_showroom_and_service($showroomsBlog) ) {
        $showrooms    = array();
        foreach ( $showroomsBlog['services'] as $id ) {
            $showrooms[ $id ] = get_field( 'name', $id );
        }
        $formService['showrooms'] = $showrooms;
    }

    restore_current_blog();

    return $formService;
}

function volvo_global_array_msort(array $array, array $cols): array
{
    setlocale(LC_COLLATE,'pl_PL.UTF-8');
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) {
            $colarr[$col]['_'.$k] = strtolower($row[$col]);
        }
    }

    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k]))
                $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;

}

function volvo_global_sort_list( array $list ): array
{
    foreach ( $list as $key => $value ) {
        $list[ $key ] = $value['text'];
    }
    return $list;
}

function volvo_global_get_multi_salon()
{
    return ['PL041','PL050'];
}

function volvo_global_get_service_blog_ids(int $blog_id, $domain_only = false)
{
    $mSalon = volvo_global_get_multi_salon();
    $exclude_blogs = [3,38];

    $blog_ids = [];
    $blogs = wp_get_sites();

    foreach ($blogs as $b) {
        if (in_array($b['blog_id'], $exclude_blogs)) {
            continue;
        }
        
        $dealerId = null;
        $showrooms = [];
        $multisalon = false;

        switch_to_blog($b['blog_id']);

        $options = get_fields('options-dealer');
        
        if (is_array($options)) {
            $dealerId = $options['dealerId'];   
            $dealerName = $options['name'];				
        }
        
        if (
            strpos($dealerName,'Test') === false &&
            strpos($dealerName,'Euroservice Volvo Warszawa') === false &&
            $b['domain'] !== 'autobruno-gorzow.volvocars-partner.pl'
        ) {
            $addresses = [];
            $cars = get_posts([
                'post_type' => 'stock-car',
                'fields' => 'ids',
                'post_status' => 'publish',
                'posts_per_page' => -1
            ]);
            $cars_data = [];		           
            // foreach($cars as $key=>$value) {
            //  	$cars_data[] = ['vin' => get_field('vin',$value),'slug' => basename(get_permalink($value))];
            // }
            $count_pages = count($cars);
    
            if ($domain_only) {
                array_push($blog_ids, $b['domain']);
            } else {
                $data = [];
                $showroom = get_posts([
                    'post_type' => 'showroom'
                ]);

                if ($showroom) {
                    foreach($showroom as $r) {
                        $id = explode('#',get_field('showroomId',$r->ID));
                        $salon_data = get_field('address',$r->ID);
                        $n = (get_field('name', $r->ID) == $salon_data["city"] ? $salon_data["city"].', '.$dealerName.', ' : $salon_data["city"].', '.$dealerName.', ');
                        $name = $n. ' '.$salon_data["street"];
                        
                        array_push($addresses,$name);

                        if ($id && is_array($id) && !empty($id)) {
                            $id = $id[0];
                        }
                        
                        if (!in_array($id, $showrooms)) {
                            array_push($showrooms,$id);
                            array_push($showrooms,'6'.$id);
                        }
                    }
                }

                $dealerId = str_replace('#1','',$dealerId);
                
                if (in_array($dealerId,$mSalon)) {
                    $multisalon = [15,16];
                } else {
                    $multisalon = false;
                }

                if (count($showroom) > 3) {
                    $multisalon = true;
                }

                if ($b['blog_id'] == 1) {
                    $dealerId = 1;
                }

                $data = [
                    'blog_id' => $b['blog_id'],
                    'address' => $addresses,
                    'multisalon' => $multisalon,
                    'cars' => $count_pages,
                    'domain' => $b['domain'],
                    'dealerId' => $dealerId,
                    'showrooms' => $showrooms,
                    'car_ids' => $cars,
                    'cars_data' => $cars_data
                ];
            
                if ($dealerId) {
                    array_push($blog_ids, $data);
                }
            }
        }

        restore_current_blog();
    }
    
    return $blog_ids;
}

function volvo_global_get_service_accordion_section( int $blog_id ): array
{
    $accordion = array();

    switch_to_blog( 1 );
    
    $accordionSectionOptions = get_field( 'services-section', 'options-service' );
    $multisiteUrl = get_home_url();

    restore_current_blog();
    
    $localAccordionSectionOptions = get_field( 'services-section', 'options-service' );
    
    if ( is_array( $accordionSectionOptions['services'] ) ) {
        $accordion = array_merge( $accordion, $accordionSectionOptions['services'] );
    }

    if ( 1 !== $blog_id ) {
        if ( is_array( $localAccordionSectionOptions['services'] ) ) {
            $accordion = array_merge( $accordion, $localAccordionSectionOptions['services'] );
        }
    }

    foreach ( $accordion as &$accordionItem ) {
        $accordionItem['description'] = str_replace( $multisiteUrl, get_home_url(), $accordionItem['description'] );
    }
    unset( $accordionItem );

    return array(
        'heading'   => array(
            'black' => $accordionSectionOptions['heading'],
        ),
        'accordion' => $accordion,
    );
}

function volvo_global_get_service_get_contact_section(): array
{
    switch_to_blog( 1 );

    $contactSectionOptions = get_field( 'contact-section', 'options-service' );

    restore_current_blog();
    
    $localContactSectionOptions = get_field( 'contact-section', 'options-service' );
    $contacs                    = array();

    foreach ( $localContactSectionOptions['employees'] as $contact ) {
        $employee  = get_fields( $contact['employee'] );
        $contacs[] = array(
            'specializations' => $contact['specializations'],
            'employee'        => $employee,
        );
    }

    return array(
        'heading'  => $contactSectionOptions['heading'],
        'contacts' => $contacs,
    );
}

/**
 * Campaign page
 */
function volvo_global_get_campaing_page(\WP_Post $post, int $blog_id): array
{
    global $is_volvo_ms_global_page;

    $switch = 1;
    switch_to_blog( 1 );
    
    $check_global_campain = new \WP_Query(
        array(
            'post_type'      => 'campaign',
            'posts_per_page' => -1,
            'name' => $post->post_name,
            'post_status'    => array( 'publish' )
        )
    );
    
    if (empty($check_global_campain->posts)) {
        $switch++;
		switch_to_blog( $blog_id );
	}

    $content          = volvo_global_prepare_content_block( $post->post_content, get_current_blog_id() );
	$legalInfoContent = get_field( 'legal-info-content', $post->ID );

    if (1 === get_current_blog_id() && 1 !== $blog_id) {
        $switch++;
        switch_to_blog( $blog_id );
    }

    if ( ! $query = wp_cache_get('post-'.$post->ID) ) { 
        $check_campaign = new \WP_Query(
            array(
                'post_type'      => 'campaign',
                'posts_per_page' => -1,
                'name' => $post->post_name,
                'post_status'    => array( 'publish' )
            )
        );
    }
    else {
        $check_campaign = wp_cache_get('post-'.$post->ID);
    }

    if (!empty($check_campaign->posts)) {
        foreach ( $check_campaign->posts as $local_camp ) { 
            $content = volvo_global_prepare_content_block( $local_camp->post_content, $blog_id );
        }
    }

    $campaignOverride = new \WP_Query(
        array(
            'post_type'      => 'campaign-override',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'campaign',
                    'value'   => $post->ID,
                    'compare' => '=',
                ),
            ),
        )
    );

    $additionalContent = [];
    foreach ( $campaignOverride->posts as $override ) {
        $additionalContent[] = volvo_global_prepare_content_block( $override->post_content, $blog_id );
    }
    $additionalContent = array_filter($additionalContent);

    $landing_page = get_field('one_page');
    $side_form = (get_field('side_form') == 'on' ? get_field('side_form') : false);

    for ($switch; $switch > 0; $switch--) {
        restore_current_blog();
    }

    if ($is_volvo_ms_global_page) {
        switch_to_blog( 1 );

        $post_id = $post->ID;
        $post_title = get_the_title($post->ID);

        restore_current_blog();
    } else {
        $post_id = $post->ID;
        $post_title = get_the_title($post->ID);
    }
    
    restore_current_blog();
    
    return [
        'id' => $post_id,
        'title' => $post_title,
        'sideform' => $side_form,
        'onepage' => $landing_page,
        'content'           => $content,
        'legalInfoContent'  => $legalInfoContent,
        'additionalContent' => $additionalContent,
    ];
}