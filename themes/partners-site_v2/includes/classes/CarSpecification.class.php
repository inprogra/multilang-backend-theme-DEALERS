<?php

namespace Classes;

use GuzzleHttp\Client;
use Classes\CarSpecificationDataImporter;

class CarSpecification
{

	public function __construct()
	{

	}

	public function addActions()
	{

		add_action('post_submitbox_misc_actions', array($this, 'addCustomButtonToAdminPanel'));
		add_action('edit_form_top', array($this, 'addBackdrop'));
		add_action('admin_menu', array($this, 'removeFromAdminMenu'), 100);
		add_action('current_screen', array($this, 'limitPermissionsForPostType'), 999);
		add_action('admin_notices', array($this, 'adminNotices'));
	}

	public function adminNotices(): void
	{
		if (get_post_type(get_the_ID()) !== 'stock-car') {
			return;
		}

		echo '<form name="post" action="post.php" method="post" class="js-car-specification-form"></form>';

		if (isset($_GET['car-specification-data-imported'])) {
			echo '
                <div class="updated">
                  <p>' . __('Data from the DOL system has been imported.', 'partners-site_v2') . '</p>
               </div>
            ';
		}
	}
	
	public static function registerPostType()
	{
		register_post_type(
			'car-specification',
			array(
				'label' => __('Technical specifications of cars available on-site', 'partners-site_v2'),
				'public' => false,
				'rewrite' => false,
				'show_ui' => true,
				'supports' => array('title'),
				'has_archive' => false,
				'hierarchical' => false,
			)
		);
	}

	public function addCustomButtonToAdminPanel($post): void
	{
		if (get_post_type($post->ID) !== 'stock-car') {
			return;
		}

		$html = '<div class="car-specification js-car-specification">';
		$html .= '<div class="car-specification__group">';

		global $pagenow;

		if ($pagenow !== 'post-new.php') {
			$html .= '<input type="hidden" name="postId" value="' . get_the_ID() . '" class="js-car-specification__input-post-id" />';
			$html .= '<span class="car-specification__spinner spinner js-car-specification__spinner"></span>';
			$html .= '<button type="button" class="car-specification__button button-primary js-car-specification__button">' . __('Import data from DOL', 'partners-site_v2') . '</button>';
			$html .= '<p>' . __('Note! Any changes made to the technical specifications will be overwritten by data from the DOL system.', 'partners-site_v2') . '</p>';
		} else {
			$html .= '<p>' . __('To import data from the DOL system, save the car as a draft or publish it.', 'partners-site_v2') . '</p>';
		}
		
		$html .= '</div>';
		$html .= '</div>';
		
		if (get_field('dol_sync',$post->ID) == '1' || get_field('activemotors',$post->ID) == '1') {
			$html = '';
		}
		echo $html;
	}

	public function addBackdrop($post): void
	{
		if (get_post_type($post->ID) !== 'stock-car') {
			return;
		}

		$html = '<div class="car-specification-backdrop js-car-specification-backdrop">';
		$html .= '<div class="car-specification-backdrop__inner">';
		$html .= '<span class="car-specification-backdrop__text">' . __('Importing data from DOL. <br>This operation may take a few minutes.', 'partners-site_v2') . '</span>';
		$html .= '<a class="a-spinner car-specification-backdrop__spinner"></a>';
		$html .= '</div>';
		$html .= '</div>';
		echo $html;
	}

	public function removeFromAdminMenu(): void
	{
		if (!is_main_site()) {
			remove_menu_page('edit.php?post_type=car-specification');
		}
	}

	public function limitPermissionsForPostType($current_screen): void
	{
		if (!is_main_site() && $this->isCarSpecificationPostType($current_screen)) {
			esc_html_e('No permissions', 'partners-site_v2');
			/** @noinspection ForgottenDebugOutputInspection */
			wp_die();
		}
	}

	public function update($data)
	{
		$client = new Client();

		$carSpecificationDataImporter = new CarSpecificationDataImporter($client);
		//		$checkData = file_get_contents('https://volvo-sync.easyapi.space/api/getCarData/11529237');
		$checkData = file_get_contents('https://volvo-sync.easyapi.space/api/getCarData/' . ($data['VIN'] ? $data['VIN'] : $data['CON']));
		if ($checkData !== 'Eurocode not found' && $checkData !== 'Car not found') {
			$this->updateEurocode($data['postId'], $checkData);
		}
		
		$data = $this->validateData($data);

		$newData = $carSpecificationDataImporter->import($data['VIN'] ?? null, $data['CON'] ?? null);
		
	
		$accordionData = $this->convertCarSpecificationDataImporterFormatToACF($newData['sections']);

		$this->updateAccordionSection($data['postId'], $accordionData);
		$this->updateVINAndCONFields($data['VIN'] ?? '', $data['CON'] ?? '', $data['postId']);
		$this->updateModelField($data['model'], $data['postId']);
		$this->updateVersionField($data['version'], $data['postId']);
		$fullData = file_get_contents('https://volvo-sync.easyapi.space/api/getCarDataByVin/' . ($data['VIN'] ? $data['VIN'] : $data['con']));
		$checkData = file_get_contents('https://volvo-sync.easyapi.space/api/getCarData/' . ($data['VIN'] ? $data['VIN'] : $data['con']));
		
		if ($checkData !== 'Eurocode not found' && $checkData !== 'Car not found') {
			$this->updateEurocode($checkData, $data['postId']);
		}
		if ($fullData) {
			$this->importKafkaCar(json_decode($fullData), $data['postId']);

		}


		if (array_key_exists('tyreLabels', $newData)) {
			$this->updateTyreLabels(MultisiteFixer::getCurrentBlogId(), $data['postId'], $newData['tyreLabels']);
		} else {
			$this->clearTyreLabels(MultisiteFixer::getCurrentBlogId(), $data['postId']);
		}

		if (array_key_exists('fuelConsumption', $newData)) {
			$this->updateFuelConsumption($data['postId'], $newData['fuelConsumption']['value'], $newData['fuelConsumption']['unit']);
		}

		$this->updateModelInfo($data['postId']);
	}
	private function importKafkaCar($data, $post_id)
	{

		//$this->clearTyreLabels( MultisiteFixer::getCurrentBlogId(), $data['postId'] );


		$id = $post_id;

		$tData = json_decode($data->car->technicalData);
		$temp_array = [];
			$x = 0;

			foreach ($data->car->equipment as $key => $value) {
				if ($value->category == 'Metalizowany' || ($value->category == 'Podstawowy' && $value->type == 'COLOR')) {
					$color = substr($value->id, 0, -2) . ' ' . $value->name;
				} else {


					if (!array_key_exists($value->category, $temp_array)) {
						$temp_array[$value->category] = [];
					}
					$temp_array[$value->category][] = $value->name;
					$x++;
				}
		}
		update_field('version_1', $data->car->version, $post_id);
		update_field('model_1', $data->car->model, $post_id);
		update_field('production-year', $data->car->productionYear, $post_id);


		$engines = [
			'Single Motor Extended Range' => __('Electric single Ext. range', 'partners-site_v2'),
			'Single Motor' => __('Electric single', 'partners-site_v2')
		];
		$engine = explode(' (', $data->car->engineDesc)[0];


		if (array_key_exists($engine, $engines)) {
			$engine = $engines[$engine];
		}

		$fuelType = ($data->car->engine->fuelType[0] == ' ' ? substr($data->car->engine->fuelType, 1) : $data->car->engine->fuelType);
		$powerData = (array)$data->car->engine;

		//update wszystkich dostępnych pól
		update_field('gearbox','Automatyczna',$id);
		update_field('erange',($fuelType == 'Elektryczny' ? str_replace(' km', '',$tData->electricRangeWltpTotal ) : '-'),$id);
		update_field('con', $data->car->con,$id);
		update_field('eurocode', $data->car->euroCode, $id);				
		update_field('has-discount-price', false, $id);								
		update_field('cartype','nowy',$id);	
		update_field('dol_sync', 1);
		update_field('color_1', $color, $id);		
		update_field('pno', $data->car->pno12,$id);														
	 	update_field('engine_1',$engine,$id);				
		update_field('max-power-text',(array_key_exists('maxPowerHp',$powerData) ? $data->car->engine->maxPowerHp : $data->car->engine->maxElectricPowerHp) .(array_key_exists('maxElectricPowerHp',$powerData) && array_key_exists('maxPowerHp',$powerData) ? ' + '.$data->car->engine->maxElectricPowerHp : ''),$id);
		update_field('max-power' ,str_replace(' KM','',$tData->horsepowerTotal),$id);
			update_field('fuel-type', $fuelType,$id);
		update_field('acceleration',explode(' ',$tData->acceleration)[0],$id);
		update_field('fuel-consumption-unit',($fuelType == 'Benzyna' || $fuelType == 'Diesel' ? 'l/100km' : 'kWh/100km'),$id);
		update_field('fuel-consumption',($fuelType == 'Elektryczny' ? str_replace(' kWh/100 km','',$tData->electricEnergyConsumptionWltpTotal) : explode(' ',$tData->fuelConsumptionWltpMedium)[0]),$id);
		update_field('max-speed',explode(' ',$tData->maxSpeed)[0],$id);
		update_field('cargo-capacity',explode(' ',$tData->cargoCapacity)[0], $id);
		update_field('accordion-heading',$data->car->model . ' ' . $data->car->version,$id);

		
		
		
		
		
		$winterLabels = [];
		foreach($data->car->tires as $tire) {
			$tire = (array) $tire;
			$winterLabels[]['url'] = $tire['url'];

		}
		
		// $winterLabels = $this->saveTyreLabelsFiles( $data->car->tires, 'winter', $id );
		$this->updateWinterTyreLabelsSection( $id, $winterLabels );
		//update wszystkich dostępnych pól



		$tmp = [];
		$temp_array = [];
		$x = 0;

		foreach ($data->car->equipment as $key => $value) {
			if (!array_key_exists($value->category, $temp_array)) {
				$temp_array[$value->category] = [];
			}
			$temp_array[$value->category][] = $value->name;
			$x++;

		}
		$accordionData = [];
		$x = 0;
		foreach ($temp_array as $key => $value) {

			$accordionData[$x]['name'] = $key;
			foreach ($value as $i) {
				$accordionData[$x]['items'][] = [
					'name' => $i
				];


			}
			$x++;

		}

		$imagesArr = [];
		$secondGallery = [];
		$x=0;
		foreach ($data->car->images as $image) {
			$file = $image->url;
			
			$filename = basename($image->url);

			
			$upload_file = wp_upload_bits($filename, null, file_get_contents($file));
			if (!$upload_file['error']) {
				$wp_filetype = wp_check_filetype($filename, null);
				$checkfile = true;
				if ($checkfile) {
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_parent' => $id,
					'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
					'post_content' => '',
					'post_status' => 'inherit'
				);
				
				$attachment_id = wp_insert_attachment($attachment, $upload_file['file'], $id);
				
				if (!is_wp_error($attachment_id)) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					$attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
					wp_update_attachment_metadata($attachment_id,  $attachment_data);
				}
				if ($image->type == 'INTERIOR' && $x < 3) {
					array_push($secondGallery,$attachment_id);
					$x++;
				} else {
					array_push($imagesArr, $attachment_id);
				}
				
			}
		}
		}		
		$secondGallery = array_reverse($secondGallery);
		$gallery = get_field('gallery', $post_id);
	//	$second_gallery = get_field('gallery', $post_id);
		update_field('field_602ba0b52720c', $imagesArr, $post_id);
		update_field('field_6034b4fa1e81e',$secondGallery, $post_id);
		update_field('accordion', $accordionData, $post_id);
	//	$gallery = get_field('gallery', $post_id);
		update_field('accordion', $accordionData, $post_id);
		update_field('field_602ba0b52720c', $imagesArr, $post_id);
	}
	private function updateModelInfo($postId): bool
	{
		if (!$this->postExists($postId)) {
			return false;
		}

		$model = get_field('model', $postId);
		$version = get_field('version', $postId);

		$modelInfo = $this->getModelInfo($model, $version);

		if (empty($modelInfo)) {
			return false;
		}

		foreach ($modelInfo as $key => $value) {
			update_field($key, $value, $postId);
		}

		return true;
	}

	private function getModelInfo($model, $version = null): array
	{
		switch_to_blog(1);
		$query = new \WP_Query(
			array(
				'post_type' => 'car-specification',
				'posts_per_page' => 1,
				'post_status' => 'publish',
				'cache_results' => true,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'model',
						'value' => $model,
					),
				),
			)
		);

		if ($query->post_count <= 0) {
			return array();
		}

		$post = $query->posts[0];

		$versions = get_field('versions', $post->ID);

		if ($version && $versions && in_array($version, array_column($versions, 'version'), true)) {
			$versionKey = array_search($version, array_column($versions, 'version'), true);

			$modelInfo = array(
				'category' => get_field('category', $post->ID),
				'max-speed' => str_replace(',', '.', $versions[$versionKey]['max-speed']),
				'cargo-capacity' => str_replace(',', '.', $versions[$versionKey]['cargo-capacity']),
				'seats' => str_replace(',', '.', $versions[$versionKey]['seats']),
				'height' => str_replace(',', '.', $versions[$versionKey]['height']),
				'length' => str_replace(',', '.', $versions[$versionKey]['length']),
				'width' => str_replace(',', '.', $versions[$versionKey]['width']),
				'ground-clearance' => str_replace(',', '.', $versions[$versionKey]['ground-clearance']),
			);
		} else {
			$modelInfo = array(
				'category' => get_field('category', $post->ID),
				'max-speed' => str_replace(',', '.', get_field('max-speed', $post->ID)),
				'cargo-capacity' => str_replace(',', '.', get_field('cargo-capacity', $post->ID)),
				'seats' => str_replace(',', '.', get_field('seats', $post->ID)),
				'height' => str_replace(',', '.', get_field('height', $post->ID)),
				'length' => str_replace(',', '.', get_field('length', $post->ID)),
				'width' => str_replace(',', '.', get_field('width', $post->ID)),
				'ground-clearance' => str_replace(',', '.', get_field('ground-clearance', $post->ID)),
			);
		}

		restore_current_blog();

		return $modelInfo;
	}

	public function convertCarSpecificationDataImporterFormatToACF($data): array
	{
		$convertedData = array();

		foreach ($data as $section) {
			$sectionArray = array(
				'name' => $section['name'],
				'items' => array(),
			);

			foreach ($section['items'] as $item) {
				$sectionArray['items'][] = array(
					'name' => $item['name'],
				);
			}

			$convertedData[] = $sectionArray;
		}

		return $convertedData;
	}

	public function updateTyreLabels($siteId, $postId, $labels)
	{
		$postTyreLabelsFolder = TyreLabel::getLabelsFolderPath($siteId, $postId);
		$this->removeFolder($postTyreLabelsFolder);
		$this->createDirectoryIfNotExist($postTyreLabelsFolder);
		$this->createDirectoryIfNotExist($postTyreLabelsFolder . '/summer');
		$this->createDirectoryIfNotExist($postTyreLabelsFolder . '/winter');

		$summerLabels = $this->saveTyreLabelsFiles($labels['SUMMER'], 'summer', $postId);
		$this->updateSummerTyreLabelsSection($postId, $summerLabels);

		$winterLabels = $this->saveTyreLabelsFiles($labels['WINTER'], 'winter', $postId);
		$this->updateWinterTyreLabelsSection($postId, $winterLabels);
		return $labels;
	}

	private function clearTyreLabels($siteId, $postId)
	{
		$postTyreLabelsFolder = TyreLabel::getLabelsFolderPath($siteId, $postId);
		$this->removeFolder($postTyreLabelsFolder);
		$this->updateSummerTyreLabelsSection($postId, array());
		$this->updateWinterTyreLabelsSection($postId, array());
	}

	private function isCarSpecificationPostType($current_screen): bool
	{
		return ($current_screen->base === 'post' || $current_screen->base === 'edit') && $current_screen->post_type === 'car-specification';
	}

	private function saveTyreLabelsFiles($labels, $type, $postId): array
	{
		$labelsNewUrls = array();

		foreach ($labels as $label) {
			$tyreLabel = new TyreLabel($type);
			$labelsNewUrls[]['url'] = $tyreLabel->save($label['url'], basename($label['url']), $postId);
		}

		return $labelsNewUrls;
	}

	public function removeFolder($dir)
	{
		foreach (glob($dir . '/*') as $file) {
			if (is_dir($file)) {
				$this->removeFolder($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dir);
	}

	public function validateData($data): array
	{
		$validFields = array('VIN', 'CON', 'pno12', 'model', 'version', 'postId');
		$validatedData = array();

		foreach ($data as $key => $value) {
			if (!in_array($key, $validFields, true)) {
				continue;
			}
			$validatedData[$key] = $value;
		}

		return $validatedData;
	}

	private function createDirectoryIfNotExist($path)
	{
		if (!mkdir($path, 0755, true) && !is_dir($path)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
		}
	}

	private function updateFuelConsumption($postId, $value, $unit): bool
	{
		if ($this->postExists($postId)) {
			update_field('fuel-consumption', $value, $postId);
			update_field('fuel-consumption-unit', $unit, $postId);
			return true;
		}

		return false;
	}
	private function updateEurocode($data, $postId)
	{
		if ($this->postExists($postId)) {
			update_field('eurocode', $data, $postId);
		}
	}
	private function updatePno($data, $postId)
	{
		if ($this->postExists($postId)) {
			update_field('field_pno', $data, $postId);
		}
	}
	private function updateAccordionSection($postId, $data)
	{
		if ($this->postExists($postId)) {
			update_field('accordion', $data, $postId);
			return true;
		}

		return false;
	}

	private function updateSummerTyreLabelsSection($postId, $data)
	{
		if ($this->postExists($postId)) {
			update_field('summer-tyre-labels', $data, $postId);
			return true;
		}

		return false;
	}

	public function updateWinterTyreLabelsSection($postId, $data)
	{
		if ($this->postExists($postId)) {
			update_field('winter-tyre-labels', $data, $postId);
			return true;
		}

		return false;
	}

	private function updateVINAndCONFields($vin, $con, $postId)
	{
		if ($this->postExists($postId)) {
			update_field('vin', $vin, $postId);
			update_field('con', $con, $postId);

			return true;
		}

		return false;
	}

	private function updateModelField($model, $postId)
	{
		if ($this->postExists($postId)) {
			update_field('model', $model, $postId);
			return true;
		}

		return false;
	}

	private function updateVersionField($version, $postId)
	{
		if ($this->postExists($postId)) {
			update_field('version', $version, $postId);
			return true;
		}

		return false;
	}

	private function postExists($postId)
	{
		return get_post($postId) !== null;
	}
}
