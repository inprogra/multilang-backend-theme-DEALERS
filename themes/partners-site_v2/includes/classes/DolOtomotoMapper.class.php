<?php

namespace Classes;

class DolOtomotoMapper {
    const CATEGORY_CARS = 29;

    const FUEL_TYPE_MAP = [
        'benzyna' => 'petrol',
        'petrol' => 'petrol',
        'gasoline' => 'petrol',
        'diesel' => 'diesel',
        'hybryda' => 'hybrid',
        'hybrid' => 'hybrid',
        'hev' => 'hybrid',
        'plug-in' => 'petrol',
        'phev' => 'petrol',
        'elektryczny' => 'electric',
        'electric' => 'electric',
        'ev' => 'electric',
    ];

    const BODY_TYPE_MAP = [
        'suv' => 'suv',
        'crossover' => 'suv',
        'kompakt' => 'compact',
        'compact' => 'compact',
        'sedan' => 'sedan',
        'limuzyna' => 'sedan',
        'kombi' => 'estate',
        'estate' => 'estate',
        'wagon' => 'estate',
        'hatchback' => 'hatchback',
        'liftback' => 'coupe',
        'coupe' => 'coupe',
        'sportowy' => 'coupe',
        'cabrio' => 'cabrio',
        'kabriolet' => 'cabrio',
        'roadster' => 'cabrio',
        'van' => 'minivan',
        'minibus' => 'minivan',
        'mpv' => 'minivan',
        'minivan' => 'minivan',
        'pickup' => 'pickup',
        'małe' => 'city-car',
        'small' => 'city-car',
        'miejskie' => 'city-car',
        'city' => 'city-car',
    ];

    const GEARBOX_MAP = [
        'automatic' => 'automatic',
        'automata' => 'automatic',
        'automat' => 'automatic',
        'manual' => 'manual',
        'manualna' => 'manual',
    ];

    const COLOR_MAP = [
        'biały' => 'white',
        'white' => 'white',
        'czarny' => 'black',
        'black' => 'black',
        'szary' => 'grey',
        'grafitowy' => 'grey',
        'gray' => 'grey',
        'grey' => 'grey',
        'srebrny' => 'silver',
        'silver' => 'silver',
        'niebieski' => 'blue',
        'granatowy' => 'navy-blue',
        'blue' => 'blue',
        'navy' => 'navy-blue',
        'czerwony' => 'red',
        'bordowy' => 'burgundy',
        'red' => 'red',
        'burgundy' => 'burgundy',
        'zielony' => 'green',
        'green' => 'green',
        'brązowy' => 'brown',
        'brown' => 'brown',
        'pomarańczowy' => 'orange',
        'orange' => 'orange',
        'beżowy' => 'beige',
        'beige' => 'beige',
        'złoty' => 'gold',
        'gold' => 'gold',
        'żółty' => 'yellow',
        'yellow' => 'yellow',
        'fioletowy' => 'violet',
        'violet' => 'violet',
        'purple' => 'violet',
        'różowy' => 'pink',
        'pink' => 'pink',
    ];

    private $dealerSettings;

    public function __construct($dealerSettings = []) {
        $this->dealerSettings = $dealerSettings;
    }

    public function map($dolCar) {
        $carData = $dolCar->carData ?? $dolCar;
        $vin = $carData->vin ?? ($dolCar->id ?? null);

        if (empty($vin)) {
            return new \WP_Error('otomoto_map_error', __('No car VIN', 'partners-site_v2'));
        }

        // Required dealer settings
        $requiredSettings = ['region_id', 'city_id', 'district_id', 'latitude', 'longitude'];
        $missingSettings = [];
        foreach ($requiredSettings as $key) {
            $fullKey = 'otomoto_' . $key;
            if (!isset($this->dealerSettings[$fullKey]) || $this->dealerSettings[$fullKey] === '' || $this->dealerSettings[$fullKey] === null) {
                $missingSettings[] = $key;
            }
        }
        if (!empty($missingSettings)) {
            return new \WP_Error('otomoto_map_error', __('No Otomoto location settings', 'partners-site_v2') . ': ' . implode(', ', $missingSettings));
        }

        $title = $this->buildTitle($carData);
        $description = $this->buildDescription($carData);

        $model = strtolower($carData->model ?? '');
        $version = $carData->version ?? '';
        $year = $carData->year ?? ($carData->productionYear ?? null);
        $mileage = $carData->mileage ?? 0;
        $color = $this->mapColor($carData->color ?? '');
        $fuelType = $this->mapFuelType($carData->fuelType ?? '', $carData->engine ?? '');
        $bodyType = $this->mapBodyType($carData->model ?? '', $carData->bodyType ?? '');
        $gearbox = $this->mapGearbox($carData->gearboxVersion ?? '');
        $engineCapacity = $this->resolveEngineCapacity($carData);
        $enginePower = $this->resolveEnginePower($carData);
        $price = $dolCar->price->grossPrice ?? null;

        $params = [
            'make' => 'volvo',
            'model' => $model,
            'version' => $version,
            'year' => intval($year),
            'mileage' => intval($mileage),
            'fuel_type' => $fuelType,
            'body_type' => $bodyType,
            'color' => $color,
            'gearbox' => $gearbox,
            'door_count' => 5,
            'nr_seats' => 5,
            'vin' => $vin,
            'new_used' => 'new',
            'registered' => 0,
            'cepik_authorization' => 1,
        ];

        if (!empty($engineCapacity)) {
            $params['engine_capacity'] = intval($engineCapacity);
        }
        if (!empty($enginePower)) {
            $params['engine_power'] = intval($enginePower);
        }

        if (!empty($price)) {
            $params['price'] = [
                '0' => 'price',
                '1' => intval($price),
                'currency' => 'PLN',
                'gross_net' => 'gross',
            ];
        }

        $payload = [
            'title' => $title,
            'description' => $description,
            'category_id' => self::CATEGORY_CARS,
            'region_id' => intval($this->dealerSettings['otomoto_region_id']),
            'city_id' => intval($this->dealerSettings['otomoto_city_id']),
            'district_id' => intval($this->dealerSettings['otomoto_district_id']),
            'coordinates' => [
                'latitude' => floatval($this->dealerSettings['otomoto_latitude']),
                'longitude' => floatval($this->dealerSettings['otomoto_longitude']),
            ],
            'contact' => [
                'person' => $this->dealerSettings['otomoto_contact_person'] ?? 'Dealer',
            ],
            'params' => $params,
            'advertiser_type' => 'business',
        ];

        return $payload;
    }

    private function buildTitle($carData) {
        $parts = [];
        $model = $carData->model ?? '';
        $version = $carData->version ?? '';
        $year = $carData->year ?? ($carData->productionYear ?? '');
        if ($model) $parts[] = 'Volvo ' . $model;
        if ($version) $parts[] = $version;
        if ($year) $parts[] = $year;
        return implode(' ', $parts) ?: 'Volvo';
    }

    private function buildDescription($carData) {
        $parts = [];
        $model = $carData->model ?? '';
        $version = $carData->version ?? '';
        $engine = $carData->engine ?? '';
        $year = $carData->year ?? ($carData->productionYear ?? '');
        $color = $carData->color ?? '';

        if ($model) $parts[] = 'Model: Volvo ' . $model;
        if ($version) $parts[] = 'Wersja: ' . $version;
        if ($engine) $parts[] = 'Silnik: ' . $engine;
        if ($year) $parts[] = 'Rok produkcji: ' . $year;
        if ($color) $parts[] = 'Kolor: ' . $color;

        return implode("\n", $parts) ?: 'Volvo';
    }

    private function resolveEngineCapacity($carData) {
        if (!empty($carData->engineCapacityCc)) {
            return $carData->engineCapacityCc;
        }
        $engine = strtolower($carData->engine ?? '');
        if (preg_match('/(\d+\.?\d*)\s*l/i', $engine, $matches)) {
            return floatval($matches[1]) * 1000;
        }
        $fuelType = strtolower($carData->fuelType ?? '');
        if (in_array($fuelType, ['diesel', 'benzyna', 'petrol', 'gasoline'])) {
            return 2000;
        }
        if (in_array($fuelType, ['electric', 'elektryczny', 'plug-in', 'phev', 'hybryda', 'hybrid'])) {
            return 0;
        }
        if (strpos($engine, 'recharge') !== false || strpos($engine, 'single') !== false || strpos($engine, 'twin') !== false) {
            return 0;
        }
        return null;
    }

    private function resolveEnginePower($carData) {
        if (!empty($carData->powerHp)) {
            return $carData->powerHp;
        }
        $engine = strtolower($carData->engine ?? '');
        if (strpos($engine, 'b5') !== false) return 250;
        if (strpos($engine, 'b4') !== false) return 197;
        if (strpos($engine, 'b6') !== false) return 300;
        if (strpos($engine, 't5') !== false) return 250;
        if (strpos($engine, 't6') !== false) return 300;
        if (strpos($engine, 't8') !== false) return 455;
        if (strpos($engine, 'd4') !== false) return 190;
        if (strpos($engine, 'd5') !== false) return 235;
        if (strpos($engine, 'single') !== false) return 272;
        if (strpos($engine, 'twin') !== false) return 428;
        if (strpos($engine, 'recharge') !== false) return 231;
        return null;
    }

    private function mapFuelType($fuelType, $engine) {
        $fuelType = strtolower(trim($fuelType));
        if (isset(self::FUEL_TYPE_MAP[$fuelType])) {
            return self::FUEL_TYPE_MAP[$fuelType];
        }
        $engine = strtolower($engine);
        if (strpos($engine, 'electric') !== false || strpos($engine, 'single') !== false || strpos($engine, 'twin') !== false || strpos($engine, 'recharge') !== false) {
            return 'electric';
        }
        if (strpos($engine, 'b5') !== false || strpos($engine, 'b4') !== false || strpos($engine, 'b6') !== false || strpos($engine, 't5') !== false || strpos($engine, 't6') !== false || strpos($engine, 't8') !== false) {
            return 'hybrid';
        }
        if (strpos($engine, 'd4') !== false || strpos($engine, 'd5') !== false) {
            return 'diesel';
        }
        return 'petrol';
    }

    private function mapBodyType($model, $bodyType) {
        $bodyType = strtolower(trim($bodyType));
        if (isset(self::BODY_TYPE_MAP[$bodyType])) {
            return self::BODY_TYPE_MAP[$bodyType];
        }
        $model = strtolower($model);
        if (strpos($model, 'xc') !== false) return 'suv';
        if (strpos($model, 'ex') !== false) return 'suv';
        if (strpos($model, 'ec') !== false) return 'suv';
        if (strpos($model, 'v40') !== false) return 'hatchback';
        if (strpos($model, 'v') !== false) return 'estate';
        if (strpos($model, 's') !== false) return 'sedan';
        return 'suv';
    }

    private function mapGearbox($gearbox) {
        $gearbox = strtolower(trim($gearbox));
        if (isset(self::GEARBOX_MAP[$gearbox])) {
            return self::GEARBOX_MAP[$gearbox];
        }
        return 'automatic';
    }

    private function mapColor($color) {
        $color = strtolower(trim($color));
        if (isset(self::COLOR_MAP[$color])) {
            return self::COLOR_MAP[$color];
        }
        return 'other';
    }
}
