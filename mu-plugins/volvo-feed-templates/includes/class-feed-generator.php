<?php

if (!defined('ABSPATH')) {
    exit;
}

class Feed_Generator
{
    private $template;
    private $blog_id;

    public function __construct($template, $blog_id = null)
    {
        $this->template = $template;
        $this->blog_id = $blog_id ?: get_current_blog_id();
    }

    public function get_cars()
    {
        $car_type = $this->template->get_car_type();
        
        $args = [
            'post_type' => 'stock-car',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        if ($car_type === 'used') {
            $args['meta_query'] = [
                [
                    'key' => 'cartype',
                    'value' => 'used',
                    'compare' => '='
                ]
            ];
        } elseif ($car_type === 'new') {
            $args['meta_query'] = [
                [
                    'key' => 'cartype',
                    'value' => 'nowy',
                    'compare' => '='
                ]
            ];
        }

        $query = new WP_Query($args);
        $car_ids = $query->posts;

        $cars = [];
        foreach ($car_ids as $car_id) {
            $cars[] = $this->get_car_data($car_id);
        }

        return array_filter($cars);
    }

    private function get_car_data($car_id)
    {
        $post = get_post($car_id);
        if (!$post || $post->post_type !== 'stock-car') {
            return null;
        }

        $car = [
            'id' => $car_id,
            'post_id' => $car_id,
            'title' => $post->post_title,
            'description' => wp_strip_all_tags($post->post_content),
            'link' => get_permalink($car_id),
        ];

        $acf_fields = [
            'cartype' => 'car_type',
            'model' => 'model',
            'version' => 'version',
            'vin' => 'vin',
            'production-year' => 'production_year',
            'offer-number' => 'offer_number',
            'regular-price' => 'price',
            'discount-price' => 'discount_price',
            'car-distance' => 'mileage',
            'mileage' => 'mileage',
            'category' => 'category',
            'sales-phone' => 'dealer_phone',
            'eurocode' => 'eurocode',
            'con' => 'con',
            'pno' => 'pno',
            'is-featured' => 'is_featured',
        ];

        foreach ($acf_fields as $acf_key => $local_key) {
            $value = get_field($acf_key, $car_id);
            $car[$local_key] = $value;
        }

        switch_to_blog(1);
        
        $showroom_id = get_field('showroom', $car_id);
        if ($showroom_id) {
            $showroom = get_post($showroom_id);
            if ($showroom) {
                $car['showroom'] = $showroom->post_title;
                $car['dealer_name'] = get_field('dealer_name', $showroom_id) ?: $showroom->post_title;
                $car['dealer_location'] = get_field('city', $showroom_id) ?: '';
                $car['dealer_id'] = get_field('dealer_id', $showroom_id) ?: '';
            }
        } else {
            $car['showroom'] = '';
            $car['dealer_name'] = '';
            $car['dealer_location'] = '';
            $car['dealer_id'] = '';
        }

        $car['brand'] = 'Volvo';
        
        $car['image_url'] = $this->get_car_image($car_id);
        $car['images'] = $this->get_car_gallery($car_id);
        
        restore_current_blog();

        $car['fuel_type'] = get_field('engine_type', $car_id) ?: '';
        $car['gearbox'] = get_field('gearbox', $car_id) ?: '';
        $car['transmission'] = get_field('gearbox', $car_id) ?: '';
        $car['drive'] = get_field('drive', $car_id) ?: '';
        $car['body_type'] = '';
        $car['color'] = get_field('color_name', $car_id) ?: get_field('color', $car_id) ?: '';
        $car['door_count'] = get_field('door_count', $car_id) ?: '';
        $car['engine'] = get_field('engine', $car_id) ?: '';
        $car['power'] = get_field('power', $car_id) ?: get_field('power_hp', $car_id) ?: '';
        $car['power_hp'] = get_field('power_hp', $car_id) ?: '';
        
        $car['currency'] = 'PLN';

        return $car;
    }

    private function get_car_image($car_id)
    {
        switch_to_blog(1);
        
        $social_image = get_field('social_image', $car_id);
        
        if (!empty($social_image) && is_array($social_image)) {
            $image_id = is_array($social_image) ? $social_image[0] : $social_image;
            $url = wp_get_attachment_url($image_id);
            restore_current_blog();
            return $url ?: '';
        }

        $images = get_field('images', $car_id);
        
        if (!empty($images) && is_array($images)) {
            $first_image = reset($images);
            if (is_array($first_image)) {
                $url = isset($first_image['url']) ? $first_image['url'] : wp_get_attachment_url($first_image);
            } else {
                $url = wp_get_attachment_url($first_image);
            }
            restore_current_blog();
            return $url ?: '';
        }

        $gallery = get_field('gallery', $car_id);
        
        if (!empty($gallery) && is_array($gallery)) {
            $first_gallery = reset($gallery);
            if (is_array($first_gallery)) {
                $url = isset($first_gallery['url']) ? $first_gallery['url'] : wp_get_attachment_url($first_gallery);
            } else {
                $url = wp_get_attachment_url($first_gallery);
            }
            restore_current_blog();
            return $url ?: '';
        }

        restore_current_blog();
        return '';
    }

    private function get_car_gallery($car_id)
    {
        switch_to_blog(1);
        
        $images = get_field('images', $car_id);
        
        if (empty($images) || !is_array($images)) {
            restore_current_blog();
            return [];
        }

        $gallery = [];
        foreach ($images as $image) {
            if (is_array($image)) {
                $gallery[] = isset($image['url']) ? $image['url'] : wp_get_attachment_url($image);
            } else {
                $gallery[] = wp_get_attachment_url($image);
            }
        }

        restore_current_blog();
        return array_filter($gallery);
    }

    public function generate($cars = null)
    {
        if ($cars === null) {
            $cars = $this->get_cars();
        }

        $format = $this->template->get_format();

        if ($format === 'xml') {
            return $this->generate_xml($cars);
        }

        return $this->generate_csv($cars);
    }

    public function generate_csv($cars = null)
    {
        if ($cars === null) {
            $cars = $this->get_cars();
        }

        $field_mappings = $this->template->get_fields();
        
        if (empty($field_mappings) || !is_array($field_mappings)) {
            $field_mappings = [
                'id' => 'id',
                'title' => 'title',
                'price' => 'price',
                'model' => 'model',
                'year' => 'year',
                'mileage' => 'mileage',
                'image_url' => 'image_url'
            ];
        }

        $output = fopen('php://temp', 'r+');
        
        $headers = [];
        foreach ($field_mappings as $source => $tag) {
            $headers[] = $tag ?: $source;
        }
        fputcsv($output, $headers, ';');

        foreach ($cars as $car) {
            $row = [];
            foreach ($field_mappings as $source => $tag) {
                $value = isset($car[$source]) ? $car[$source] : '';
                
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                
                $row[] = $value;
            }
            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    public function generate_xml($cars = null)
    {
        if ($cars === null) {
            $cars = $this->get_cars();
        }

        $field_mappings = $this->template->get_fields();

        if (empty($field_mappings) || !is_array($field_mappings)) {
            $field_mappings = [
                'id' => 'id',
                'title' => 'title',
                'price' => 'price',
                'model' => 'model',
                'year' => 'year',
                'mileage' => 'mileage'
            ];
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><offers></offers>');
        
        foreach ($cars as $car) {
            $offer = $xml->addChild('offer');
            
            foreach ($field_mappings as $source => $tag) {
                $tag_name = $tag ?: $source;
                $value = isset($car[$source]) ? $car[$source] : '';
                
                if (is_array($value)) {
                    if ($tag_name === 'images' || $tag_name === 'gallery') {
                        $images_elem = $offer->addChild($tag_name);
                        foreach ($value as $img) {
                            $images_elem->addChild('image', htmlspecialchars($img));
                        }
                    } else {
                        $value = implode(', ', $value);
                        $offer->addChild($tag_name, htmlspecialchars($value));
                    }
                } else {
                    $offer->addChild($tag_name, htmlspecialchars($value));
                }
            }
        }

        return $xml->asXML();
    }

    public function generate_preview($cars = null, $max_cars = 5)
    {
        if ($cars === null) {
            $cars = $this->get_cars();
        }

        $cars = array_slice($cars, 0, $max_cars);
        $format = $this->template->get_format();

        if ($format === 'xml') {
            return $this->generate_xml($cars);
        }

        return $this->generate_csv($cars);
    }

    public function get_feed_url($force_format = null)
    {
        $blog = get_blog_details($this->blog_id);
        $slug = $this->template->get_slug();
        
        if ($force_format) {
            $url = trailingslashit($blog->path) . 'feeds/' . $slug . '/' . $force_format;
        } else {
            $url = trailingslashit($blog->path) . 'feeds/' . $slug;
        }
        
        return $url;
    }
}
