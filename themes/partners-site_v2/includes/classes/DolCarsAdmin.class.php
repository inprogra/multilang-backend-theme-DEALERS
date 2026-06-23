<?php

namespace Classes;

class DolCarsAdmin {
    public function __construct() {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueOtomotoLocationScripts']);
        add_action('wp_ajax_fetch_dol_cars', [$this, 'ajaxFetchDolCars']);
        add_action('wp_ajax_send_dol_car_to_findcar', [$this, 'ajaxSendToFindcar']);
        add_action('wp_ajax_send_dol_car_to_otomoto', [$this, 'ajaxSendToOtomoto']);
        add_action('wp_ajax_stop_dol_car_on_findcar', [$this, 'ajaxStopOnFindcar']);
        add_action('wp_ajax_resume_dol_car_on_findcar', [$this, 'ajaxResumeOnFindcar']);
        add_action('wp_ajax_stop_dol_car_on_otomoto', [$this, 'ajaxStopOnOtomoto']);
        add_action('wp_ajax_resume_dol_car_on_otomoto', [$this, 'ajaxResumeOnOtomoto']);
        add_action('wp_ajax_otomoto_test_connection', [$this, 'ajaxOtomotoTestConnection']);
        add_action('wp_ajax_otomoto_fetch_regions', [$this, 'ajaxOtomotoFetchRegions']);
        add_action('wp_ajax_otomoto_fetch_cities', [$this, 'ajaxOtomotoFetchCities']);
        add_action('wp_ajax_otomoto_fetch_districts', [$this, 'ajaxOtomotoFetchDistricts']);
    }

    public function addAdminMenu() {
        add_submenu_page(
            'edit.php?post_type=stock-car',
            'Marketplace',
            'Marketplace',
            'edit_posts',
            'dol-cars',
            [$this, 'renderAdminPage']
        );
    }

    public function enqueueScripts($hook) {
        if ($hook !== 'stock-car_page_dol-cars') {
            return;
        }

        wp_enqueue_script(
            'dol-cars-admin',
            get_template_directory_uri() . '/includes/views/admin/dol-cars.js',
            ['jquery'],
            null,
            true
        );

        $dealerId = $this->getCurrentDealerId();
        $initialCars = [];
        if ($dealerId) {
            $initialCars = $this->getInitialCarsData($dealerId);
        }

        wp_localize_script('dol-cars-admin', 'dolCarsAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dol_cars_nonce'),
            'initial_cars' => $initialCars,
        ]);

        wp_enqueue_style(
            'dol-cars-admin-css',
            get_template_directory_uri() . '/includes/views/admin/dol-cars.css',
            [],
            null
        );
    }

    public function renderAdminPage() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Cars — available for FIND CAR / OTOMOTO', 'partners-site_v2'); ?></h1>
            <p><?php esc_html_e('Cars from DOL (Dealer Online) and used cars from inventory available for listing on platforms FIND CAR i OTOMOTO.', 'partners-site_v2'); ?></p>

            <div id="dol-cars-controls">
                <button id="refresh-dol-cars" class="button button-secondary"><?php esc_html_e('Refresh the list', 'partners-site_v2'); ?></button>
                <span class="spinner" id="dol-cars-spinner"></span>
            </div>

            <div id="dol-cars-message"></div>

            <table class="wp-list-table widefat fixed striped" id="dol-cars-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Source', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('Model', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('Version', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('Color', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('VIN', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('Year', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('FIND CAR', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('OTOMOTO', 'partners-site_v2'); ?></th>
                        <th><?php esc_html_e('Actions', 'partners-site_v2'); ?></th>
                    </tr>
                </thead>
                <tbody id="dol-cars-tbody">
                    <tr>
                        <td colspan="9"><?php esc_html_e('Click "Refresh list" to load the cars.', 'partners-site_v2'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function ajaxFetchDolCars() {
        check_ajax_referer('dol_cars_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $dealerId = $this->getCurrentDealerId();

        if (!$dealerId) {
            wp_send_json_error(['message' => __('No dealer ID found for the current page', 'partners-site_v2')]);
        }

        $carDict = new CarDictionary(new \GuzzleHttp\Client());
        $response = json_decode($carDict->getDolCars());

        if (!$response || !isset($response->content)) {
            wp_send_json_error(['message' => __('Failed to retrieve cars from DOL', 'partners-site_v2')]);
        }

        $filtered = $this->filterCarsByDealer($response->content, $dealerId);
        $filtered = $this->filterCarsByExposes($filtered);
        $sentCars = $this->getSentCars($dealerId);

        $dolCars = [];
        foreach ($filtered as $car) {
            $carId = $car->id;
            $car->source = 'DOL';
            $car->findcar_sent = isset($sentCars[$carId]['findcar']) ? $sentCars[$carId]['findcar'] : false;
            $car->findcar_status = $sentCars[$carId]['findcar_data']['status'] ?? null;
            $car->findcar_listing_url = $sentCars[$carId]['findcar_data']['listing_url'] ?? null;
            $car->otomoto_sent = isset($sentCars[$carId]['otomoto']) ? $sentCars[$carId]['otomoto'] : false;
            $car->otomoto_status = $sentCars[$carId]['otomoto_data']['status'] ?? null;
            $car->otomoto_advert_url = $sentCars[$carId]['otomoto_data']['advert_url'] ?? null;
            $dolCars[$carId] = $car;
        }

        $this->saveDolCars($dealerId, $dolCars);

        $usedCars = $this->getUsedStockCars($dealerId);

        $allCars = array_merge(array_values($dolCars), $usedCars);

        wp_send_json_success([
            'cars' => $allCars,
            'total' => count($allCars),
            'dol_count' => count($dolCars),
            'used_count' => count($usedCars)
        ]);
    }

    public function ajaxSendToFindcar() {
        check_ajax_referer('dol_cars_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $carData = json_decode(stripslashes($_POST['car_data'] ?? '[]'), true);

        if (empty($carData) || !isset($carData['id'])) {
            wp_send_json_error(['message' => __('No car data provided', 'partners-site_v2')]);
        }

        $dealerId = $this->getCurrentDealerId();

        $findcarEnabled = get_field('findcar_enabled', 'options-dealer');
        if (!$findcarEnabled) {
            wp_send_json_error(['message' => __('FindCar integration is not enabled for this dealer', 'partners-site_v2')]);
        }

        $apiKey = get_field('findcar_api_key', 'options-dealer');
        $locationId = get_field('findcar_location_id', 'options-dealer');
        $locationToken = get_field('findcar_location_token', 'options-dealer');
        $inventoryBrandNew = get_field('findcar_inventory_brand_new', 'options-dealer');

        if (empty($apiKey) || empty($locationId)) {
            wp_send_json_error(['message' => __('FindCar login credentials are not configured', 'partners-site_v2')]);
        }

        $savedCars = $this->getSavedDolCars($dealerId);
        $carId = $carData['id'];
        $dolCar = $savedCars[$carId] ?? null;

        if (!$dolCar) {
            wp_send_json_error(['message' => __('Car not found in saved DOL cars', 'partners-site_v2')]);
        }

        $dolCar = is_object($dolCar) ? $dolCar : json_decode(json_encode($dolCar));

        $mapper = new DolFindCarMapper();
        $listingData = $mapper->map($dolCar);

        if (is_wp_error($listingData)) {
            wp_send_json_error(['message' => __('Mapping failed', 'partners-site_v2') . ': ' . $listingData->get_error_message()]);
        }

        $client = new \FindCar_API_Client($apiKey, $locationToken);

        $partnerListingId = 'dol-' . $carId;
        $targetLocationId = !empty($inventoryBrandNew) ? $inventoryBrandNew : $locationId;

        $listingDataForApi = $listingData;
        unset($listingDataForApi['inventoryType']);

        $result = $client->create_listing_brand_new($targetLocationId, $partnerListingId, $listingDataForApi);

        $log_file = '/www/wwwroot/main-stage.volvotest.pl/web/debug-dol-findcar.log';
        $log_msg = '[' . date('Y-m-d H:i:s') . '] DOL FindCar API response: carId=' . $carId . ', is_wp_error=' . (is_wp_error($result) ? 'yes' : 'no');
        if (is_wp_error($result)) {
            $log_msg .= ', error=' . $result->get_error_message();
        } else {
            $log_msg .= ', response=' . json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        $log_msg .= "\n";
        @file_put_contents($log_file, $log_msg, FILE_APPEND);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('FindCar API error', 'partners-site_v2') . ': ' . $result->get_error_message()]);
        }

        $this->markCarAsSent($dealerId, $carId, 'findcar', [
            'listing_id' => $result['id'] ?? null,
            'listing_number' => $result['publicListingNumber'] ?? null,
            'listing_url' => $result['listingUrl'] ?? null,
            'status' => 'active',
        ]);

        wp_send_json_success([
            'message' => __('The car has been sent to FindCar', 'partners-site_v2'),
            'listing_number' => $result['publicListingNumber'] ?? null,
            'listing_url' => $result['listingUrl'] ?? null,
        ]);
    }

    public function ajaxSendToOtomoto() {
        check_ajax_referer('dol_cars_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $carData = json_decode(stripslashes($_POST['car_data'] ?? '[]'), true);

        if (empty($carData) || !isset($carData['id'])) {
            wp_send_json_error(['message' => __('No car data provided', 'partners-site_v2')]);
        }

        $dealerId = $this->getCurrentDealerId();

        $otomotoSettings = get_field('otomoto_settings', 'options-dealer');
        if (!$otomotoSettings || empty($otomotoSettings['otomoto_enabled'])) {
            wp_send_json_error(['message' => __('Otomoto integration is not enabled for this dealer', 'partners-site_v2')]);
        }

        if (empty($otomotoSettings['otomoto_username']) || empty($otomotoSettings['otomoto_password']) || empty($otomotoSettings['otomoto_client_id']) || empty($otomotoSettings['otomoto_client_secret'])) {
            wp_send_json_error(['message' => __('Otomoto login credentials are not configured', 'partners-site_v2')]);
        }

        $savedCars = $this->getSavedDolCars($dealerId);
        $carId = $carData['id'];
        $dolCar = $savedCars[$carId] ?? null;

        if (!$dolCar) {
            wp_send_json_error(['message' => __('Car not found in saved DOL cars', 'partners-site_v2')]);
        }

        $dolCar = is_object($dolCar) ? $dolCar : json_decode(json_encode($dolCar));

        $mapper = new DolOtomotoMapper($otomotoSettings);
        $payload = $mapper->map($dolCar);

        if (is_wp_error($payload)) {
            wp_send_json_error(['message' => __('Mapping failed', 'partners-site_v2') . ': ' . $payload->get_error_message()]);
        }

        $client = new CarOtoMoto(
            $otomotoSettings['otomoto_username'],
            $otomotoSettings['otomoto_password'],
            $otomotoSettings['otomoto_client_id'] . ':' . $otomotoSettings['otomoto_client_secret']
        );

        if (!$client->isAuthenticated()) {
            wp_send_json_error(['message' => __('Otomoto authentication failed', 'partners-site_v2')]);
        }

        $images = [];
        if (!empty($dolCar->images) && is_array($dolCar->images)) {
            foreach ($dolCar->images as $img) {
                if (!empty($img->url)) {
                    $images[] = $img->url;
                }
            }
        }

        if (!empty($images)) {
            $collection = $client->createImageCollection();
            if (is_wp_error($collection)) {
                wp_send_json_error(['message' => __('Error creating Otomoto photo collection', 'partners-site_v2') . ': ' . $collection->get_error_message()]);
            }

            $collectionId = $collection['id'] ?? null;
            if ($collectionId) {
                foreach ($images as $imageUrl) {
                    $uploadResult = $client->addImageToCollection($collectionId, $imageUrl);
                    if (is_wp_error($uploadResult)) {
                        error_log('Otomoto image upload failed: ' . $uploadResult->get_error_message());
                    }
                }
                $payload['image_collection_id'] = $collectionId;
            }
        }

        $log_file = '/www/wwwroot/main-stage.volvotest.pl/web/debug-dol-otomoto.log';
        $log_msg = '[' . date('Y-m-d H:i:s') . '] DOL Otomoto send attempt: carId=' . $carId . ', payload=' . json_encode($payload, JSON_PARTIAL_OUTPUT_ON_ERROR) . "\n";
        @file_put_contents($log_file, $log_msg, FILE_APPEND);

        $result = $client->createAdvert($payload);

        $log_msg = '[' . date('Y-m-d H:i:s') . '] DOL Otomoto API response: carId=' . $carId . ', is_wp_error=' . (is_wp_error($result) ? 'yes' : 'no');
        if (is_wp_error($result)) {
            $log_msg .= ', error=' . $result->get_error_message();
        } else {
            $log_msg .= ', response=' . json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        $log_msg .= "\n";
        @file_put_contents($log_file, $log_msg, FILE_APPEND);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('Otomoto API error', 'partners-site_v2') . ': ' . $result->get_error_message()]);
        }

        $this->markCarAsSent($dealerId, $carId, 'otomoto', [
            'advert_id' => $result['id'] ?? null,
            'advert_url' => $result['url'] ?? null,
            'status' => 'active',
        ]);

        wp_send_json_success([
            'message' => __('The car has been sent to Otomoto', 'partners-site_v2'),
            'advert_id' => $result['id'] ?? null,
            'advert_url' => $result['url'] ?? null,
        ]);
    }

    public function ajaxStopOnFindcar() {
        check_ajax_referer('dol_cars_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $carId = sanitize_text_field($_POST['car_id'] ?? '');
        if (empty($carId)) {
            wp_send_json_error(['message' => __('No car ID provided', 'partners-site_v2')]);
        }

        $dealerId = $this->getCurrentDealerId();
        $sentCars = $this->getSentCars($dealerId);
        $findcarData = $sentCars[$carId]['findcar_data'] ?? [];

        if (empty($findcarData['listing_id'])) {
            wp_send_json_error(['message' => __('No FindCar listing ID', 'partners-site_v2')]);
        }

        $apiKey = get_field('findcar_api_key', 'options-dealer');
        $locationId = get_field('findcar_location_id', 'options-dealer');
        $locationToken = get_field('findcar_location_token', 'options-dealer');
        $inventoryBrandNew = get_field('findcar_inventory_brand_new', 'options-dealer');

        if (empty($apiKey) || empty($locationId)) {
            wp_send_json_error(['message' => __('FindCar login credentials are not configured', 'partners-site_v2')]);
        }

        $client = new \FindCar_API_Client($apiKey, $locationToken);
        $partnerListingId = 'dol-' . $carId;
        $targetLocationId = !empty($inventoryBrandNew) ? $inventoryBrandNew : $locationId;

        $result = $client->delete_listing($targetLocationId, $partnerListingId);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('Error while pausing the FindCar listing', 'partners-site_v2') . ': ' . $result->get_error_message()]);
        }

        $this->markCarAsStopped($dealerId, $carId, 'findcar');

        wp_send_json_success(['message' => __('The listing has been paused on FindCar', 'partners-site_v2')]);
    }

    public function ajaxResumeOnFindcar() {
        check_ajax_referer('dol_cars_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $carId = sanitize_text_field($_POST['car_id'] ?? '');
        if (empty($carId)) {
            wp_send_json_error(['message' => __('No car ID provided', 'partners-site_v2')]);
        }

        $dealerId = $this->getCurrentDealerId();
        $savedCars = $this->getSavedDolCars($dealerId);
        $dolCar = $savedCars[$carId] ?? null;

        if (!$dolCar) {
            wp_send_json_error(['message' => __('Car not found in saved DOL cars', 'partners-site_v2')]);
        }

        $findcarEnabled = get_field('findcar_enabled', 'options-dealer');
        if (!$findcarEnabled) {
            wp_send_json_error(['message' => __('FindCar integration is not enabled for this dealer', 'partners-site_v2')]);
        }

        $apiKey = get_field('findcar_api_key', 'options-dealer');
        $locationId = get_field('findcar_location_id', 'options-dealer');
        $locationToken = get_field('findcar_location_token', 'options-dealer');
        $inventoryBrandNew = get_field('findcar_inventory_brand_new', 'options-dealer');

        if (empty($apiKey) || empty($locationId)) {
            wp_send_json_error(['message' => __('FindCar login credentials are not configured', 'partners-site_v2')]);
        }

        $dolCar = is_object($dolCar) ? $dolCar : json_decode(json_encode($dolCar));

        $mapper = new DolFindCarMapper();
        $listingData = $mapper->map($dolCar);

        if (is_wp_error($listingData)) {
            wp_send_json_error(['message' => __('Mapping failed', 'partners-site_v2') . ': ' . $listingData->get_error_message()]);
        }

        $client = new \FindCar_API_Client($apiKey, $locationToken);
        $partnerListingId = 'dol-' . $carId;
        $targetLocationId = !empty($inventoryBrandNew) ? $inventoryBrandNew : $locationId;

        $listingDataForApi = $listingData;
        unset($listingDataForApi['inventoryType']);

        $result = $client->create_listing_brand_new($targetLocationId, $partnerListingId, $listingDataForApi);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('FindCar API error during resumption', 'partners-site_v2') . ': ' . $result->get_error_message()]);
        }

        $this->markCarAsSent($dealerId, $carId, 'findcar', [
            'listing_id' => $result['id'] ?? null,
            'listing_number' => $result['publicListingNumber'] ?? null,
            'listing_url' => $result['listingUrl'] ?? null,
            'status' => 'active',
        ]);

        wp_send_json_success([
            'message' => __('The listing has been resumed on FindCar', 'partners-site_v2'),
            'listing_number' => $result['publicListingNumber'] ?? null,
            'listing_url' => $result['listingUrl'] ?? null,
        ]);
    }

    public function ajaxStopOnOtomoto() {
        check_ajax_referer('dol_cars_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Brak uprawnień']);
        }

        $carId = sanitize_text_field($_POST['car_id'] ?? '');
        if (empty($carId)) {
            wp_send_json_error(['message' => __('No car ID provided', 'partners-site_v2')]);
        }

        $dealerId = $this->getCurrentDealerId();
        $sentCars = $this->getSentCars($dealerId);
        $otomotoData = $sentCars[$carId]['otomoto_data'] ?? [];

        if (empty($otomotoData['advert_id'])) {
            wp_send_json_error(['message' => __('No Otomoto listing ID', 'partners-site_v2')]);
        }

        $otomotoSettings = get_field('otomoto_settings', 'options-dealer');
        if (!$otomotoSettings || empty($otomotoSettings['otomoto_enabled'])) {
            wp_send_json_error(['message' => __('Otomoto integration is not enabled', 'partners-site_v2')]);
        }

        $client = new CarOtoMoto(
            $otomotoSettings['otomoto_username'],
            $otomotoSettings['otomoto_password'],
            $otomotoSettings['otomoto_client_id'] . ':' . $otomotoSettings['otomoto_client_secret']
        );

        if (!$client->isAuthenticated()) {
            wp_send_json_error(['message' => __('Otomoto authentication failed', 'partners-site_v2')]);
        }

        $result = $client->deleteAdvert($otomotoData['advert_id']);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('Error while pausing the Otomoto listing', 'partners-site_v2') . ': ' . $result->get_error_message()]);
        }

        $this->markCarAsStopped($dealerId, $carId, 'otomoto');

        wp_send_json_success(['message' => __('The listing has been paused in Otomoto', 'partners-site_v2')]);
    }

    public function ajaxResumeOnOtomoto() {
        check_ajax_referer('dol_cars_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $carId = sanitize_text_field($_POST['car_id'] ?? '');
        if (empty($carId)) {
            wp_send_json_error(['message' => __('No car ID provided', 'partners-site_v2')]);
        }

        $dealerId = $this->getCurrentDealerId();
        $savedCars = $this->getSavedDolCars($dealerId);
        $dolCar = $savedCars[$carId] ?? null;

        if (!$dolCar) {
            wp_send_json_error(['message' => __('Car not found in saved DOL cars', 'partners-site_v2')]);
        }

        $otomotoSettings = get_field('otomoto_settings', 'options-dealer');
        if (!$otomotoSettings || empty($otomotoSettings['otomoto_enabled'])) {
            wp_send_json_error(['message' => __('Otomoto integration is not enabled for this dealer', 'partners-site_v2')]);
        }

        if (empty($otomotoSettings['otomoto_username']) || empty($otomotoSettings['otomoto_password']) || empty($otomotoSettings['otomoto_client_id']) || empty($otomotoSettings['otomoto_client_secret'])) {
            wp_send_json_error(['message' => __('Otomoto login credentials are not configured', 'partners-site_v2')]);
        }

        $dolCar = is_object($dolCar) ? $dolCar : json_decode(json_encode($dolCar));

        $mapper = new DolOtomotoMapper($otomotoSettings);
        $payload = $mapper->map($dolCar);

        if (is_wp_error($payload)) {
            wp_send_json_error(['message' => __('Mapping failed', 'partners-site_v2') . ': ' . $payload->get_error_message()]);
        }

        $client = new CarOtoMoto(
            $otomotoSettings['otomoto_username'],
            $otomotoSettings['otomoto_password'],
            $otomotoSettings['otomoto_client_id'] . ':' . $otomotoSettings['otomoto_client_secret']
        );

        if (!$client->isAuthenticated()) {
            wp_send_json_error(['message' => __('Otomoto authentication failed', 'partners-site_v2')]);
        }

        $images = [];
        if (!empty($dolCar->images) && is_array($dolCar->images)) {
            foreach ($dolCar->images as $img) {
                if (!empty($img->url)) {
                    $images[] = $img->url;
                }
            }
        }

        if (!empty($images)) {
            $collection = $client->createImageCollection();
            if (is_wp_error($collection)) {
                wp_send_json_error(['message' => __('Error creating Otomoto photo collection', 'partners-site_v2') . ': ' . $collection->get_error_message()]);
            }

            $collectionId = $collection['id'] ?? null;
            if ($collectionId) {
                foreach ($images as $imageUrl) {
                    $uploadResult = $client->addImageToCollection($collectionId, $imageUrl);
                    if (is_wp_error($uploadResult)) {
                        error_log('Otomoto image upload failed: ' . $uploadResult->get_error_message());
                    }
                }
                $payload['image_collection_id'] = $collectionId;
            }
        }

        $result = $client->createAdvert($payload);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => __('Otomoto API error during resumption', 'partners-site_v2') . ': ' . $result->get_error_message()]);
        }

        $this->markCarAsSent($dealerId, $carId, 'otomoto', [
            'advert_id' => $result['id'] ?? null,
            'advert_url' => $result['url'] ?? null,
            'status' => 'active',
        ]);

        wp_send_json_success([
            'message' => __('The listing has been resumed in Otomoto', 'partners-site_v2'),
            'advert_id' => $result['id'] ?? null,
            'advert_url' => $result['url'] ?? null,
        ]);
    }

    private function getInitialCarsData($dealerId) {
        $savedDolCars = $this->getSavedDolCars($dealerId);
        $sentCars = $this->getSentCars($dealerId);
        $dolCars = [];

        foreach ($savedDolCars as $carId => $car) {
            $car = is_object($car) ? $car : (object) $car;
            $car->source = 'DOL';
            $car->findcar_sent = isset($sentCars[$carId]['findcar']) ? $sentCars[$carId]['findcar'] : false;
            $car->findcar_status = $sentCars[$carId]['findcar_data']['status'] ?? null;
            $car->findcar_listing_url = $sentCars[$carId]['findcar_data']['listing_url'] ?? null;
            $car->otomoto_sent = isset($sentCars[$carId]['otomoto']) ? $sentCars[$carId]['otomoto'] : false;
            $car->otomoto_status = $sentCars[$carId]['otomoto_data']['status'] ?? null;
            $car->otomoto_advert_url = $sentCars[$carId]['otomoto_data']['advert_url'] ?? null;
            $dolCars[] = $car;
        }

        $usedCars = $this->getUsedStockCars($dealerId);
        $allCars = array_merge($dolCars, $usedCars);

        return [
            'cars' => $allCars,
            'total' => count($allCars),
            'dol_count' => count($dolCars),
            'used_count' => count($usedCars),
        ];
    }

    private function getCurrentDealerId() {
        $options = get_fields('options-dealer');
        return $options['dealerId'] ?? null;
    }

    private function getDolCarsOptionKey($dealerId) {
        return 'dol_cars_data_' . md5($dealerId);
    }

    private function getSentCarsOptionKey($dealerId) {
        return 'dol_cars_sent_' . md5($dealerId);
    }

    private function saveDolCars($dealerId, $cars) {
        $optionKey = $this->getDolCarsOptionKey($dealerId);
        update_option($optionKey, $cars, false);
    }

    private function getSavedDolCars($dealerId) {
        $optionKey = $this->getDolCarsOptionKey($dealerId);
        $cars = get_option($optionKey, []);
        return is_array($cars) ? $cars : [];
    }

    private function getSentCars($dealerId) {
        $optionKey = $this->getSentCarsOptionKey($dealerId);
        $sent = get_option($optionKey, []);
        return is_array($sent) ? $sent : [];
    }

    private function markCarAsSent($dealerId, $carId, $platform, $extraData = []) {
        $optionKey = $this->getSentCarsOptionKey($dealerId);
        $sent = $this->getSentCars($dealerId);

        if (!isset($sent[$carId])) {
            $sent[$carId] = [];
        }

        $sent[$carId][$platform] = true;
        if (!empty($extraData)) {
            $sent[$carId][$platform . '_data'] = $extraData;
        }
        update_option($optionKey, $sent);
    }

    private function markCarAsStopped($dealerId, $carId, $platform) {
        $optionKey = $this->getSentCarsOptionKey($dealerId);
        $sent = $this->getSentCars($dealerId);

        if (!isset($sent[$carId])) {
            $sent[$carId] = [];
        }

        $sent[$carId][$platform] = true;
        if (isset($sent[$carId][$platform . '_data'])) {
            $sent[$carId][$platform . '_data']['status'] = 'inactive';
        } else {
            $sent[$carId][$platform . '_data'] = ['status' => 'inactive'];
        }
        update_option($optionKey, $sent);
    }

    private function getUsedStockCars($dealerId) {
        $args = [
            'post_type' => 'stock-car',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'cartype',
                    'value' => 'used',
                    'compare' => '=',
                ],
                [
                    'key' => 'archive',
                    'value' => '1',
                    'compare' => '!=',
                ],
            ],
        ];

        $query = new \WP_Query($args);
        $cars = [];
        $sentCars = $this->getSentCars($dealerId);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $postId = get_the_ID();
                $carId = 'used_' . $postId;

                $findcarStatus = get_post_meta($postId, 'findcar_status', true);
                $findcarListingStatus = get_post_meta($postId, 'findcar_listing_status', true);
                $findcarAvailable = ($findcarStatus === 'active' || $findcarStatus === 'pending');
                $findcarSent = ($findcarStatus === 'active' && $findcarListingStatus === 'active');

                $otomotoSettings = get_field('otomoto_settings', 'options-dealer');
                $otomotoEnabled = $otomotoSettings && isset($otomotoSettings['otomoto_enabled']) && $otomotoSettings['otomoto_enabled'];
                $otomotoSent = isset($sentCars[$carId]['otomoto']) ? $sentCars[$carId]['otomoto'] : false;

                $car = (object) [
                    'id' => $carId,
                    'source' => __('Used', 'partners-site_v2'),
                    'post_id' => $postId,
                    'model' => get_field('model_1', $postId) ?: get_field('model', $postId),
                    'version' => get_field('version_1', $postId) ?: get_field('version', $postId),
                    'color' => get_field('color_1', $postId) ?: get_field('color', $postId),
                    'vin' => get_field('vin', $postId),
                    'year' => get_field('production-year', $postId),
                    'findcar_available' => $findcarAvailable,
                    'otomoto_available' => $otomotoEnabled,
                    'findcar_sent' => $findcarSent || (isset($sentCars[$carId]['findcar']) ? $sentCars[$carId]['findcar'] : false),
                    'otomoto_sent' => $otomotoSent,
                ];

                $cars[] = $car;
            }
            wp_reset_postdata();
        }

        return $cars;
    }

    private function filterCarsByDealer($cars, $dealerId) {
        return array_filter($cars, function($car) use ($dealerId) {
            if (!isset($car->dealer)) {
                return false;
            }

            $carDealerId = is_object($car->dealer) ? ($car->dealer->dealerId ?? null) : $car->dealer;
            return $carDealerId && $carDealerId == $dealerId;
        });
    }

    private function filterCarsByExposes($cars) {
        return array_filter($cars, function($car) {
            if (!isset($car->exposes)) {
                return false;
            }

            foreach ($car->exposes as $expose) {
                if (($expose->platform === 'FIND_CAR' || $expose->platform === 'OTOMOTO') && $expose->value === true) {
                    return true;
                }
            }

            return false;
        });
    }

    public function enqueueOtomotoLocationScripts($hook) {
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'dealer_page_options-dealer') {
            return;
        }

        wp_enqueue_script(
            'otomoto-location-admin',
            get_template_directory_uri() . '/includes/views/admin/otomoto-locations.js',
            ['jquery'],
            null,
            true
        );

        wp_localize_script('otomoto-location-admin', 'otomotoLocationAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('otomoto_location_nonce'),
        ]);
    }

    private function getOtomotoClient() {
        $settings = get_field('otomoto_settings', 'options-dealer');
        if (empty($settings) || empty($settings['otomoto_username']) || empty($settings['otomoto_password']) || empty($settings['otomoto_client_id']) || empty($settings['otomoto_client_secret'])) {
            return new \WP_Error('otomoto_missing_credentials', __('No Otomoto login credentials configured', 'partners-site_v2'));
        }

        $client = new \CarOtoMoto(
            $settings['otomoto_username'],
            $settings['otomoto_password'],
            $settings['otomoto_client_id'] . ':' . $settings['otomoto_client_secret']
        );

        if (!$client->isAuthenticated()) {
            return new \WP_Error('otomoto_auth_failed', __('Failed to log in to Otomoto', 'partners-site_v2'));
        }

        return $client;
    }

    public function ajaxOtomotoTestConnection() {
        check_ajax_referer('otomoto_location_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $client = $this->getOtomotoClient();
        if (is_wp_error($client)) {
            wp_send_json_error(['message' => $client->get_error_message()]);
        }

        $result = $client->testConnection();
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'message' => __('Connection active', 'partners-site_v2'),
            'data' => $result,
        ]);
    }

    public function ajaxOtomotoFetchRegions() {
        check_ajax_referer('otomoto_location_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $client = $this->getOtomotoClient();
        if (is_wp_error($client)) {
            wp_send_json_error(['message' => $client->get_error_message()]);
        }

        $regions = $client->getRegions();
        if (is_wp_error($regions)) {
            wp_send_json_error(['message' => $regions->get_error_message()]);
        }

        $choices = [];
        if (!empty($regions) && is_array($regions)) {
            foreach ($regions as $region) {
                if (isset($region['id']) && isset($region['name'])) {
                    $choices[] = [
                        'id' => $region['id'],
                        'name' => $region['name'],
                    ];
                }
            }
        }

        wp_send_json_success(['regions' => $choices]);
    }

    public function ajaxOtomotoFetchCities() {
        check_ajax_referer('otomoto_location_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $regionId = intval($_GET['region_id'] ?? 0);
        if (!$regionId) {
            wp_send_json_error(['message' => __('No region ID', 'partners-site_v2')]);
        }

        $client = $this->getOtomotoClient();
        if (is_wp_error($client)) {
            wp_send_json_error(['message' => $client->get_error_message()]);
        }

        $cities = $client->getCities($regionId);
        if (is_wp_error($cities)) {
            wp_send_json_error(['message' => $cities->get_error_message()]);
        }

        $choices = [];
        if (!empty($cities) && is_array($cities)) {
            foreach ($cities as $city) {
                if (isset($city['id']) && isset($city['name'])) {
                    $choices[] = [
                        'id' => $city['id'],
                        'name' => $city['name'],
                        'latitude' => $city['latitude'] ?? null,
                        'longitude' => $city['longitude'] ?? null,
                    ];
                }
            }
        }

        wp_send_json_success(['cities' => $choices]);
    }

    public function ajaxOtomotoFetchDistricts() {
        check_ajax_referer('otomoto_location_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('No permissions', 'partners-site_v2')]);
        }

        $cityId = intval($_GET['city_id'] ?? 0);
        if (!$cityId) {
            wp_send_json_error(['message' => __('No city ID', 'partners-site_v2')]);
        }

        $client = $this->getOtomotoClient();
        if (is_wp_error($client)) {
            wp_send_json_error(['message' => $client->get_error_message()]);
        }

        $districts = $client->getDistricts($cityId);
        if (is_wp_error($districts)) {
            wp_send_json_error(['message' => $districts->get_error_message()]);
        }

        $choices = [];
        if (!empty($districts) && is_array($districts)) {
            foreach ($districts as $district) {
                if (isset($district['id']) && isset($district['name'])) {
                    $choices[] = [
                        'id' => $district['id'],
                        'name' => $district['name'],
                    ];
                }
            }
        }

        wp_send_json_success(['districts' => $choices]);
    }
}
