<?php

if (!defined('ABSPATH')) {
    exit;
}

class Feed_Template
{
    const OPTION_NAME = 'volvo_feed_templates';
    const MAIN_BLOG_ID = 1;

    private $id;
    private $name;
    private $slug;
    private $format;
    private $car_type;
    private $fields;
    private $blog_ids;
    private $is_global;
    private $created_at;
    private $updated_at;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->id = isset($data['id']) ? $data['id'] : uniqid('tpl_');
            $this->name = isset($data['name']) ? $data['name'] : '';
            $this->slug = isset($data['slug']) ? $data['slug'] : sanitize_title($data['name']);
            $this->format = isset($data['format']) ? $data['format'] : 'csv';
            $this->car_type = isset($data['car_type']) ? $data['car_type'] : 'both';
            $this->fields = isset($data['fields']) ? $data['fields'] : [];
            $this->blog_ids = isset($data['blog_ids']) ? $data['blog_ids'] : [];
            $this->is_global = isset($data['is_global']) ? $data['is_global'] : false;
            $this->created_at = isset($data['created_at']) ? $data['created_at'] : current_time('mysql');
            $this->updated_at = isset($data['updated_at']) ? $data['updated_at'] : current_time('mysql');
        }
    }

    public function to_array()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'format' => $this->format,
            'car_type' => $this->car_type,
            'fields' => $this->fields,
            'blog_ids' => $this->blog_ids,
            'is_global' => $this->is_global,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public static function get_templates($blog_id = null)
    {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }

        $current_blog_id = get_current_blog_id();
        
        if ($blog_id != $current_blog_id) {
            switch_to_blog($blog_id);
        }
        
        if ($blog_id == 1) {
            $option_name = self::OPTION_NAME;
        } else {
            $option_name = self::OPTION_NAME . '_' . $blog_id;
        }
        
        $templates = get_option($option_name, []);
        
        if ($blog_id != $current_blog_id) {
            restore_current_blog();
        }
        
        if (!is_array($templates)) {
            $templates = [];
        }

        return array_map(function($data) {
            return new self($data);
        }, $templates);
    }

    public static function get_global_templates()
    {
        return self::get_templates(1);
    }

    public static function get_template_by_slug($slug, $blog_id = null)
    {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }

        $templates = self::get_templates($blog_id);
        
        foreach ($templates as $template) {
            if ($template->slug === $slug) {
                return $template;
            }
        }

        if ($blog_id != 1) {
            $global_templates = self::get_templates(1);
            foreach ($global_templates as $template) {
                if ($template->slug === $slug) {
                    return $template;
                }
            }
        }

        return null;
    }

    public static function save_template($data, $blog_id = null)
    {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }

        $templates = self::get_templates($blog_id);
        
        if (isset($data['id']) && !empty($data['id'])) {
            $found = false;
            foreach ($templates as $index => $template) {
                if ($template->id === $data['id']) {
                    $templates[$index] = new self(array_merge($template->to_array(), $data));
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data['id'] = uniqid('tpl_');
                $templates[] = new self($data);
            }
        } else {
            $data['id'] = uniqid('tpl_');
            $templates[] = new self($data);
        }

        return self::save_all_templates($templates, $blog_id);
    }

    public static function delete_template($template_id, $blog_id = null)
    {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }

        $templates = self::get_templates($blog_id);
        
        $templates = array_filter($templates, function($template) use ($template_id) {
            return $template->id !== $template_id;
        });

        return self::save_all_templates($templates, $blog_id);
    }

    public static function copy_template($template_id, $new_name, $blog_id = null)
    {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }

        $template = null;
        $source_blog_id = $blog_id;

        $templates = self::get_templates($blog_id);
        foreach ($templates as $t) {
            if ($t->id === $template_id) {
                $template = $t;
                break;
            }
        }

        if ($template === null && $blog_id != 1) {
            $global_templates = self::get_templates(1);
            foreach ($global_templates as $t) {
                if ($t->id === $template_id) {
                    $template = $t;
                    $source_blog_id = 1;
                    break;
                }
            }
        }

        if ($template === null) {
            return false;
        }

        $data = $template->to_array();
        unset($data['id']);
        $data['name'] = $new_name;
        $data['slug'] = sanitize_title($new_name);
        $data['is_global'] = false;
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');

        $target_templates = self::get_templates($blog_id);
        
        $base_slug = $data['slug'];
        $counter = 1;
        while (true) {
            $slug_exists = false;
            foreach ($target_templates as $t) {
                if ($t->slug === $data['slug']) {
                    $slug_exists = true;
                    break;
                }
            }
            if (!$slug_exists) break;
            $data['slug'] = $base_slug . '-' . $counter;
            $counter++;
        }
        
        $target_templates[] = new self($data);

        return self::save_all_templates($target_templates, $blog_id);
    }

    private static function save_all_templates($templates, $blog_id)
    {
        $current_blog_id = get_current_blog_id();
        
        if ($blog_id != $current_blog_id) {
            switch_to_blog($blog_id);
        }
        
        if ($blog_id == 1) {
            $option_name = self::OPTION_NAME;
        } else {
            $option_name = self::OPTION_NAME . '_' . $blog_id;
        }
        
        $data = array_map(function($template) {
            return $template->to_array();
        }, $templates);

        $result = update_option($option_name, $data);
        
        if ($blog_id != $current_blog_id) {
            restore_current_blog();
        }
        
        return $result;
    }

    public static function create_default_templates($blog_id = null)
    {
        if ($blog_id === null) {
            $blog_id = 1;
        }

        $current_blog_id = get_current_blog_id();
        
        if ($blog_id != $current_blog_id) {
            switch_to_blog($blog_id);
        }
        
        $option_name = ($blog_id == 1) ? self::OPTION_NAME : self::OPTION_NAME . '_' . $blog_id;
        $existing = get_option($option_name, []);
        
        if (!empty($existing)) {
            if ($blog_id != $current_blog_id) {
                restore_current_blog();
            }
            return;
        }

        $default_templates = [
            [
                'name' => 'Facebook CSV',
                'slug' => 'facebook-csv',
                'format' => 'csv',
                'car_type' => 'both',
                'fields' => [
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'description',
                    'price' => 'price',
                    'image_url' => 'image_url',
                    'link' => 'link',
                    'brand' => 'brand',
                    'model' => 'model',
                    'year' => 'year',
                    'mileage' => 'mileage',
                    'fuel_type' => 'fuel_type',
                    'vin' => 'vin',
                ],
                'is_global' => true,
            ],
            [
                'name' => 'Otomoto XML',
                'slug' => 'otomoto-xml',
                'format' => 'xml',
                'car_type' => 'both',
                'fields' => [
                    'id' => 'offer_id',
                    'title' => 'title',
                    'price' => 'price',
                    'currency' => 'currency',
                    'year' => 'year',
                    'mileage' => 'mileage',
                    'fuel_type' => 'fuel_type',
                    'gearbox' => 'gearbox',
                    'drive' => 'drive',
                    'body_type' => 'body_type',
                    'color' => 'color',
                    'door_count' => 'door_count',
                    'engine' => 'engine',
                    'power' => 'power',
                    'image_url' => 'image_url',
                    'images' => 'images',
                    'description' => 'description',
                    'vin' => 'vin',
                    'dealer_name' => 'dealer_name',
                    'dealer_phone' => 'dealer_phone',
                    'dealer_location' => 'dealer_location',
                ],
                'is_global' => true,
            ],
            [
                'name' => 'Findcar.pl XML',
                'slug' => 'findcar-xml',
                'format' => 'xml',
                'car_type' => 'both',
                'fields' => [
                    'id' => 'id',
                    'title' => 'title',
                    'price' => 'price',
                    'year' => 'year',
                    'mileage' => 'mileage',
                    'fuel_type' => 'fuel_type',
                    'transmission' => 'transmission',
                    'image' => 'image',
                    'gallery' => 'gallery',
                    'description' => 'description',
                    'make' => 'make',
                    'model' => 'model',
                    'version' => 'version',
                    ' VIN' => 'vin',
                    'dealer' => 'dealer',
                ],
                'is_global' => true,
            ],
        ];

        $templates = [];
        foreach ($default_templates as $data) {
            $data['created_at'] = current_time('mysql');
            $data['updated_at'] = current_time('mysql');
            $templates[] = new self($data);
        }

        self::save_all_templates($templates, $blog_id);
        
        if ($blog_id != $current_blog_id) {
            restore_current_blog();
        }
    }

    public static function init_ajax_handlers()
    {
        add_action('wp_ajax_vft_get_templates', [__CLASS__, 'ajax_get_templates']);
        add_action('wp_ajax_vft_save_template', [__CLASS__, 'ajax_save_template']);
        add_action('wp_ajax_vft_delete_template', [__CLASS__, 'ajax_delete_template']);
        add_action('wp_ajax_vft_copy_template', [__CLASS__, 'ajax_copy_template']);
        add_action('wp_ajax_vft_get_preview', [__CLASS__, 'ajax_get_preview']);
    }

    public static function ajax_get_templates()
    {
        check_ajax_referer('volvo_feed_templates_nonce', 'nonce');
        
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : get_current_blog_id();
        
        $templates = self::get_templates($blog_id);
        $templates_arr = array_map(function($t) { return $t->to_array(); }, $templates);
        
        $global = self::get_templates(1);
        $global_arr = array_map(function($t) { return $t->to_array(); }, $global);

        wp_send_json_success([
            'templates' => $templates_arr,
            'globalTemplates' => $global_arr,
            'debug' => [
                'blog_id' => $blog_id,
                'templates_count' => count($templates_arr),
                'global_count' => count($global_arr)
            ]
        ]);
    }

    public static function ajax_save_template()
    {
        check_ajax_referer('volvo_feed_templates_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Brak uprawnień'], 403);
        }

        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : [];
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : get_current_blog_id();

        if (empty($data) || !is_array($data)) {
            wp_send_json_error(['message' => 'Nieprawidłowe dane'], 400);
        }

        if (empty($data['name'])) {
            wp_send_json_error(['message' => 'Nazwa feedu jest wymagana'], 400);
        }

        if (empty($data['slug'])) {
            $data['slug'] = sanitize_title($data['name']);
        } else {
            $data['slug'] = sanitize_title($data['slug']);
        }

        $result = self::save_template($data, $blog_id);

        if ($result) {
            wp_send_json_success(['message' => 'Feed zapisany pomyślnie']);
        } else {
            wp_send_json_error(['message' => 'Nie udało się zapisać feedu'], 500);
        }
    }

    public static function ajax_delete_template()
    {
        check_ajax_referer('volvo_feed_templates_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : get_current_blog_id();

        if (empty($template_id)) {
            wp_send_json_error(['message' => 'ID feedu jest wymagane'], 400);
        }

        $result = self::delete_template($template_id, $blog_id);

        if ($result) {
            wp_send_json_success(['message' => 'Feed usunięty pomyślnie']);
        } else {
            wp_send_json_error(['message' => 'Nie udało się usunąć feedu'], 500);
        }
    }

    public static function ajax_copy_template()
    {
        check_ajax_referer('volvo_feed_templates_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Brak uprawnień'], 403);
        }

        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
        $new_name = isset($_POST['new_name']) ? sanitize_text_field($_POST['new_name']) : '';
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : get_current_blog_id();

        if (empty($template_id) || empty($new_name)) {
            wp_send_json_error(['message' => 'ID feedu i nowa nazwa są wymagane'], 400);
        }

        $result = self::copy_template($template_id, $new_name, $blog_id);

        if ($result) {
            wp_send_json_success(['message' => 'Feed skopiowany pomyślnie']);
        } else {
            wp_send_json_error(['message' => 'Nie udało się skopiować feedu'], 500);
        }
    }

    public static function ajax_get_preview()
    {
        check_ajax_referer('volvo_feed_templates_nonce', 'nonce');
        
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : get_current_blog_id();

        if (empty($template_id)) {
            wp_send_json_error(['message' => 'ID feedu jest wymagane'], 400);
        }

        $template = self::get_template_by_slug($template_id, $blog_id);
        
        if (!$template) {
            wp_send_json_error(['message' => 'Feed nie znaleziony'], 404);
        }

        $generator = new Feed_Generator($template, $blog_id);
        $cars = $generator->get_cars();
        $preview = $generator->generate_preview($cars);

        wp_send_json_success([
            'preview' => $preview,
            'carCount' => count($cars)
        ]);
    }

    public static function get_available_fields()
    {
        return [
            'id' => __('ID', 'volvo-feed-templates'),
            'title' => __('Tytuł', 'volvo-feed-templates'),
            'description' => __('Opis', 'volvo-feed-templates'),
            'price' => __('Cena', 'volvo-feed-templates'),
            'discount_price' => __('Cena promocyjna', 'volvo-feed-templates'),
            'image_url' => __('Główne zdjęcie', 'volvo-feed-templates'),
            'images' => __('Wszystkie zdjęcia', 'volvo-feed-templates'),
            'link' => __('Link', 'volvo-feed-templates'),
            'brand' => __('Marka', 'volvo-feed-templates'),
            'model' => __('Model', 'volvo-feed-templates'),
            'version' => __('Wersja', 'volvo-feed-templates'),
            'year' => __('Rok', 'volvo-feed-templates'),
            'production_year' => __('Rok produkcji', 'volvo-feed-templates'),
            'mileage' => __('Przebieg', 'volvo-feed-templates'),
            'fuel_type' => __('Rodzaj paliwa', 'volvo-feed-templates'),
            'gearbox' => __('Skrzynia biegów', 'volvo-feed-templates'),
            'transmission' => __('Napęd', 'volvo-feed-templates'),
            'drive' => __('Napęd', 'volvo-feed-templates'),
            'body_type' => __('Typ nadwozia', 'volvo-feed-templates'),
            'color' => __('Kolor', 'volvo-feed-templates'),
            'door_count' => __('Liczba drzwi', 'volvo-feed-templates'),
            'engine' => __('Silnik', 'volvo-feed-templates'),
            'power' => __('Moc', 'volvo-feed-templates'),
            'power_hp' => __('Moc (KM)', 'volvo-feed-templates'),
            'vin' => __('VIN', 'volvo-feed-templates'),
            'offer_number' => __('Numer oferty', 'volvo-feed-templates'),
            'category' => __('Kategoria', 'volvo-feed-templates'),
            'dealer_name' => __('Nazwa dealera', 'volvo-feed-templates'),
            'dealer_phone' => __('Telefon dealera', 'volvo-feed-templates'),
            'dealer_location' => __('Lokalizacja dealera', 'volvo-feed-templates'),
            'dealer_id' => __('ID dealera', 'volvo-feed-templates'),
            'showroom' => __('Salon', 'volvo-feed-templates'),
            'currency' => __('Waluta', 'volvo-feed-templates'),
            'is_featured' => __('Wyróżniony', 'volvo-feed-templates'),
            'car_type' => __('Typ samochodu', 'volvo-feed-templates'),
        ];
    }

    public function get_id() { return $this->id; }
    public function get_name() { return $this->name; }
    public function get_slug() { return $this->slug; }
    public function get_format() { return $this->format; }
    public function get_car_type() { return $this->car_type; }
    public function get_fields() { return $this->fields; }
    public function get_blog_ids() { return $this->blog_ids; }
    public function is_global() { return $this->is_global; }
    public function get_created_at() { return $this->created_at; }
}
