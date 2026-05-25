<?php

if (!defined('ABSPATH')) {
    exit;
}

class FindCar_Data_Mapper
{
    private $dictionaries = [];
    private $makes_models = [];

    const FUEL_TYPE_MAP = [
        'benzyna' => 11001,
        'petrol' => 11001,
        'gasoline' => 11001,
        'diesel' => 11002,
        'hybryda' => 11003,
        'hybrid' => 11003,
        'hev' => 11003,
        'plug-in' => 11004,
        'phev' => 11004,
        'elektryczny' => 11005,
        'electric' => 11005,
        'ev' => 11005,
        'wodór' => 11006,
        'hydrogen' => 11006,
        'lpg' => 11007,
        'cng' => 11008,
        'mhev' => 11009,
    ];

    const TRANSMISSION_MAP = [
        'automatic' => 12001,
        'automata' => 12001,
        'automat' => 12001,
        'manual' => 12002,
        'manualna' => 12002,
        'manualna' => 12002,
    ];

    const DRIVE_TYPE_MAP = [
        'fwd' => 13001,
        'front' => 13001,
        'przedni' => 13001,
        'rwd' => 13002,
        'rear' => 13002,
        'tylni' => 13002,
        '4x4' => 13003,
        'awd' => 13003,
        'all-wheel' => 13003,
        '4WD' => 13003,
    ];

    const BODY_TYPE_MAP = [
        'suv' => 14012,
        'crossover' => 14012,
        'kompakt' => 14008,
        'compact' => 14008,
        'sedan' => 14005,
        'limuzyna' => 14005,
        'kombi' => 14007,
        'estate' => 14007,
        'wagon' => 14007,
        'hatchback' => 14002,
        'liftback' => 14003,
        'coupe' => 14006,
        'sportowy' => 14006,
        'cabrio' => 14004,
        'kabriolet' => 14004,
        'roadster' => 14004,
        'van' => 14010,
        'minibus' => 14010,
        'mpv' => 14011,
        'minivan' => 14011,
        'pickup' => 14009,
        'małe' => 14001,
        'small' => 14001,
        'miejskie' => 14002,
        'city' => 14002,
        'SUV' => 14012,
        'Crossover' => 14012,
        'Kombi' => 14007,
    ];

    const COLOR_MAP = [
        'biały' => 17001,
        'white' => 17001,
        'czarny' => 17002,
        'black' => 17002,
        'szary' => 17003,
        'grafitowy' => 17003,
        'gray' => 17003,
        'grey' => 17003,
        'srebrny' => 17004,
        'silver' => 17004,
        'niebieski' => 17005,
        'granatowy' => 17005,
        'blue' => 17005,
        'navy' => 17005,
        'czerwony' => 17006,
        'bordowy' => 17006,
        'red' => 17006,
        'burgundy' => 17006,
        'zielony' => 17007,
        'green' => 17007,
        'brązowy' => 17008,
        'brown' => 17008,
        'pomarańczowy' => 17009,
        'orange' => 17009,
        'beżowy' => 17010,
        'beige' => 17010,
        'złoty' => 17011,
        'gold' => 17011,
        'żółty' => 17012,
        'yellow' => 17012,
        'fioletowy' => 17013,
        'violet' => 17013,
        'purple' => 17013,
        'różowy' => 17014,
        'pink' => 17014,
    ];

    const INVENTORY_TYPE_MAP = [
        'nowy' => 19001,
        'new' => 19001,
        'brand_new' => 19001,
        'used' => 19002,
        'używany' => 19002,
        'pre_owned' => 19002,
    ];

    public function __construct()
    {
    }

    public function load_dictionaries()
    {
        if (!get_field('findcar_enabled', 'options-dealer')) {
            return;
        }

        $cached = get_transient('findcar_dictionaries');
        if ($cached !== false) {
            $this->dictionaries = $cached;
            return;
        }

        if (class_exists('FindCar_API_Client')) {
            $client = new FindCar_API_Client();
            $dictionaries = $client->get_dictionaries();
            if (!is_wp_error($dictionaries) && is_array($dictionaries)) {
                $this->dictionaries = $dictionaries;
                set_transient('findcar_dictionaries', $dictionaries, DAY_IN_SECONDS);
            }
        }
    }

    public function refresh_dictionaries()
    {
        delete_transient('findcar_dictionaries');
        $this->load_dictionaries();
    }

    public function map_car_to_listing($car_id, $showroom_id = null)
    {
        $car = $this->get_car_data($car_id);
        
        if (!$car) {
            return new WP_Error('findcar_map_error', __('Car not found', 'volvo-feed-templates'));
        }

        $listing = [];

        $listing['makeId'] = $this->get_volvo_make_id();

        $model_name = get_field('model', $car_id);
        if (!empty($model_name)) {
            $listing['model'] = [
                'originalName' => $model_name,
            ];
        } else {
            return new WP_Error('findcar_map_error', __('No car model', 'volvo-feed-templates'));
        }

        $production_year = get_field('production-year', $car_id);
        if (!empty($production_year)) {
            $listing['productionYear'] = intval($production_year);
        }

        $price = get_field('regular-price', $car_id);
        if (!empty($price)) {
            $listing['priceOfferPln100'] = intval($price * 100);
        }

        $description = get_the_content(null, false, $car_id);
        if (empty($description)) {
            $accordion_heading = get_field('accordion-heading', $car_id);
            $accordion = get_field('accordion', $car_id);
            if (!empty($accordion_heading) || !empty($accordion)) {
                $description_parts = [];
                if (!empty($accordion_heading)) {
                    $description_parts[] = $accordion_heading;
                }
                if (!empty($accordion) && is_array($accordion)) {
                    foreach ($accordion as $section) {
                        if (!empty($section['name'])) {
                            $description_parts[] = $section['name'];
                            if (!empty($section['items']) && is_array($section['items'])) {
                                foreach ($section['items'] as $item) {
                                    if (!empty($item['name'])) {
                                        $description_parts[] = $item['name'];
                                    }
                                }
                            }
                        }
                    }
                }
                $description = implode("\n", $description_parts);
            }
        }
        if (!empty($description)) {
            $listing['contentBody'] = wp_strip_all_tags($description);
        }

        $images = $this->get_car_images($car_id);
        if (!empty($images)) {
            $listing['mediaUrls'] = $images;
        } else {
            return new WP_Error('findcar_map_error', __('No photos of the car', 'volvo-feed-templates'));
        }

        $drive = get_field('driveType', $car_id);
        if (!empty($drive)) {
            $listing['driveType'] = $this->map_coded_value($drive, self::DRIVE_TYPE_MAP, 13000);
        }

        $gearbox = get_field('gearbox', $car_id);
        if (!empty($gearbox)) {
            $listing['transmission'] = $this->map_coded_value($gearbox, self::TRANSMISSION_MAP, 12000);
        }

        $engine = get_field('engine', $car_id);
        if (!empty($engine)) {
            $listing['engineName'] = $engine;
        }

        $engine_capacity = get_field('engine-capacity', $car_id);
        if (empty($engine_capacity)) {
            $engine_value = get_field('engine', $car_id);
            if (!empty($engine_value) && is_string($engine_value)) {
                if (preg_match('/(\d+\.?\d*)\s*l/i', $engine_value, $matches)) {
                    $engine_capacity = floatval($matches[1]) * 1000;
                } elseif (preg_match('/(\d+)\s*cm3/i', $engine_value, $matches)) {
                    $engine_capacity = intval($matches[1]);
                }
            }
            if (empty($engine_capacity)) {
                $engine_type = get_field('engine_type', $car_id);
                if (empty($engine_type)) {
                    $engine_type = get_field('fuel-type', $car_id);
                }
                if (!empty($engine_type) && in_array(strtolower($engine_type), ['diesel', 'benzyna', 'petrol'])) {
                    $engine_capacity = 2000;
                }
            }
        }
        if (!empty($engine_capacity)) {
            $listing['engineCapacityCc'] = intval($engine_capacity);
        }

        $power_hp = get_field('power_hp', $car_id);
        if (empty($power_hp)) {
            $power_hp = get_field('max-power', $car_id);
        }
        if (empty($power_hp)) {
            $power_text = get_field('max-power-text', $car_id);
            if (!empty($power_text) && preg_match('/(\d+)/', $power_text, $matches)) {
                $power_hp = intval($matches[1]);
            }
        }
        if (!empty($power_hp)) {
            $listing['enginePowerHp'] = intval($power_hp);
        }

        $engine_type = get_field('engine_type', $car_id);
        if (empty($engine_type)) {
            $engine_type = get_field('fuel-type', $car_id);
        }
        if (!empty($engine_type)) {
            $listing['fuelType'] = $this->map_coded_value($engine_type, self::FUEL_TYPE_MAP, 11000);
        }

        $color = get_field('color_name', $car_id);
        if (empty($color)) {
            $color = get_field('color', $car_id);
        }
        if (!empty($color)) {
            $listing['color'] = $this->map_coded_value($color, self::COLOR_MAP, 17000);
        }

        $body_type = get_field('body_type', $car_id);
        if (empty($body_type)) {
            $body_type = get_field('category', $car_id);
        }
        if (!empty($body_type)) {
            $listing['bodyType'] = $this->map_coded_value($body_type, self::BODY_TYPE_MAP, 14000);
        }

        $seats = get_field('seats', $car_id);
        if (!empty($seats)) {
            $listing['seats'] = intval($seats);
        } else {
            $listing['seats'] = 5;
        }

        $mileage = get_field('car-distance', $car_id);
        if (empty($mileage)) {
            $mileage = get_field('mileage', $car_id);
        }
        if (!empty($mileage)) {
            $listing['mileageKm'] = intval($mileage);
        }

        $registration_date = get_field('registration_date', $car_id);
        if (!empty($registration_date)) {
            $listing['registrationDate'] = $registration_date;
        }

        $cartype = get_field('cartype', $car_id);
        if (!empty($cartype)) {
            $listing['inventoryType'] = $this->map_coded_value($cartype, self::INVENTORY_TYPE_MAP, 19001);
        }

        $vin = get_field('vin', $car_id);
        if (!empty($vin)) {
            $listing['vin'] = $vin;
        }

        $version = get_field('version', $car_id);
        if (!empty($version)) {
            $listing['versionName'] = $version;
        }

        $registration_number = get_field('registration_number', $car_id);
        if (!empty($registration_number)) {
            $listing['registrationNumber'] = $registration_number;
        }

        $has_tow_hitch = get_field('has_tow_hitch', $car_id);
        if (!empty($has_tow_hitch)) {
            $listing['hasTowHitch'] = (bool) $has_tow_hitch;
        }

        $doors = get_field('door_count', $car_id);
        if (!empty($doors)) {
            $listing['doors'] = intval($doors);
        }

        $wheel_size = get_field('wheel_size', $car_id);
        if (!empty($wheel_size)) {
            $listing['wheelSize'] = intval($wheel_size);
        }

        $warranty_months = get_field('warranty_months', $car_id);
        if (!empty($warranty_months)) {
            $listing['warrantyMonths'] = intval($warranty_months);
        }

        $warranty_due_date = get_field('warranty_due_date', $car_id);
        if (!empty($warranty_due_date)) {
            $listing['warrantyDueDate'] = $warranty_due_date;
        }

        if (empty($listing['contentBody'])) {
            return new WP_Error('findcar_map_error', __('No offer content (contentBody)', 'volvo-feed-templates'));
        }
        if (empty($listing['engineCapacityCc'])) {
            return new WP_Error('findcar_map_error', __('No engine capacity (engineCapacityCc)', 'volvo-feed-templates'));
        }
        if (empty($listing['enginePowerHp'])) {
            return new WP_Error('findcar_map_error', __('No engine power (enginePowerHp)', 'volvo-feed-templates'));
        }
        if (empty($listing['bodyType'])) {
            return new WP_Error('findcar_map_error', __('No car body type (bodyType)', 'volvo-feed-templates'));
        }

        $listing = apply_filters('findcar_map_car_to_listing', $listing, $car_id);

        return $listing;
    }

    private function get_car_data($car_id)
    {
        $post = get_post($car_id);
        if (!$post || $post->post_type !== 'stock-car') {
            return null;
        }
        return $post;
    }

    private function get_car_images($car_id)
    {
        $images = [];

        $gallery = get_field('gallery', $car_id);
        if (!empty($gallery) && is_array($gallery)) {
            foreach ($gallery as $image) {
                if (is_array($image) && isset($image['url'])) {
                    $images[] = $image['url'];
                } elseif (is_numeric($image)) {
                    $url = wp_get_attachment_url($image);
                    if ($url) {
                        $images[] = $url;
                    }
                }
            }
        }

        if (empty($images)) {
            $images_field = get_field('images', $car_id);
            if (!empty($images_field) && is_array($images_field)) {
                foreach ($images_field as $image) {
                    if (is_array($image) && isset($image['url'])) {
                        $images[] = $image['url'];
                    } elseif (is_numeric($image)) {
                        $url = wp_get_attachment_url($image);
                        if ($url) {
                            $images[] = $url;
                        }
                    }
                }
            }
        }

        return $images;
    }

    private function map_coded_value($value, $map, $default_code)
    {
        $value_lower = strtolower(trim($value));
        
        if (isset($map[$value_lower])) {
            return [
                'code' => $map[$value_lower],
                'originalValue' => $value,
            ];
        }

        return [
            'code' => $default_code,
            'originalValue' => $value,
        ];
    }

    private function get_volvo_make_id()
    {
        return '8d32213a-cd73-42d3-a034-29d16d3f6762';
    }

    public function get_required_fields()
    {
        return [
            'model' => __('Car model', 'volvo-feed-templates'),
            'production-year' => __('Year of manufacture', 'volvo-feed-templates'),
            'regular-price' => __('Price', 'volvo-feed-templates'),
            'images' => __('Photos', 'volvo-feed-templates'),
            'gallery' => __('Photo Gallery', 'volvo-feed-templates'),
            'fuel-type' => __('Fuel Type', 'volvo-feed-templates'),
            'engine_type' => __('Fuel Type', 'volvo-feed-templates'),
            'gearbox' => __('Transmission', 'volvo-feed-templates'),
            'driveType' => __('Drive', 'volvo-feed-templates'),
            'color' => __('Color', 'volvo-feed-templates'),
        ];
    }

    public function validate_car_fields($car_id)
    {
        $required_fields = $this->get_required_fields();
        $missing_fields = [];

        foreach ($required_fields as $field => $label) {
            if ($field === 'engine_type') {
                $value = get_field('engine_type', $car_id);
                if (empty($value)) {
                    $value = get_field('fuel-type', $car_id);
                }
            } elseif ($field === 'images' || $field === 'gallery') {
                $value = get_field($field, $car_id);
                if (empty($value)) {
                    $missing_fields[] = $label;
                }
                continue;
            } else {
                $value = get_field($field, $car_id);
            }
            
            if (empty($value)) {
                $missing_fields[] = $label;
            }
        }

        return $missing_fields;
    }

    public function get_car_listing_id($car_id)
    {
        $offer_number = get_field('offer-number', $car_id);
        if (!empty($offer_number)) {
            return $offer_number;
        }
        
        return 'car-' . $car_id;
    }

    public function map_listing_to_car($listing_data)
    {
        $car = [];

        if (isset($listing_data['partnerListingId'])) {
            $car['partner_listing_id'] = $listing_data['partnerListingId'];
        }

        if (isset($listing_data['id'])) {
            $car['listing_id'] = $listing_data['id'];
        }

        if (isset($listing_data['publicListingNumber'])) {
            $car['listing_number'] = $listing_data['publicListingNumber'];
        }

        if (isset($listing_data['listingUrl'])) {
            $car['listing_url'] = $listing_data['listingUrl'];
        }

        if (isset($listing_data['carListing']['listingStatus'])) {
            $car['listing_status'] = $listing_data['carListing']['listingStatus'];
        }

        if (isset($listing_data['status'])) {
            $car['status'] = $listing_data['status'];
        }

        return $car;
    }
}