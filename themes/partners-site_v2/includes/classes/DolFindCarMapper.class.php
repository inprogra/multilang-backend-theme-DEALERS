<?php

namespace Classes;

class DolFindCarMapper {
    private $volvo_token = null;

    const VOLVO_MAKE_ID_PROD = '8d32213a-cd73-42d3-a034-29d16d3f6762';
    const VOLVO_MAKE_ID_UAT  = '3df74537-4850-3d4e-a822-77d2d0420de5';

    public function __construct() {
        $this->loadVolvoToken();
    }

    private function getVolvoMakeId() {
        $env_value = defined('WP_FINDCAR') ? WP_FINDCAR : (isset($_ENV['WP_FINDCAR']) ? $_ENV['WP_FINDCAR'] : '');
        $is_dev = ($env_value === 'stage' || $env_value === 'dev' || $env_value === 'uat');
        return $is_dev ? self::VOLVO_MAKE_ID_UAT : self::VOLVO_MAKE_ID_PROD;
    }

    private function loadVolvoToken() {
        $token_path = WP_CONTENT_DIR . '/../wikicars/token.json';
        if (!file_exists($token_path)) {
            $token_path = dirname(ABSPATH) . '/wikicars/token.json';
        }
        if (file_exists($token_path)) {
            $data = json_decode(file_get_contents($token_path), true);
            if (!empty($data['access_token'])) {
                $this->volvo_token = $data['access_token'];
            }
        }
    }

    /**
     * Map DOL car object to FindCar listing payload
     *
     * @param object $dolCar Raw car object from DOL API
     * @return array|\WP_Error
     */
    public function map($dolCar) {
        $carData = $dolCar->carData ?? $dolCar;
        $vin = $carData->vin ?? ($dolCar->id ?? null);

        if (empty($vin)) {
            return new \WP_Error('findcar_map_error', 'Brak VIN samochodu');
        }

        // Try to enrich from Volvo Partner API
        $enriched = $this->enrichFromVolvoApi($vin);

        $listing = [];
        $listing['makeId'] = $this->getVolvoMakeId();

        // Model
        $model = $carData->model ?? null;
        if (!empty($model)) {
            $listing['model'] = ['originalName' => $model];
        } else {
            return new \WP_Error('findcar_map_error', 'Brak modelu samochodu');
        }

        // Production Year
        $year = $carData->year ?? ($carData->productionYear ?? null);
        if (!empty($year)) {
            $listing['productionYear'] = intval($year);
        }

        // Price
        $price = $dolCar->price->grossPrice ?? null;
        if (!empty($price)) {
            $listing['priceOfferPln100'] = intval($price * 100);
        }

        // Images
        $images = [];
        if (!empty($dolCar->images) && is_array($dolCar->images)) {
            foreach ($dolCar->images as $img) {
                if (!empty($img->url)) {
                    $images[] = $img->url;
                }
            }
        }
        if (!empty($images)) {
            $listing['mediaUrls'] = $images;
        } else {
            return new \WP_Error('findcar_map_error', 'Brak zdjęć samochodu');
        }

        // Content Body (description)
        $description = $this->buildDescription($carData);
        if (!empty($description)) {
            $listing['contentBody'] = $description;
        }

        // Engine Name
        $engine = $carData->engine ?? null;
        if (!empty($engine)) {
            $listing['engineName'] = $engine;
        }

        // Engine Capacity
        $engineCapacity = $this->resolveEngineCapacity($carData, $enriched);
        if ($engineCapacity !== null) {
            $listing['engineCapacityCc'] = intval($engineCapacity);
        }

        // Engine Power
        $enginePower = $this->resolveEnginePower($carData, $enriched);
        if ($enginePower !== null) {
            $listing['enginePowerHp'] = intval($enginePower);
        }

        // Fuel Type
        $fuelType = $this->resolveFuelType($carData, $enriched);
        if (!empty($fuelType)) {
            $listing['fuelType'] = $this->mapCodedValue($fuelType, \FindCar_Data_Mapper::FUEL_TYPE_MAP, 11000);
        }

        // Color
        $color = $carData->color ?? null;
        if (!empty($color)) {
            $listing['color'] = $this->mapCodedValue($color, \FindCar_Data_Mapper::COLOR_MAP, 17000);
        }

        // Body Type
        $bodyType = $this->resolveBodyType($carData, $enriched);
        if (!empty($bodyType)) {
            $listing['bodyType'] = $this->mapCodedValue($bodyType, \FindCar_Data_Mapper::BODY_TYPE_MAP, 14000);
        }

        // Transmission
        $transmission = $this->resolveTransmission($carData, $enriched);
        if (!empty($transmission)) {
            $listing['transmission'] = $this->mapCodedValue($transmission, \FindCar_Data_Mapper::TRANSMISSION_MAP, 12000);
        }

        // Drive Type
        $driveType = $this->resolveDriveType($enriched);
        if (!empty($driveType)) {
            $listing['driveType'] = $this->mapCodedValue($driveType, \FindCar_Data_Mapper::DRIVE_TYPE_MAP, 13000);
        }

        // Mileage
        $mileage = $carData->mileage ?? null;
        if (!empty($mileage)) {
            $listing['mileageKm'] = intval($mileage);
        }

        // VIN
        if (!empty($vin)) {
            $listing['vin'] = $vin;
        }

        // Version
        $version = $carData->version ?? null;
        if (!empty($version)) {
            $listing['versionName'] = $version;
        }

        // Inventory Type - always brand new
        $listing['inventoryType'] = $this->mapCodedValue('new', \FindCar_Data_Mapper::INVENTORY_TYPE_MAP, 19001);

        // Seats
        $listing['seats'] = 5;

        // Validate required fields
        $missing = [];
        $required = ['contentBody', 'engineCapacityCc', 'enginePowerHp', 'bodyType'];
        foreach ($required as $field) {
            if (!isset($listing[$field]) || $listing[$field] === '' || $listing[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            return new \WP_Error('findcar_map_error', 'Brak wymaganych pól: ' . implode(', ', $missing) . '. Wymagane jest wzbogacenie danych przez API Volvo (token może być niedostępny lub VIN nieznany).');
        }

        return $listing;
    }

    private function enrichFromVolvoApi($vin) {
        if (empty($this->volvo_token)) {
            return [];
        }

        $url = 'https://gw.partner.api.volvocars.biz/vehicle/vin/' . rawurlencode($vin);
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->volvo_token,
                'Api-version' => '2.0',
                'Ocp-Apim-Subscription-Key' => '3cf34637fef3402b85c4cfb0210a5a5d',
            ],
            'timeout' => 15,
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return [];
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) {
            return [];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['responseDetails']['vehicle'])) {
            return [];
        }

        return $body['responseDetails']['vehicle'];
    }

    private function buildDescription($carData) {
        $parts = [];
        $model = $carData->model ?? '';
        $version = $carData->version ?? '';
        $engine = $carData->engine ?? '';
        $year = $carData->year ?? ($carData->productionYear ?? '');

        if ($model) $parts[] = $model;
        if ($version) $parts[] = $version;
        if ($engine) $parts[] = $engine;
        if ($year) $parts[] = $year;

        $desc = implode(' ', $parts);
        return $desc ?: null;
    }

    private function resolveEngineCapacity($carData, $enriched) {
        // First try DOL data
        if (!empty($carData->engineCapacityCc)) {
            return $carData->engineCapacityCc;
        }

        // Try enriched data
        if (!empty($enriched['engineSize'])) {
            return intval($enriched['engineSize']);
        }

        // Try to parse from engine description
        $engine = $carData->engine ?? '';
        if (preg_match('/(\d+\.?\d*)\s*l/i', $engine, $matches)) {
            return floatval($matches[1]) * 1000;
        }

        // Default for known engine types (FindCar UAT rejects 0 and returns 500)
        $fuelType = strtolower($this->resolveFuelType($carData, $enriched));
        if (in_array($fuelType, ['diesel', 'benzyna', 'petrol', 'gasoline', 'electric', 'elektryczny', 'plug-in', 'phev', 'hybryda', 'hybrid'])) {
            return 2000;
        }

        return null;
    }

    private function resolveEnginePower($carData, $enriched) {
        if (!empty($carData->powerHp)) {
            return $carData->powerHp;
        }
        if (!empty($enriched['maxPowerHp'])) {
            return $enriched['maxPowerHp'];
        }
        if (!empty($enriched['maxElectricPowerHp'])) {
            return $enriched['maxElectricPowerHp'];
        }
        // Try to parse from engine name for known patterns
        $engine = strtolower($carData->engine ?? '');
        if (strpos($engine, 'b5') !== false) return 250;
        if (strpos($engine, 'b4') !== false) return 197;
        if (strpos($engine, 'b6') !== false) return 300;
        if (strpos($engine, 't5') !== false) return 250;
        if (strpos($engine, 't6') !== false) return 300;
        if (strpos($engine, 't8') !== false) return 455;
        if (strpos($engine, 'd4') !== false) return 190;
        if (strpos($engine, 'd5') !== false) return 235;
        if (strpos($engine, 'single') !== false) return 272; // EX30 Single
        if (strpos($engine, 'twin') !== false) return 428; // EX30 Twin
        if (strpos($engine, 'recharge') !== false) return 231; // EC40 / XC40 Recharge single motor
        return null;
    }

    private function resolveFuelType($carData, $enriched) {
        if (!empty($carData->fuelType)) {
            return $carData->fuelType;
        }
        if (!empty($enriched['fuelType'])) {
            return $enriched['fuelType'];
        }
        if (!empty($enriched['fuelTypeDescription'])) {
            return $enriched['fuelTypeDescription'];
        }
        // Infer from engine name
        $engine = strtolower($carData->engine ?? '');
        if (strpos($engine, 'electric') !== false || strpos($engine, 'single') !== false || strpos($engine, 'twin') !== false || strpos($engine, 'recharge') !== false) {
            return 'electric';
        }
        if (strpos($engine, 'b5') !== false || strpos($engine, 'b4') !== false || strpos($engine, 'b6') !== false) {
            return 'hybrid';
        }
        if (strpos($engine, 't5') !== false || strpos($engine, 't6') !== false || strpos($engine, 't8') !== false) {
            return 'hybrid';
        }
        if (strpos($engine, 'd4') !== false || strpos($engine, 'd5') !== false) {
            return 'diesel';
        }
        return null;
    }

    private function resolveBodyType($carData, $enriched) {
        if (!empty($carData->bodyType)) {
            return $carData->bodyType;
        }
        if (!empty($enriched['bodyTypeDescription'])) {
            return $enriched['bodyTypeDescription'];
        }
        // Infer from model
        $model = strtolower($carData->model ?? '');
        if (strpos($model, 'xc') !== false) return 'suv';
        if (strpos($model, 'ex') !== false) return 'suv';
        if (strpos($model, 'ec') !== false) return 'suv';
        if (strpos($model, 'v40') !== false) return 'hatchback';
        if (strpos($model, 'v') !== false) return 'estate';
        if (strpos($model, 's') !== false) return 'sedan';
        return null;
    }

    private function resolveTransmission($carData, $enriched) {
        if (!empty($carData->gearboxVersion)) {
            return $carData->gearboxVersion;
        }
        if (!empty($enriched['gearboxCode'])) {
            return $enriched['gearboxCode'] === 'D' ? 'automatic' : 'manual';
        }
        if (!empty($enriched['gearboxDescription'])) {
            return $enriched['gearboxDescription'];
        }
        // Most new Volvos are automatic
        return 'automatic';
    }

    private function resolveDriveType($enriched) {
        if (!empty($enriched['driveType'])) {
            return $enriched['driveType'];
        }
        if (!empty($enriched['driveDescription'])) {
            return $enriched['driveDescription'];
        }
        return 'awd';
    }

    private function mapCodedValue($value, $map, $default_code) {
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
}
