<?php

require_once WPMU_PLUGIN_DIR . '/volvo-global-api/classes/CacheTagsCollector.class.php';
require_once WPMU_PLUGIN_DIR . '/volvo-global-api/classes/Electrification.class.php';


/**
 * Prepare block to view
 * 
 * @param array $blocks
 * @param int $post_id
 * @param int $blog_id
 * @return array
 */
function volvo_global_block_prepare_to_view(array $blocks, int $post_id, int $blog_id): array
{
    foreach($blocks as $key_block => $block) {
        switch ($block['block_name']) {
            case 'acf/anchor':
            case 'acf/banner-with-content-overlay':
            case 'acf/blog-posts-component':
            case 'acf/blog-post-footer':
            case 'acf/cost-map':
            case 'acf/electrification-map':
            case 'acf/gallery':
            case 'acf/hero-image':
            case 'acf/html-code':
            case 'acf/offer-box':
            case 'acf/offer-boxes':
            case "acf/offer-cards":
            case 'acf/preview-component':
            case 'acf/quick-info':
            case 'acf/short-notes':
            case 'acf/site-heading';
            case 'acf/table-component':
            case 'acf/text-editor':
            case 'acf/text-editor-extended':
            case 'acf/three-boxes':
            case 'acf/two-column-content-component':
            case "acf/two-image":
                $result = volvo_global_get_block_data($block, $post_id, $blog_id);

                $blocks[$key_block] = $result;
                break;
            default:
                var_dump($block['block_name']);exit;
        }
    }

    return $blocks;
}

/**
 * Prepare all blocks
 * 
 * @param string|array $post_content_or_blocks
 * @param int $post_id
 * @param int $blog_id
 * @return array
 */
function volvo_global_blocks_prepare_all( array|string $post_content_or_blocks, int $post_id, int $blog_id ): array
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
                volvo_global_blocks_maps( $raw_data, $fields_def, $block_name, $post_id, $blog_id )
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
                $block_data['inner_blocks'] = volvo_global_blocks_prepare_all( $block['innerBlocks'], $post_id, $blog_id );
            }
        }

        $parsed_content[] = [
            'block_name' => $block_name,
            'data'       => $block_data,
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
                    if ( 'block' === $rule['param'] && '==' === $rule['operator'] && ($rule['value'] === $block_name || 'all' === $rule['value']) ) {
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

/**
 * Maps data block
 * 
 * @param array $raw_data
 * @param array $fields_definition
 * @param int $post_id
 * @param int $blog_id
 * @param string $prefix
 * @return array
 */
function volvo_global_blocks_maps( array $raw_data, array $fields_definition, string $block_name, int $post_id, int $blog_id, string $prefix = '' ): array
{
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
                        
                        // Rekurencja
                        $parsed_layout_fields = volvo_global_blocks_maps( $raw_data, $active_layout['sub_fields'], $block_name, $post_id, $blog_id, $row_prefix );

                        $parsed[ $field_name ][] = array_merge(
                            [ 'acf_fc_layout' => $layout_name ],
                            $parsed_layout_fields
                        );
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
                $parsed[ $field_name ][ $i ] = volvo_global_blocks_maps( $raw_data, $field['sub_fields'], $block_name, $post_id, $blog_id, $row_prefix );
            }
        }
        // GROUP
        elseif ( 'group' === $field['type'] && ! empty( $field['sub_fields'] ) ) {
            $parsed[ $field_name ] = volvo_global_blocks_maps( $raw_data, $field['sub_fields'], $block_name, $post_id, $blog_id, $current_key );
        }
        // TABLE
        elseif ( 'table' === $field['type'] ) {
            $parsed[ $field_name ] = [
                'use_header' => isset( $raw_data[ $current_key ]['p']['o']['uh'] ) ? (int) $raw_data[ $current_key ]['p']['o']['uh'] : 0,
                'header'     => $raw_data[ $current_key ]['h'] ?? [],
                'caption'    => $raw_data[ $current_key ]['p']['ca'] ?? '',
                'body'       => $raw_data[ $current_key ]['b'] ?? []
            ];
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

                $parsed[ $field_name ] = is_string($value) ? trim($value) : $value;
            } else {
                $parsed[ $field_name ] = $field['default_value'] ?? null;
            }
        }
    }
    
    return $parsed;
}

// BLOCKS

/**
 * Block data
 * 
 * @param array $block
 * @param int $post_id
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_data (array $block, int $post_id, int $blog_id): array
{
    static $cachaParameters = [];
    $block_name = $block['block_name'];
    $volvo_function = 'volvo_global_get_block_' . str_replace(['/','-'], '_', $block_name);
    if (!function_exists($volvo_function)) {
        echo('Missing function: ' . $volvo_function);exit;
    }

    if (!array_key_exists($volvo_function, $cachaParameters)) {
        $function_reflection = new ReflectionFunction($volvo_function);
        $functionParameters = $function_reflection->getParameters();

        $cachaParameters[$volvo_function] = $functionParameters;
    }

    $args = [];
    foreach($cachaParameters[$volvo_function] as $functionParam) {
        $args[] = ${$functionParam->name};
    }
    
    return call_user_func_array($volvo_function, $args);
}

/**
 * Block margins
 * 
 * @param array $block_data
 * @return array
 */
function volvo_global_get_block_acf_prepare_margin(array $block_data): ?array
{
    if (!array_key_exists('margin-top', $block_data) && !array_key_exists('margin-top', $block_data)) {
        return null;
    }

    return [
        'top' => $block_data['margin-top'],
        'bottom' => $block_data['margin-bottom'],
    ];
}

/**
 * Block anchor
 * 
 * @param array $blocks
 * @return array
 */
function volvo_global_get_block_acf_anchor (array $block): array
{
    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'id'         => $block['data']['anchor'],
            'margin'     => volvo_global_get_block_acf_prepare_margin($block['data']),
        ],
    ];

    return $result;
}

/**
 * Block banner with content overlay
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_banner_with_content_overlay (array $block): array
{
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

    $img_id = $block['data']['img'];
    
    $images = volvo_global_prepare_image($img_id);

    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'img'            => $images,
            'heading'        => $block['data']['heading'],
            'content'        => $block['data']['description'],
            'format'         => $block['data']['format_banner'],
            'button'         => $button,
            'margin'         => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block blog posts
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_blog_posts_component (array $block, int $blog_id): array
{
    $page_limit = $block['data']['limit'];
    $tags = $block['data']['tags'];

    $args = [
        'post_type' => 'blog',
        'posts_per_page' => $page_limit,
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        'tag__in' => $tags
    ];

    $posts_query = new WP_Query($args);
    $posts_array = [];
    $total_pages = 0;
    
    if (!empty($posts_query->posts)) {
        foreach ($posts_query->posts as $post) {
            $imagesDesktop = [];
            
            $img_id = get_field('blog_image', $post->ID);
            if (!$img_id) {
                $img_id = get_post_thumbnail_id($post->ID);
            }
            
            if ($img_id) {
                $imagesDesktop = volvo_global_prepare_image($img_id);
            }
            $post_data = [
                'heading'       => get_the_title($post->ID),
                'image'         => $imagesDesktop,
                'blog_desc'     => get_field('blog_desc', $post->ID),
                'link'          => ['url' => get_permalink($post->ID)],
                'date'          => get_the_date('d.m.Y', $post->ID),
                //'description'   => get_the_excerpt(),
                //'ctaText'       => strtoupper(__('Read', 'partners-site_v2'))
            ];

            //if (empty($post_data['description'])) {
            //    $post_data['description'] = wp_trim_words(get_the_content(), 30, '...');
            //}

            $posts_array[] = $post_data;

            \VGA\Classes\Cache_Tags_Collector::instance()->add("site:{$blog_id}:page:{$post->ID}");
        }

        $total_pages = $posts_query->max_num_pages;
        $current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    }

    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'title'             => $block['data']['title_1'],
            'subtitle'          => $block['data']['title_2'],
            'posts'             => $posts_array,
            'pagination' => [
                'currentPage'   => $current_page,
                'maxPages'      => $total_pages
            ],
            'margin'            => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block blog post footer
 * 
 * @param array $block
 * @param int $post_id
 * @return array
 */
function volvo_global_get_block_acf_blog_post_footer (array $block, int $post_id): array
{
    $author_data = [];
    
    $post_author_id = get_post_field('post_author', $post_id);
    
    if ($post_author_id) {
        $post_author = get_userdata($post_author_id);

        if ($post_author) {
            $author_data['name'] = $post_author->display_name;
            $author_data['email'] = $post_author->user_email;
            $author_data['phone'] = get_user_meta($post_author_id, 'phone_number', true);
            $author_data['position'] = get_user_meta($post_author_id, 'user_bio', true);
        }
    }

    $imageId = $block['data']['img'];
    
    $image = volvo_global_prepare_image($imageId);

    $content = $block['data']['content'];

    if ( $content && array_filter( $content ) ) {
        foreach ( $content as &$contentItem ) {
            if (array_key_exists('contact-info', $contentItem)) {
                $contentItem['contactPerson'] = volvo_global_block_contact_person($contentItem['contact-info']);
            }
            
            if (array_key_exists('link', $contentItem)) {
                $contentItem['link'] = volvo_global_block_link($contentItem['link']);
            }
        }
    }

    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'image'          => $image,
            'heading'        => $block['data']['heading'],
            'content'        => $content,
            'author'         => $author_data,
            'margin'         => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block cost map
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_cost_map (array $block): array
{
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

    $electrification = new \VGA\Classes\Electrification();

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
            'margin'          => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block electrification map
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_electrification_map (array $block): array
{
    $opt       = getBasicOptions(0);
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
            'margin'            => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block gallery
 * 
 * @param array $block
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_acf_gallery (array $block, int $blog_id): array
{
    $gallery      = $block['data']['gallery'];
    $galleryItems = array();

    foreach ( $gallery as $itemId ) {
        $img_id = $itemId;
        $img_url = wp_get_attachment_url($itemId);

        $images = [
            volvo_global_prepare_image_for_render($blog_id, $img_id, 1920, 1080, $img_url, 'crop', 300, 200, false),
        ];

        $images = volvo_global_prepare_images_render($images);

        $galleryItems[] = array(
            'mobileImage'  => $images,
            'desktopImage' => $images,
            'full'         => '',
            'domain'       => get_bloginfo('url'),
        );
    }
    
    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'images'    => $galleryItems,
            'margin'     => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block hero image
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_hero_image (array $block): array
{
    $bigDescription   = $block['data']['bigDescription'];
    $smallDescription = $block['data']['smallDescription'];
    $darkOverlay      = $block['data']['darkOverlay'];

    $crop_image = $block['data']['field_crop'];
    $crop_image = ($crop_image ? $crop_image : 'crop-center');
    
    $images = volvo_global_prepare_image( $block['data']['img'] );
    
    $images['crop_image'] = $crop_image;
    
    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'image'             => $images,
            'bigDescription'    => $bigDescription,
            'smallDescription'  => $smallDescription,
            'darkOverlay'       => $darkOverlay,
            'margin'            => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}

/**
 * Block html code
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_html_code (array $block): array
{
    $htmlCode = strip_tags($block['data']['html_code_render'],'<script><iframe><img>');

    $result = [
        'block_name'    => $block['block_name'],
        'data' => [
            'html'    => $htmlCode,
            'margin'  => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}

/**
 * Block offer box
 * 
 * @param array $block
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_acf_offer_box (array $block, int $blog_id): array
{
    $items = [];

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

                $images = [
                    volvo_global_prepare_image_for_render($blog_id, $img_id, 288, 162, $itemId, false),
                ];
                $getImage = volvo_global_prepare_images_render($images);
            }
            
            $items[] = [
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
        'block_name'        => $block['block_name'],
        'data' => [
            'items'     => $items,
            'heading'   => $heading,
            'margin'    => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block offer boxes
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_offer_boxes (array $block): array
{
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
            'icon'       => $block['data']['iconview'],
            'margin'     => volvo_global_get_block_acf_prepare_margin($block['data']),
        ],
    ];

    return $result;
}

/**
 * Block offer cards
 * 
 * @param array $block
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_acf_offer_cards (array $block, int $blog_id): array
{
    $cards = [];
    $cardsField = $block['data']['cards'];

    foreach ($cardsField as $card) {
        $link = '';

        if ($card['link'] !== "") {
            $link = volvo_global_build_link($card['link']);
        }
        $img_id = $card['image'];
        $item_url = wp_get_attachment_url($img_id);
        $item_url = volvo_global_clear_url($item_url, $blog_id);
        
        $images = [
            volvo_global_prepare_image_for_render($blog_id, $img_id, 472, 275, $item_url, 'crop'),
        ];
        
        $images = volvo_global_prepare_images_render($images);

        $cards[] = [
            'link'          => $link,
            'heading'       => $card['heading'],
            'description'   => $card['description'],
            'image'         => $images,
            'ctaText'       => $card['cta-text'],
        ];
    }

    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'heading'   => $block['data']['heading'],
            'items'     => $cards,
            'margin'    => volvo_global_get_block_acf_prepare_margin($block['data']),
        ],
    ];
    
    return $result;
}

/**
 * Block preview component
 * 
 * @param array $block
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_acf_preview_component (array $block, int $blog_id): array
{
    $reverse = $block['data']['image-position'] ?? false;
    
    $allowed_tags = ['h1','h2','h3','h4','h5','h6'];
    $heading_tag = $block['data']['heading_tag'];

    if (!in_array($heading_tag, $allowed_tags)) {
        $heading_tag = 'h2';
    }
    
    $img_id = $block['data']['img'];
    $item_url = wp_get_attachment_url($img_id);
    $item_url = volvo_global_clear_url($item_url, $blog_id);
    
    $images = [
        volvo_global_prepare_image_for_render($blog_id, $img_id, 1562, 1024, $item_url, 'fit'),
    ];

    $images = volvo_global_prepare_images_render($images);
    
    foreach ( $block['data']['content'] as $k => &$contentItem ) {
        if (array_key_exists('contact-info', $contentItem)) {
            $contentItem['contactPerson'] = volvo_global_block_contact_person($contentItem['contact-info']);
        }
        
        if (array_key_exists('link', $contentItem)) {
            $contentItem['link'] = volvo_global_block_link($contentItem['link']);
        }
    }
    
    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'reverse'      => $reverse == 'right',
            'image'        => $images,
            'heading'      => $block['data']['heading'],
            'heading_tag'  => $heading_tag,
            'content'      => $block['data']['content'],
            'margin'       => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block quick info
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_quick_info (array $block): array
{
    $result = [
        'block_name'    => $block['block_name'],
        'data' => [
            'items'        => $block['data']['items'],
            'margin'       => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block short notes
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_short_notes (array $block): array
{
    $result = [
        'block_name'    => $block['block_name'],
        'data' => [
            'notes'        => $block['data']['items'],
            'margin'       => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block site heading
 * 
 * @param array $block
 * @param int $post_id
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_acf_site_heading (array $block, int $post_id, int $blog_id): array
{
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
            'template'          => $template,
            'version'           => $version,
            'heading'           => $block['data']['heading'],
            'header_type'       => $block['data']['header_type'],
            'description'       => $block['data']['description'],
            'date'              => get_the_date('d.m.Y', $post_id),
            'tags'	            => volvo_global_get_post_tags($post_id),
            'current_blog_id'   => $blog_id,
            'margin'            => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}

/**
 * Block table component
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_table_component (array $block): array
{
    $content = $block['data']['table'];

    $result = [
        'block_name'    => $block['block_name'],
        'data' => [
            'color'          => $block['data']['table_color'],
            'blocked_column' => $block['data']['static_header'],
            'content'        => [
                'use_header'     => $content['use_header'],
                'caption'        => $content['caption'],
                'header'         => ($content['header'] ? $content['header'] : null),
                'body'           => ($content['body'] ? $content['body'] : null),
            ],
            'margin'         => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}

/**
 * Block text editor
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_text_editor (array $block): array
{
    $result = [
        'block_name'    => $block['block_name'],
        'data' => [
            'content'       => apply_filters( 'acf_the_content', $block['data']['content']),
            'margin'        => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}

/**
 * Block text editor extended
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_text_editor_extended (array $block): array
{
    $result = [
        'block_name'    => $block['block_name'],
        'data' => [
            'content'       => apply_filters( 'acf_the_content', $block['data']['content']),
            'margin'        => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}

/**
 * Block three boxes
 * 
 * @param array $block
 * @return array
 */
function volvo_global_get_block_acf_three_boxes (array $block): array
{
    $boxes = [];

    for ($i = 1; $i <= 3; $i++) {
        $boxData = $block['data']["offerBox{$i}"];

        if (is_array($boxData)) {
            $image = $boxData["imageBox{$i}"] ?? $boxData['imageBox'] ?? null;
            
            if (!empty($image)) {
                $image = volvo_global_prepare_image($image);
            }

            $boxes[] = [
                'image'       => $image,
                'heading'     => $boxData["heading{$i}"] ?? $boxData['heading'] ?? '',
                'description' => $boxData["description{$i}"] ?? $boxData['description'] ?? '',
                'link'        => $boxData["link{$i}"] ?? $boxData['link'] ?? null,
            ];
        }
    }

    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'title'       => $block['data']['mainHeading'],
            'items'       => $boxes,
            'margin'      => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];

    return $result;
}

/**
 * Block two column content component
 * 
 * @param array $block
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_acf_two_column_content_component (array $block, int $blog_id): array
{
    $img_id = $block['data']['img'];

    $image = [];
    $item_url = wp_get_attachment_url($img_id);
    $item_url = volvo_global_clear_url($item_url, $blog_id);
    
    $images = [
        volvo_global_prepare_image_for_render($blog_id, $img_id, 1300, 850, $item_url, 'crop', 300, 200, false),
    ];

    $image = volvo_global_prepare_images_render($images);

    $reverse = $block['data']['image-position'] ?? false;
    $video = $block['data']['video'];

    if (is_array($block['data']['content']) && !empty($block['data']['content']) && array_key_exists('description', $block['data']['content'][0])) {
        $contentParsedown = volvo_global_block_description_parsedown($block['data']['content'][0]['description']);
        $block['data']['content'][0]['description'] = $contentParsedown;
    }

    foreach ( $block['data']['content'] as &$contentItem ) {
        if (array_key_exists('contact-info', $contentItem)) {
            $contentItem['contactPerson'] = volvo_global_block_contact_person($contentItem['contact-info']);
        }

        if (array_key_exists('link', $contentItem) && !empty($contentItem['link'])) {
            $contentItem['link'] = volvo_global_block_link($contentItem['link']);
        }
    }

    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'reverse'           => 'right' == $reverse,
            'custom_reverse'    => $reverse, 
            'image'             => $image,
            'heading'           => $block['data']['heading'],
            'subheading'        => $block['data']['subheading'],
            'content'           => $block['data']['content'],
            'left_content'      => $block['data']['single_opt_column'],
            'equal_columns'     => $block['data']['image-width'],
            'video'             => ($video ? volvo_global_youtube_link_to_video_id($video) : false),
            'margin'            => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}

/**
 * Block two image
 * 
 * @param array $block
 * @param int $blog_id
 * @return array
 */
function volvo_global_get_block_acf_two_image (array $block, int $blog_id): array
{
    $img_id_first = $block['data']['firstPicture'];
    $img_id_second = $block['data']['secondPicture'];

    $item_url_first = wp_get_attachment_url($img_id_first);
    $item_url_first = volvo_global_clear_url($item_url_first, $blog_id);

    $item_url_second = wp_get_attachment_url($img_id_second);
    $item_url_second = volvo_global_clear_url($item_url_second, $blog_id);

    $image_first = [
        volvo_global_prepare_image_for_render($blog_id, $img_id_first, 1200, 800, $item_url_first, 'crop', 300, 200, false),
    ];
    $image_second = [
        volvo_global_prepare_image_for_render($blog_id, $img_id_second, 1200, 800, $item_url_second, 'crop', 300, 200, false),
    ];
    $image_first = volvo_global_prepare_images_render($image_first);
    $image_second = volvo_global_prepare_images_render($image_second);

    $result = [
        'block_name' => $block['block_name'],
        'data' => [
            'image1'     => $image_first,
            'image2'     => $image_second,
            'margin'     => volvo_global_get_block_acf_prepare_margin($block['data']),
        ]
    ];
    
    return $result;
}
