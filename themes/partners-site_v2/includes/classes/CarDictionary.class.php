<?php

namespace Classes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Controllers\StockCarController;
use WP_Query;

use function Env\env;

class CarDictionary
{
	private $token;
	private ?Client $client = null;
	private string $api = '';
	public $upload_path;
	public $disable_dol;
	public $select_url;
	public $select_token;
	public function __construct(Client $client = null)
	{
		$this->disable_dol = $GLOBALS['disable_dol'];
		
		if (env('WP_ENV') === 'production') {

			$this->upload_path = '/var/www/volvocars-partner.pl/partners-site/web/wikicars/';
		} else {
			
			if ($_SERVER['SERVER_ADDR' ] == '51.68.131.242') {
				$this->upload_path = '/www/wwwroot/main-stage.easyapi.space/web/wikicars/';
			} else {
				$this->upload_path = '/home/volvotest.pl/public_html/web/wikicars/';
			}
		}

		if (env('DOMAIN_CURRENT_SITE') == 'volvotest.pl') {
			$this->select_url = 'https://volvo-dev.activemotors.eu/';
			$this->select_token = 'acEWfsabRTbdnTYMYmd7tn46jkT';
		} else {
			$this->select_url = 'https://volvo.activemotors.eu/';
			$this->select_token = 'GrfZohh3gJjCy1OjiiYO8pF5qcgwl24juOtTOBPLA484Vb9mKg6oVxwx6kCwjWYq';
		}





		add_action( 'save_post', [$this, 'odrekiSync'], 20, 2 );
		add_action( 'edit_post', [$this, 'odrekiSync'], 20, 2 );
		$this->client = $client;
		$this->api    = 'https://volvo-dealer.pl/api/zps/api/';
	}
	public function getDolCars($dealerId = null, $filters = null) {
		try {
			$token = $this->getDolToken();

			if (empty($token) || !is_string($token)) {
				throw new \Exception('Failed to retrieve valid token for DOL cars');
			}
			$domain = (env('WP_ENV') === 'production' ? 'https://volvo-dealer.pl' : 'https://test.volvo-dealer.pl');
			$endpoint = '/api/car/web/stock-cars/search?page=0&size=1000';

			$data = [
				'cities' => [],
				'models' => [],
				'colors' => [],
				'engines' => [],
				'upholsteries' => [],
				'productionYears' => [],
				'versions' => [],
				'powerRanges' => [],
				'size' => 300,
			];

			$log = [];
			$log['time'] = date('Y-m-d H:i:s');
			$log['url'] = $domain . $endpoint;
			$log['token'] = $token;
			$log['body'] = $data;

			$request = $this->client->request(
				'POST',
				$domain . $endpoint,
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
						'authToken' => $token,
					),
					'body' => json_encode($data),
					'debug' => false,
					'http_errors' => false,
				)
			);

			$statusCode = $request->getStatusCode();
			$body = (string) $request->getBody();
			$log['status'] = $statusCode;
			$log['response_preview'] = substr($body, 0, 500);
			file_put_contents('/www/wwwroot/main-stage.volvotest.pl/web/dol_debug.log', json_encode($log, JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);
			file_put_contents('/www/wwwroot/main-stage.volvotest.pl/web/cars.json', $body);
			$response = json_decode($body);

			return json_encode($response);
		} catch (\Exception $e) {
			file_put_contents('/www/wwwroot/main-stage.volvotest.pl/web/dol_error.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . "\n", FILE_APPEND);
			return json_encode(['content' => [], 'error' => $e->getMessage()]);
		}
	}
	public function getToken()
	{	
		if ($GLOBALS['disable_dol']) {
		$this->disable_dol = true;

		return [];
	    }
		//$response = [];
		//return $response;
		if ($this->client) {
		$response = array();

		$cert      = $this->client->request(
			'GET',
			'https://volvo.easyapi.space',
			array(
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'query'       => array('getToken' => 1, 'test' => (env('WP_ENV') === 'stage' || env('WP_ENV') === 'development' ? 'true' : 'false')),
				'body'        => json_encode(array()),
				'http_errors' => false,
			)
		);
		$cert_resp = json_decode($cert->getBody());
		$response  = $cert_resp;
		}
		
		return $response;
	}
	public function getDolToken()
	{
		if ($this->client) {
			$cert = $this->client->request(
				'GET',
				'https://volvo.easyapi.space',
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
					),
					'query' => array('getToken' => 1, 'test' => 1),
					'body' => json_encode(array()),
					'http_errors' => false,
				)
			);
			$cert_resp = json_decode($cert->getBody());
			if (is_array($cert_resp) && isset($cert_resp[1])) {
				return $cert_resp[1];
			}
		}
		return null;
	}
	public function odrekiSync(int $post_id): void {

		if ($_POST['post_type'] == 'stock-car' && $_POST['post_status'] == 'publish') {
			update_field('odreki_sync', 2, $post_id);
		} else {
			return;
		}

	}
	public function getSettings()
	{
		$d             = $_SERVER['HTTP_HOST'];
		$tmp           = array();
		$upl = $this->upload_path;

		$settings = $upl . 'settings.json';

		$tmp['lease']           = $this->getLeaseOffer();
		$tmp['najem']           = $this->getNajemOffer();
		$tmp['global_settings'] = file_exists($settings) ? json_decode(file_get_contents($settings)) : null;
		
		return json_encode($tmp);
	}
	public function getOffer($token, $path, $cache = true)
	{
		if ($this->disable_dol) {
			return [];
		}
		
		if (!file_exists($path . 'settings.json')) {
			$cache = false;
		}
		
		if (!$cache) {
		
			$request = $this->client->request(
				'GET',
				$this->api . 'products',
				array(
					'headers'     => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $token[0],
						'AuthToken'     => $token[1],
					),
					'http_errors' => false,
					// 'query' => ['token' => $token],
				)
			);

			$response      = json_decode($request->getBody());
			
			$response_data = json_decode($response);

			if (!is_dir(dirname($path))) {
				@mkdir(dirname($path), 0755, true);
			}
			if (is_dir(dirname($path))) {
				@$save_file = file_put_contents($path . 'settings.json', $response);
			}
		} else {

			$response = file_get_contents($path . 'settings.json');
			
			$response_data = json_decode($response);
			
		}
		// $response_data = [];
		if ($response_data && !empty($response_data)) {
			
			return $response_data->Result;
		} 
		return $response_data;
		
		
	}
	public function getUsedCars($data, $type, $blog_id, $filters = null) {
		$carsController = new StockCarController();
		
		$cars = $carsController->getAll($data,$type,$blog_id);
		return $cars;
	}
	public function getCarOffer($data, $token)
	{
		echo '<pre>';
		//var_dump($data);
		if ($this->disable_dol) {
			return '';
		}
		foreach ($data as $source) {
			$id      = $source->Id;
			$request = $this->client->request(
				'GET',
				$this->api . 'cases',
				array(
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $token,
					),
					'query'   => array('take' => $id),
				)
			);
			//var_dump($this->api . 'cases');
			$response      = json_decode($request->getBody());
			$response_data = json_decode($response);

			//var_dump($id);
			//var_dump($response);
			//var_dump($response_data);
		}
	}
	public function doCalculation($data, $token)
	{

		return false;
		$upload_folder = explode('/', wp_get_upload_dir()['basedir']);
		array_pop($upload_folder);
		$upl               = implode('/', $upload_folder) . '/cars/';
		$offer_id          = $data['DealerProductId'];
		$offer_eurocode    = $data['LeaseObject']['Eurocode'];
		$offer_price       = $data['Price'];
		$offer_length      = $data['InstalmentNumber'];
		$offer_appeallevel = $data['AppealLevel'];
		$feeRatio          = $data['EntryFeeRatio'];
		$finalValue        = $data['FinalValueRatio'];

		$offer_year     = $data['ManufacturingYear'];
		$default        = null;
		$config_default = null;

		if (array_key_exists('CFM', $data)) {
			$type = 'najem_' . $offer_id . '_' . $offer_eurocode . '_' . (int) $offer_price . '_' . $offer_length . '_' . $offer_appeallevel . '_' . $feeRatio . '_' . $finalValue . '_' . $data['CFM']['MileageLimitId'];
		} else {
			$default        = 'leasing_' . $offer_id . '_' . $offer_price . '_default.json';
			$config_default = $upl . '' . $default . '.json';
			$type           = 'leasing_' . $offer_id . '_' . $offer_eurocode . '_' . $offer_price . '_' . $offer_length . '_' . $offer_appeallevel . '_' . $feeRatio . '_' . $finalValue;
		}

		$config_file = $upl . '' . $type . '.json';
		if (!file_exists($config_file)) {
			$request = $this->client->request(
				'POST',
				$this->api . 'calculate',
				array(
					'headers'     => array(
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $token[0],
						'AuthToken'     => $token[1],
					),
					'body'        => json_encode($data),
					'debug'       => false,
					'http_errors' => false,
				)
			);

			$response      = json_decode($request->getBody());
			$response_data = json_decode($response);
			if ($config_default) {
				$f = fopen($config_default, 'w');
				fwrite($f, $response);
				fclose($f);
			}
			$file = fopen($config_file, 'w');

			fwrite($file, $response);
			fclose($file);
		} else {
			$response_data = json_decode(file_get_contents($config_file));
		}
		$price = null;

		return $response_data;
	}
	public function generateResidalValue($data, $token)
	{
		$request       = $this->client->request(
			'POST',
			'https://volvo.easyapi.space/?generateResidal=1',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => json_encode($data),
				'debug'   => false,
			)
		);
		$response      = json_decode($request->getBody());
		$response_data = json_decode($response);
	}

	public function getResidalValue($data, $token)
	{
		$offer_id       = $data['DealerProductId'];
		$offer_eurocode = $data['Eurocode'];
		$offer_price    = $data['Price'];
		$offer_length   = $data['InstalmentNumber'];
		$offer_year     = $data['ManufacturingYear'];
		if (array_key_exists('MileageLimitId', $data)) {
			$offer_milleage = $data['MileageLimitId'];
		} else {
			$offer_milleage = '';
		}

		$request  = $this->client->request(
			'POST',
			'https://volvo.easyapi.space/?getResidal=1',
			array(
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'body'        => json_encode($data),
				'debug'       => false,
				'http_errors' => false,
			)
		);
		$response = json_decode($request->getBody());
		// //var_dump($response);
		$response_data = $response;

		// $upload_folder = explode('/',wp_get_upload_dir()['basedir']);
		// array_pop($upload_folder);
		// $upl = implode('/',$upload_folder).'/cars/';
		// $offer_id = $data['DealerProductId'];
		// $offer_eurocode = $data['Eurocode'];
		// $offer_price = $data['Price'];
		// $offer_length = $data['InstalmentNumber'];
		// $offer_year = $data['ManufacturingYear'];
		// if (array_key_exists('MileageLimitId',$data)) {
		// $offer_milleage = $data['MileageLimitId'];
		// } else {
		// $offer_milleage = '';
		// }
		// $type = 'residal_milleage_'.$offer_milleage.'_'.$offer_id.'_'.$offer_eurocode.'_'.$offer_price.'_'.$offer_length.'_'.$offer_year;

		// $config_file = $upl.''.$type.'.json';
		// if (!file_exists($config_file)) {

		// $request = $this->client->request('POST', $this->api.'calculate/residual-value', [
		// 'headers' => [
		// 'Content-Type' => 'application/json',
		// 'Authorization' => 'Bearer '.$token[0],
		// 'AuthToken' => $token[1]
		// ],
		// 'body' => json_encode($data),
		// 'debug' => false
		// ]);
		// $response = json_decode($request->getBody());
		// $response_data = json_decode($response);

		// $file = fopen($config_file, "w");

		// fwrite($file, $response);
		// fclose($file);
		// } else {
		// $response_data = json_decode(file_get_contents($config_file));
		// }

		return $response_data;
	}
	public function importKafkasCars($id)
	{
		$id = $_GET['id'];
		$data = file_get_contents('https://volvo-sync.easyapi.space/api/getCarDataById/' . $id);

	
		
		
		$data = json_decode($data);
		$tData = json_decode($data->car->technicalData);
		$blog_id = $data->blog_id;
		


		
		switch_to_blog($blog_id);
		if ($data->car->vin) {
			$type = 'vin';
			$vin_data = $data->car->vin;
		} else {
			$type = 'con';
			$vin_data = $data->car->con;
		}
		$post_type = 'stock-car'; // replace with the actual custom post type name
		$args = [
			'post_type' => $post_type,
			'posts_per_page' => -1,
			'post_status' => 'any',
			'meta_query'    => [
				'relation'      => 'AND',
				[
					'key'       => $type,
					'value'     => $vin_data,
					'compare'   => '='
				]
				
			],
		];
		
		$posts = get_posts($args);
		
		
		if (empty($posts) && $blog_id) {
			
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
			$engines = [
				'Single Motor Extended Range' => 'Single elektryczny Ext. range',
				'Single Motor' => 'Single elektryczny'
			];
			$engine = explode(' (', $data->car->engineDesc)[0];


			if (array_key_exists($engine, $engines)) {
				$engine = $engines[$engine];
			}

			$fuelType = ($data->car->engine->fuelType[0] == ' ' ? substr($data->car->engine->fuelType, 1) : $data->car->engine->fuelType);
			$powerData = (array)$data->car->engine;
			
			$id = wp_insert_post([
				'post_type' => 'stock-car',
				'post_status' => 'draft',
				'post_title' => '[VOLVODOL] ' . $data->car->model . ' ' . $data->car->version.' '.$data->car->con. ' '.$data->car->engineDesc,
				'meta_input' => [
					'gearbox' => 'Automatyczna',
					'erange' => ($fuelType == 'Elektryczny' ? str_replace(' km', '',$tData->electricRangeWltpTotal ) : '-'),
					'vin' => $data->car->vin,
					'con' => $data->car->con,
					'eurocode' => $data->car->euroCode,				
					'model_1' => $data->car->model,
					'version_1' => $data->car->version,
					'offer-number' => '',
					'has-discount-price' => false,								
					'cartype' => 'nowy',	
					'dol_sync' => 1,	
					'color_1' => $color,		
					'pno' => $data->car->pno12,														
					'production-year' => $data->car->productionYear,															
					'engine_1' => $engine,				
					'max-power-text' => (array_key_exists('maxPowerHp',$powerData) ? $data->car->engine->maxPowerHp : $data->car->engine->maxElectricPowerHp) .(array_key_exists('maxElectricPowerHp',$powerData) && array_key_exists('maxPowerHp',$powerData) ? ' + '.$data->car->engine->maxElectricPowerHp : ''),
					'max-power' => str_replace(' KM','',$tData->horsepowerTotal),
					'fuel-type' => $fuelType,
					'acceleration' => explode(' ',$tData->acceleration)[0],
					'fuel-consumption-unit' => ($fuelType == 'Benzyna' || $fuelType == 'Diesel' ? 'l/100km' : 'kWh/100km'),
					'fuel-consumption' => ($fuelType == 'Elektryczny' ? str_replace(' kWh/100 km','',$tData->electricEnergyConsumptionWltpTotal) : explode(' ',$tData->fuelConsumptionWltpMedium)[0]),
					'max-speed' => explode(' ',$tData->maxSpeed)[0],
					'cargo-capacity' => explode(' ',$tData->cargoCapacity)[0],												
					'accordion-heading' => $data->car->model . ' ' . $data->car->version,																		
					//'accordion' => $accordionData,			
					'eurocode' => $data->car->euroCode
									
				]
			]);

			$carspec = new CarSpecification();
		$winterLabels = [];
		foreach($data->car->tires as $tire) {
			$tire = (array) $tire;
			$winterLabels[]['url'] = $tire['url'];

		}
		
		// $winterLabels = $carspec->saveTyreLabelsFiles( $data->car->tires, 'winter', $id );
		$carspec->updateWinterTyreLabelsSection( $id, $winterLabels );
		
		$imagesArr = [];
		$secondGallery = [];
		$x=0;
		foreach ($data->car->images as $image) {
			$file = $image->url;
			if ($image->type !== 'COLOR' && $image->type !=='WHEEL') {
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
		}		
		$secondGallery = array_reverse($secondGallery);
		$gallery = get_field('gallery', $post_id);
	//	$second_gallery = get_field('gallery', $post_id);
		update_field('field_602ba0b52720c', $imagesArr, $id);
		update_field('field_6034b4fa1e81e',$secondGallery, $id);
		update_field('accordion', $accordionData, $id);
      
		restore_current_blog();
      
		exit('Auto zaimportowane '.$vin_data);
		} else {
			exit('Auto jest już zaimportowane '.$vin_data);
		}
	}
	public function getOldCarsData() {
		$domain_only = $_GET['domain'];
		$mSalon = ['PL041','PL050'];

        $blog_ids = [];
        $blogs = wp_get_sites();
	
		$exclude_blogs = [3,38];
        foreach ($blogs as $b) {
			if (!in_array($b['blog_id'],$exclude_blogs)) {
			$dealerId = null;
			$showrooms = [];
			$multisalon = false;
            switch_to_blog($b['blog_id']);
            $options = get_fields('options-dealer'); 
            if (is_array($options)) {
                $dealerId = $options['dealerId'];   
				$dealerName = $options['name'];				
            }
			
            if (strpos($dealerName,'Test') === false && strpos($dealerName,'Euroservice Volvo Warszawa') === false && $b['domain'] !== 'autobruno-gorzow.volvocars-partner.pl') {
				
			$addresses = [];
			$cars = get_posts( ['post_type' => 'stock-car', 'fields' => 'ids', 'post_status' => 'publish','posts_per_page' => -1] );
			$cars_data = [];		           
			foreach($cars as $key=>$value) {
			 	$cars_data[] = ['vin' => get_field('vin',$value),'slug' => basename(get_permalink($value))];
			 	
			}
			$count_pages = count($cars);
			
            if ($domain_only) {
                array_push($blog_ids, $b['domain']);
				
            } else {
				
                $data = [];
				$showroom = get_posts(['post_type' => 'showroom']);
				
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
				if ($b['blog_id'] == 1) {
					$dealerId = 1;
				}
                $data = ['blog_id' => $b['blog_id'],'address'=> $addresses,'multisalon' => $multisalon, 'cars' => $count_pages,'domain'=> $b['domain'], 'dealerId' => $dealerId,'showrooms' => $showrooms,'car_ids' => $cars,'cars_data' => $cars_data];
			
				if ($dealerId ) {
					
               	 array_push($blog_ids, $data);
				}
				
            }
			
		}
        
         
            restore_current_blog();
			}
        }
		//array_pop($blog_ids);
		
		file_put_contents('old_car_data.json' ,json_encode($blog_ids));
		exit('ok');
	}
	public function getBlogIds() {
		$domain_only = $_GET['domain'];
		$mSalon = ['PL041','PL050'];

        $blog_ids = [];
        $blogs = wp_get_sites();
	
		$exclude_blogs = [3,38];
        foreach ($blogs as $b) {
			if (!in_array($b['blog_id'],$exclude_blogs)) {
			$dealerId = null;
			$showrooms = [];
			$multisalon = false;
            switch_to_blog($b['blog_id']);
            $options = get_fields('options-dealer'); 
            if (is_array($options)) {
                $dealerId = $options['dealerId'];   
				$dealerName = $options['name'];				
            }
			
            if (strpos($dealerName,'Test') === false && strpos($dealerName,'Euroservice Volvo Warszawa') === false && $b['domain'] !== 'autobruno-gorzow.volvocars-partner.pl') {
				
			$addresses = [];
			$cars = get_posts( ['post_type' => 'stock-car', 'fields' => 'ids', 'post_status' => 'publish','posts_per_page' => -1] );
			$cars_data = [];		           
			// foreach($cars as $key=>$value) {
			//  	$cars_data[] = ['vin' => get_field('vin',$value),'slug' => basename(get_permalink($value))];
			 	
			// }
			$count_pages = count($cars);
			
            if ($domain_only) {
                array_push($blog_ids, $b['domain']);
				
            } else {
				
                $data = [];
				$showroom = get_posts(['post_type' => 'showroom']);
				
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
                $data = ['blog_id' => $b['blog_id'],'address'=> $addresses,'multisalon' => $multisalon, 'cars' => $count_pages,'domain'=> $b['domain'], 'dealerId' => $dealerId,'showrooms' => $showrooms,'car_ids' => $cars,'cars_data' => $cars_data];
			
				if ($dealerId ) {
					
               	 array_push($blog_ids, $data);
				}
				
            }
			
		}
        
         
            restore_current_blog();
			}
        }
		//array_pop($blog_ids);
		
		return $blog_ids;
	}
	public function deleteSelectCars() {
		
		$dealers = $this->getBlogIds();
	
		$data = [
			'token' => $this->select_token
		];
		$request = $this->client->request('GET', $this->select_url . '/api/odreki/cars/delete', [
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body' => json_encode($data),
			'debug' => false,
			'http_errors' => false
		]);
		$response = json_decode($request->getBody());
		var_dump($response);

		
		if ($_GET['vin']) {
			$response = [$_GET['vin']];	
		}
		
	    foreach($response as $d) {
			$status = false;
			$resp = $d->vin;
			$respId = $d->internal_id;
			
			foreach($dealers as $d) {
				
				switch_to_blog($d->blog_id);
			$post_type = 'stock-car'; // replace with the actual custom post type name
				$args = [
					'post_type' => $post_type,
					'posts_per_page' => -1,
					'post_status' => 'any',
					'meta_query'    => [
						'relation'      => 'AND',
						[
							'key'       => 'vin',
							'value'     => $resp,
							'compare'   => '='
						],
					],
				];

				$posts = get_posts($args);
		
		if ($posts) {		
			foreach($posts as $post) {
				wp_delete_post($post->ID);
				$this->confirmImport([$respId],'delete');
				$status = true;
			}
		}
		restore_current_blog();
		}
		if ($status == false) {
			$this->confirmImport([$respId],'delete');
		}
	}
		exit();

	}
	public function confirmImport($data, $endpoint) {
		
		$data = [
			'token' => $this->select_token,
			'internal_ids' => $data
		];
		
		$request = $this->client->request('POST', $this->select_url . 'api/odreki/cars/'.$endpoint, [
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body' => json_encode($data),
			'debug' => false,
			'http_errors' => false
		]);
		$response = json_decode($request->getBody());
		$filename = date('Y-d-m-h:m');
		file_put_contents('wikicars/select-motors-'.$filename, json_encode($response));
		exit('confirmation_send');
	}
	public function importSelectCars()
	{
		
		// $car = file_get_contents('test/stock.json');
		// var_dump($car);
		// exit();
		// $url = 'https://volvo.activemotors.eu/';
		// $token = 'GrfZohh3gJjCy1OjiiYO8pF5qcgwl24juOtTOBPLA484Vb9mKg6oVxwx6kCwjWYq';
		$data = [
			'token' => $this->select_token
		];
		
		$request = $this->client->request('GET', $this->select_url . 'api/odreki/cars/export', [
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body' => json_encode($data),
			'debug' => false,
			'http_errors' => false
		]);
		$response = json_decode($request->getBody());
		
		
		
		if (!empty($response)) {
			$filename = date('Y-d-m-h:m');
			file_put_contents('wikicars/select-motors-'.$filename, json_encode($response));
			$this->importCars($response);
		}
	}
	public function importCarToBlog($blog_id, $data, $multisalon = false) {
		$token = file_get_contents('wikicars/token.json');
			if ($token) {
				$token = json_decode($token);
				$access_token = $token->access_token;
			}
			$vin = $data['vin'];
			
			switch_to_blog($blog_id);
			$post_type = 'stock-car'; // replace with the actual custom post type name
				$args = [
					'post_type' => $post_type,
					'posts_per_page' => -1,
					'post_status' => 'any',
					'meta_query'    => [
						'relation'      => 'AND',
						[
							'key'       => 'vin',
							'value'     => $vin,
							'compare'   => '='
						],
					],
				];

				$posts = get_posts($args);
		$intId = $data['internal_id'];
		restore_current_blog();
		
		if (empty($posts)) {
			
			if ($multisalon) {
				foreach($multisalon as $s) {
					switch_to_blog($s);
					$data['accordion'] = [];
			$tempArray = [];
			$x = 0;


			$temp_array = [];
			$x = 0;
			foreach ((array) $data['equipment'] as $key => $value) {
				$temp_array[$x]['name'] = $key;
				foreach ($value as $i) {
					$temp_array[$x]['items'][] = [
						'name' => $i
					];
				}
				$x++;
			}
			//  echo '<pre>';
			//  //var_dump($temp_array);
			//  exit();
			$accordionData = $temp_array;
			//  $accordionData = $test->convertCarSpecificationDataImporterFormatToACF($temp_array);
			//  foreach($data['equipment'] as $key=>$value) {                
			//     $name = $key;
			//     $items = [];

			//     foreach($value as $v) {
			//         array_push($items, $v);
			//     }
			//     $data['accordion'][$x]['name'] = $name;
			//     $data['accordion'][$x]['items'] = $items;

			//     $x++;
			//  }

			$url = 'https://gw.partner.api.volvocars.biz/vehicle/vin/' . $vin . '?Authorization=Bearer%20' . $access_token . '&Api-version=2.0&Ocp-Apim-Subscription-Key=3cf34637fef3402b85c4cfb0210a5a5d';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $access_token,
				'Api-version:2.0',
				'Ocp-Apim-Subscription-Key: 3cf34637fef3402b85c4cfb0210a5a5d'
			]);
			$response = curl_exec($ch);
			$response = json_decode($response);

			$version = $response->responseDetails->vehicle->salesVersionDescription;
			$type = $response->responseDetails->vehicle->bodyTypeDescription;
			$color = str_replace('0', '', $response->responseDetails->vehicle->externalColourCode) . ' ' . $response->responseDetails->vehicle->externalColour;
			if (strpos(strtolower($response->responseDetails->vehicle->fuelType),'hybryda')) {
					$fuelType = 'Hybryda Plug-In';
			} else {
				switch (strtolower($response->responseDetails->vehicle->fuelType)) {
					case 'petrol':
						$fuelType = 'Benzyna';
					break;
					case 'benzyna':
						$fuelType = 'Benzyna';
					case 'diesel':
						$fuelType = 'Diesel';
					break;
					case 'elektryczny':
						$fuelType = 'Elektryczny';
					$break;
				}
			}
			$id = wp_insert_post([
				'post_type' => 'stock-car',
				'post_status' => 'draft',
				'post_title' => '[VOLVOSELEKT] ' . $data['model'] . ' ' . $data['version'] . ' ' . $data['offerNumber'],
				'meta_input' => [
					'category' => $type,
					'gearbox' => ($response->responseDetails->vehicle->gearboxCode == 'D' ? 'Automatyczna' : 'Manualna'),
					'vin' => $data['vin'],
					'model' => $response->responseDetails->vehicle->modelDescription,
					'model_1' => $response->responseDetails->vehicle->modelDescription,
					
					'offer-number' => $data['offerNumber'],
					'has-discount-price' => $data['hasDiscountPrice'],
					'regular-price' => $data['regularPrice'],
					'income' => $data['income'],
					'cartype' => 'used',
					'income_najem' => $data['income_najem'],
					'pno' => $data['pno12'],
					'discount-price' => $data['discountPrice'],
					'pickup-time' => $data['pickupTime'],
					'mileage' => $data['mileage'],
					'car-distance' => $data['mileage'],
					'production-year' => $data['productionYear'],
					'lease_car' => $data['lease_car'],
					'najem_car' => $data['najem_car'],
					'color' => ucwords(strtolower($color)),
					'color_1' => ucwords(strtolower($color)),
					'inlay_1' => $data['inlay'],
					'inlay' => $data['inlay'],
					'engine' => $data['engine'],
					'engine_1' => $data['engine'],
					'version' => $version,
					'version_1' => $version,
					'max-power-text' => $data['maxPowerText'],
					'max-power' => $data['maxPower'],
					'fuel-type' => '',
					'acceleration' => $data['acceleration'],
					'fuel-consumption-unit' => $data['fuelConsumptionUnit'],
					'fuel-consumption' => $data['fuelConsumption'],
					'max-speed' => $data['maxSpeed'],
					'cargo-capacity' => $data['cargoCapacity'],
					'seats' => $data['seats'],
					'height' => $data['height'],
					'length' => $data['length'],
					'width' => $data['width'],
					'ground-clearance' => $data['groundClearance'],
					'accordion-heading' => $data['accordionHeading'],
					'dealer-name' => $data['dealerName'],
					'provider' => $data['provider'],
					'location' => $data['location'],
					'winter-tyre-labels' => json_encode($data['winterTyreLabels'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
					'gallery' => json_encode($data['gallery'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
					'accordion' => json_encode($data['accordion'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
					'created-date' => $data['createdDate'],
					'erange' => $data['erange'],
					'omnibus_date' => $data['omnibus_date'],
					'eurocode' => $data['eurocode'],
					'dealerphone' => $data['dealerphone'],
					'inlay_1' => $data['inlay'],
					'model_1' => $response->responseDetails->vehicle->modelDescription,
					'version_1' => $version,
					'color_1' => ucwords(strtolower($color)),
					'engine_1' => $data['engine'],
					'activemotors' => '1'

				]
			]);
			$imagesArr = [];
			$secondGallery = [];
			$x=0;
			foreach ($data['gallery'] as $image) {
				$file = $image->url;

				$filename = basename($image->url) . '.' . strtolower($image->extension);

				$upload_file = wp_upload_bits($filename, null, file_get_contents($file));
				if (!$upload_file['error']) {
					$wp_filetype = wp_check_filetype($filename, null);
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_parent' => $parent_post_id,
						'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attachment_id = wp_insert_attachment($attachment, $upload_file['file'], $parent_post_id);
					if (!is_wp_error($attachment_id)) {
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						$attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
						wp_update_attachment_metadata($attachment_id,  $attachment_data);
					}
					if ($x > 4 && $x<8) {
						array_push($secondGallery, $attachment_id);
					} else {
						array_push($imagesArr, $attachment_id);
					}
					$x++;
					
				}
			}
			$gallery = get_field('gallery', $post_id);
			update_field('field_6034b4fa1e81e',$secondGallery, $id);
			update_field('accordion', $accordionData, $id);
			update_field('field_602ba0b52720c', $imagesArr, $id);

			$x++;

					restore_current_blog();						
				}

			} else {
				switch_to_blog($blog_id);
					$data['accordion'] = [];
			$tempArray = [];
			$x = 0;


			$temp_array = [];
			$x = 0;
			foreach ((array) $data['equipment'] as $key => $value) {
				$temp_array[$x]['name'] = $key;
				foreach ($value as $i) {
					$temp_array[$x]['items'][] = [
						'name' => $i
					];
				}
				$x++;
			}
			//  echo '<pre>';
			//  //var_dump($temp_array);
			//  exit();
			$accordionData = $temp_array;
			//  $accordionData = $test->convertCarSpecificationDataImporterFormatToACF($temp_array);
			//  foreach($data['equipment'] as $key=>$value) {                
			//     $name = $key;
			//     $items = [];

			//     foreach($value as $v) {
			//         array_push($items, $v);
			//     }
			//     $data['accordion'][$x]['name'] = $name;
			//     $data['accordion'][$x]['items'] = $items;

			//     $x++;
			//  }

			$url = 'https://gw.partner.api.volvocars.biz/vehicle/vin/' . $vin . '?Authorization=Bearer%20' . $access_token . '&Api-version=2.0&Ocp-Apim-Subscription-Key=3cf34637fef3402b85c4cfb0210a5a5d';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $access_token,
				'Api-version:2.0',
				'Ocp-Apim-Subscription-Key: 3cf34637fef3402b85c4cfb0210a5a5d'
			]);
			$response = curl_exec($ch);
			$response = json_decode($response);

			$version = $response->responseDetails->vehicle->salesVersionDescription;
			$type = $response->responseDetails->vehicle->bodyTypeDescription;
			$color = str_replace('0', '', $response->responseDetails->vehicle->externalColourCode) . ' ' . $response->responseDetails->vehicle->externalColour;


			$id = wp_insert_post([
				'post_type' => 'stock-car',
				'post_status' => 'draft',
				'post_title' => '[VOLVOSELEKT] ' . $data['model'] . ' ' . $data['version'] . ' ' . $data['offerNumber'],
				'meta_input' => [
					'category' => $type,
					'gearbox' => ($response->responseDetails->vehicle->gearboxCode == 'D' ? 'Automatyczna' : 'Manualna'),
					'vin' => $data['vin'],
					'model' => $response->responseDetails->vehicle->modelDescription,
					'model_1' => $response->responseDetails->vehicle->modelDescription,
					
					'offer-number' => $data['offerNumber'],
					'has-discount-price' => $data['hasDiscountPrice'],
					'regular-price' => $data['regularPrice'],
					'income' => $data['income'],
					'cartype' => 'used',
					'income_najem' => $data['income_najem'],
					'pno' => $data['pno12'],
					'discount-price' => $data['discountPrice'],
					'pickup-time' => $data['pickupTime'],
					'mileage' => $data['mileage'],
					'car-distance' => $data['mileage'],
					'production-year' => $data['productionYear'],
					'lease_car' => $data['lease_car'],
					'najem_car' => $data['najem_car'],
					'color' => ucwords(strtolower($color)),
					'color_1' => ucwords(strtolower($color)),
					'inlay_1' => $data['inlay'],
					'inlay' => $data['inlay'],
					'engine' => $data['engine'],
					'engine_1' => $data['engine'],
					'version' => $version,
					'version_1' => $version,
					'max-power-text' => $data['maxPowerText'],
					'max-power' => $data['maxPower'],
					'fuel-type' => ($response->responseDetails->vehicle->fuelType == 'Petrol' ? 'Benzyna' : 'Diesel'),
					'acceleration' => $data['acceleration'],
					'fuel-consumption-unit' => $data['fuelConsumptionUnit'],
					'fuel-consumption' => $data['fuelConsumption'],
					'max-speed' => $data['maxSpeed'],
					'cargo-capacity' => $data['cargoCapacity'],
					'seats' => $data['seats'],
					'height' => $data['height'],
					'length' => $data['length'],
					'width' => $data['width'],
					'ground-clearance' => $data['groundClearance'],
					'accordion-heading' => $data['accordionHeading'],
					'dealer-name' => $data['dealerName'],
					'provider' => $data['provider'],
					'location' => $data['location'],
					'winter-tyre-labels' => json_encode($data['winterTyreLabels'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
					'gallery' => json_encode($data['gallery'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
					'accordion' => json_encode($data['accordion'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
					'created-date' => $data['createdDate'],
					'erange' => $data['erange'],
					'omnibus_date' => $data['omnibus_date'],
					'eurocode' => $data['eurocode'],
					'dealerphone' => $data['dealerphone'],
					'inlay_1' => $data['inlay'],
					'model_1' => $response->responseDetails->vehicle->modelDescription,
					'version_1' => $version,
					'color_1' => ucwords(strtolower($color)),
					'engine_1' => $data['engine'],
					'activemotors' => '1'

				]
			]);
			$imagesArr = [];
			$secondGallery = [];
			$x=0;
			foreach ($data['gallery'] as $image) {
				$file = $image->url;

				$filename = basename($image->url) . '.' . strtolower($image->extension);

				$upload_file = wp_upload_bits($filename, null, file_get_contents($file));
				if (!$upload_file['error']) {
					$wp_filetype = wp_check_filetype($filename, null);
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_parent' => $parent_post_id,
						'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attachment_id = wp_insert_attachment($attachment, $upload_file['file'], $parent_post_id);
					if (!is_wp_error($attachment_id)) {
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						$attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
						wp_update_attachment_metadata($attachment_id,  $attachment_data);
					}
					if ($x > 4 && $x<8) {
						array_push($secondGallery, $attachment_id);
					} else {
						array_push($imagesArr, $attachment_id);
					}
					$x++;
					
				}
			}
			$gallery = get_field('gallery', $post_id);
			update_field('field_6034b4fa1e81e',$secondGallery, $id);
			update_field('accordion', $accordionData, $id);
			update_field('field_602ba0b52720c', $imagesArr, $id);

			$x++;

					restore_current_blog();	
					$filename = date('Y-d-m-h:m');
					$response = ['auto o podanym VINIE '. $vin .' zostało zaimportowane'];
					file_put_contents('wikicars/select-motors-'.$filename, json_encode($response));
			}
		} else {
			
			$tmp = [$intId];
			$this->confirmImport($tmp,'export');
			$response = ['auto o podanym VINIE '. $vin .' już jest w systemie'];
			$filename = date('Y-d-m-h:m');
			file_put_contents('wikicars/select-motors-'.$filename, json_encode($response));
		}
	}
	public function importCars($data)
	{
		
		
		$token = file_get_contents('wikicars/token.json');
		if ($token) {
			$token = json_decode($token);
			$access_token = $token->access_token;
		}
		$filename = date('Y-d-m-h:m');
		file_put_contents('wikicars/active-motors-'.$filename, json_encode($data));
		$test = new CarSpecification();
		$x = 0;

		//	$reversed_array = array_reverse($data);
	
		
		$dealers = json_decode(file_get_contents('https://volvocarwarszawa.pl/api/getDealers'));
		
		foreach ($data as $data) {
			$multiple = false;
			$dealerId = preg_replace('/\s+/', '', $data->showroomId);

			$blog_id = null;
			foreach($dealers as $dealer) {				
				if ($dealerId == $dealer->dealerId || $dealerId == '6'.$dealer->dealerId) {
					$blog_id = $dealer->blog_id;
					$multiple = $dealer->multisalon;
				}
				if (!empty($dealer->showrooms) && in_array($dealerId, $dealer->showrooms)) {
					$blog_id = $dealer->blog_id;
					$multiple = $dealer->multisalon;
				}
			}

			
      		if ($blog_id) {
			
	
			$data = (array)$data;
			$import = $this->importCarToBlog($blog_id, $data, $multiple);
			
				
				

			}
		}
		exit('done');
	}
	public function getMileage()
	{
		if ($this->disable_dol) {
			return [];
		}
		$cache         = true;
		$options       = getBasicOptions(0);
		$upl = $this->upload_path;
		$check = $options['najem_0_najem_offer'][0];

		$offer    = $options['najem'][0];
		$settings = json_decode($this->getSettings());
		$variants = $settings->global_settings->Result;
		$offers   = array();
		// echo '<pre>';
		// //var_dump($options);
		// echo '</pre>';
		// die();
		if ($check) {
			$check = (int) explode(' - ', $check)[0];
		}

		$type          = 'najem';
		$settings_file = $upl . '' . $type . '.json';

		foreach ($variants as $variant) {
			$id = $variant->Id;

			if ($id == (int) $check) {

				if ($cache == false) {
					$token = $this->getToken();

					// getProduct details

					$request = $this->client->request(
						'GET',
						$this->api . 'products/' . $id,
						array(
							'headers'     => array(
								'Content-Type'  => 'application/json',
								'Authorization' => 'Bearer ' . $token[0],
								'AuthToken'     => $token[1],
							),
							'http_errors' => false,
							// 'query' => ['token' => $token],
						)
					);

					$response      = json_decode($request->getBody());
					$response_data = json_decode($response);

					file_put_contents($settings_file, $response);
					$data = $response;
				} else {
					$data = file_get_contents($settings_file);
				}
				$filterOut = json_decode($data);
			}
		}
		$tmp = array();

		$default_mileage = $filterOut->CfmParameters->DefaultMileageLimitId;
		$tmp['default']  = $default_mileage;
		$tmp['mileage']  = array();
		foreach ($filterOut->CfmParameters->CfmMileageLimits as $key => $value) {
			$tmp['mileage'][$value->Id] = $value->Limit;
		}

		return $tmp;
	}
	public function getAttractionSettings($type, $method = null)
	{
		if ($this->disable_dol) {
			return [];
		}
		$cache         = true;
		$upl = $this->upload_path;
		$options       = getBasicOptions(0);

		$settings_file = $upl . '' . $type . '.json';
		if ($method == null && !file_exists($settings_file)) {
			$cache = false;
			$this->updateSettings();						
		}
		$check         = null;
		
		
		switch ($type) {
			case 'najem':
				$check = $options['najem_0_najem_offer'][0];

				$offer = $options['najem'][0];
				break;
			case 'leasing':
				$check = $options['leasing_0_leasing_offer'][0];
				$offer = $options['leasing'][0];
				break;
		}
		
		if ($check) {
			$check = explode(' - ', $check)[0];
		}


		$settings = json_decode($this->getSettings());
		
		$variants = $settings->global_settings->Result;
		
		$offers   = array();
		// echo '<pre>';
		// //var_dump($options);
		// echo '</pre>'$this->api;
		// die();

		foreach ($variants as $variant) {
			$id = $variant->Id;
		
			if ($variant->Code == $check) {

				if ($cache == false) {
				
					$token = $this->getToken();
					// getProduct details

					$request = $this->client->request(
						'GET',
						$this->api . 'products/' . $id,
						array(
							'headers'     => array(
								'Content-Type'  => 'application/json',
								'Authorization' => 'Bearer ' . $token[0],
								'AuthToken'     => $token[1],
							),
							'http_errors' => false,
							// 'query' => ['token' => $token],
						)
					);

					$response      = json_decode($request->getBody());
					$response_data = json_decode($response);

					file_put_contents($settings_file, $response);
					$data = $response;
				} else {
					$data = file_get_contents($settings_file);
				}
				$filterOut = json_decode($data);
			}
		}

		// exit();

		$tmp = array();

		if (!$method) {
			foreach ($filterOut->Appeals as $s) {
				$tmp[$s->Level] = $s->DealerCommission;
			}
		} elseif ($method == 'installments') {

			foreach ($filterOut->Installments as $key => $value) {
				$tmp[$key] = $value;
			}
		} elseif ($method == 'entityFee') {
			$fee     = $filterOut->EntryFeeRange;
			$fee_min = $fee->Min ?? 0;
			$fee_max = $fee->Max ?? 0;
			$data    = range($fee_min, $fee_max, 1);

			return $data;
			
		}
	
		sort($tmp);
		return $tmp;
	}
	public function getAttractionDefault($type)
	{
		$options = getBasicOptions(0);
		if ($type == 'leasing') {
			$value = $options['leasing_0_default_income_leasing'][0];			
		} else {
			$value = $options['najem_0_default_income_najem'][0];
			
		}
		
		return $value;
	}
	public function getAttracion($type)
	{
		$upl = $this->upload_path;
		$post_id    = (isset($_GET['post']) ? $_GET['post'] : null);

		$car_status = get_field('cartype', $post_id);
		if ($car_status !== 'nowy') {
			return array();
		} else {
			$cache         = false;
			$settings_file = $upl . '' . $type . '.json';
			if (file_exists($upl . '' . $type . '.json')) {
				$cache = true;
			}

			$lease_offer = get_field('lease_car', $post_id);
			$najem_offer = get_field('najem_car', $post_id);
			
			$check       = null;

			if ($type = 'leasing') {
				$check = $this->filterLeaseOffer($post_id,$lease_offer);
			} elseif ($type = 'najem') {
				$check = $this->getNajemOffer($najem_offer);
			}
			if ($check &&  is_string($check)) {
				
				$check = explode(' - ', $check)[0];
				//$check = explode(' ', $check)[1];
				
			}
			$price           = get_field('regular-price', $post_id);
			$promotion_price = get_field('discount-price', $post_id);
			$settings        = json_decode($this->getSettings());
			$variants        = $settings->global_settings->Result;
			$offers          = array();

			foreach ($variants as $variant) {
				$id = $variant->Id;

				if ($id == (int) $check) {

					if ($cache == false) {
						$token = $this->getToken();
						// getProduct details

						$request = $this->client->request(
							'GET',
							$this->api . 'products/' . $id,
							array(
								'headers'     => array(
									'Content-Type'  => 'application/json',
									'Authorization' => 'Bearer ' . $token[0],
									'AuthToken'     => $token[1],
								),
								'http_errors' => false,
								// 'query' => ['token' => $token],
							)
						);

						$response = json_decode($request->getBody());

						$response_data = json_decode($response);
						if (!file_exists($settings_file)) {

							file_put_contents($settings_file, $response);
							$data = $response;
						} else {
							$data = file_get_contents($settings_file);
						}
					} else {
						$data = file_get_contents($settings_file);
					}
				}
			}
			$filterOut = json_decode($data);

			$tmp = array();
			foreach ($filterOut->Appeals as $s) {
				$tmp[$s->Level] = $s->DealerCommission;
			}

			// exit('aaaa');
			if ($promotion_price && $price !== $promotion_price) {
			}

			// //var_dump($lease);

			// $token = $this->token;
			sort($tmp);
			return $tmp;
		}

		return array();
	}
	public static function getPno12()
	{
		if (env('WP_ENV') === 'production') {

			$upl = '/var/www/volvocars-partner.pl/partners-site_v2/web/wikicars';
		} else {
			$upl = '/home/volvotest.pl/public_html/web/wikicars';
		}

		$post = (isset($_GET['post']) ? $_GET['post'] : null);
		$vin  = get_field('vin', $post) . '-production';
		$pno  = get_field('pno', $post);
		if (!$pno) {
			$path = $upl . '' . $vin;
			if (file_exists($path)) {
				$d    = file_get_contents($path);
				$data = ($d ? json_decode($d)->pno12 : null);
				if ($data) {

					update_field('pno', $data, $post);
					$cache      = true;
					$importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
					$import     = $importTool->importPno($cache);
				}
			}
		}
	}
	public function updateSettings()
	{
		$upload_folder = $this->upload_path;
		$cache = false;
		$token = $this->getToken();
		$data  = $this->getOffer($token, $cache, $upload_folder);
	}
	public function importPno($cache = true)
	{

		$token = $this->getToken();

		$csvFile = get_field('field_pno_csv_file', 'options-leasing');

		$options = getBasicOptions(0);
		$file_id = null;
		$file    = null;
		if (!empty($options['field_pno_csv_file'])) {
			$file_id = $options['field_pno_csv_file'][0];
			$file    = $options['pno_file_url'][0];
		}
		if ($file) {
			$file = parse_url($file);
		}

		if (!is_array($file)) {
			exit('no file with data is available');
		}

		// $upload_folder = wp_get_upload_dir()['basedir'];
		$upload_folder = $this->upload_path;

		// $csv_path  = explode('uploads',$csvFile['url']);
		$file_path = $upload_folder . $file['path'];

		$csv = new \ParseCsv\Csv();
		$csv->auto($file_path);
		$leasing_data = $this->upload_path;
		$sites        = wp_get_sites();
		$blog_ids     = array();
		foreach ($sites as $site) {
			array_push($blog_ids, $site['blog_id']);
		}

		foreach ($blog_ids as $v) {
			switch_to_blog($v);

			foreach ($csv->data as $s) {
				$s   = array_values($s);
				$pno = $s[0];

				$eurocode = $s[1];

				$posts = get_posts(
					array(
						'numberposts' => -1,
						'post_type'   => 'stock-car',
						'post_status' => array('publish', 'draft'),
						'meta_key'    => 'pno',
						'meta_value'  => $pno,
					)
				);

				if (!empty($posts)) {

					foreach ($posts as $post) {
						$post_id = $post->ID;

						update_field('eurocode', $eurocode, $post_id);
					}

					$leasing_file = $leasing_data . $pno . '.json';
					$file         = fopen($leasing_file, 'w');
					$txt          = 'data';
					fwrite($file, $txt);
					fclose($file);
					// $this->getCarOffer([]);
					// die();
				}
			}
			wp_reset_postdata();

			restore_current_blog();
		}
		if ($cache == false) {
			echo 'finished';
			exit();
		} else {
			return 'finished';
		}
	}
	public function getLeasingProducts($id = false)
	{
		$token = $this->getToken();
		
		// $upload_folder = wp_get_upload_dir()['basedir'];
		$upload_folder = $this->upload_path;

		
		$cache        = true;
		$data         = $this->getOffer($token, $cache, $upload_folder);
		
		$tmp          = array();
		if ($data) {
			foreach ($data as $s) {
				if ($id) {
					$tmp[$s->Code] = $s->Id;
				} else {
					$tmp[$s->Code] = $s->Id . ' ' . $s->Name . ' ' . ($s->Description !== null ? strip_tags(str_replace(' ', '', $s->Description)) : '');
				}
				
			}
		}
		return $tmp;
		//return array();
	}
	public function getBasicParameters($car_id,$id) {
		switch_to_blog(1);
		$najem = get_field('najem','acf_network_options');
		$leasing = get_field('leasing','acf_network_options');
		// var_dump($najem);
		restore_current_blog();
		return [$leasing, $najem];
	}
	public function filterNajemOffer($car_id,$key=null) {
		$result['none'] = 'Brak finansowania';
		$options        = getBasicOptions(0);
		$model = (get_field('model_1', $car_id) ? get_field('model_1', $car_id) : get_field('model', $car_id));
		if (!$model || $model == '') {
			return [];
		}
		$state = get_field('cartype', $car_id);
		$leasing_products = $this->getLeasingProducts(true);
		
		$lease_number   = (int) $options['najem'][0];
	
		$tmp = [];
		for ($i = 0; $lease_number > $i; $i++) {
			$limits_excl = [];
			$limits_include = [];
			$x             = $i + 1;
			$excluded_cars = $options['najem_' . $i . '_exclude_cars'][0];
			$tmp_key = $options['najem_'. $i .'_najem_offer'][0];
			
			if ($excluded_cars) {
				$excluded_cars = unserialize($excluded_cars);
				$limits = array_merge($limits_excl, $excluded_cars);
			} else {
				$excluded_cars = [];
			}
			$excluded_type = $options['najem_' . $i . '_exclude_state'][0];
			if ($excluded_type) {
				array_push($limits_include, $excluded_type);
			} else {
				$limits_include = ['nowy','used' ];
			}
		
			$price_variant = $options['najem_' . $i . '_najem_pricing'][0];
			if (!in_array($model, $excluded_cars) && in_array($state, $limits_include) ) {
					
					$pid = $leasing_products[$tmp_key];
					$price_variant = $options['najem_' . $i . '_najem_pricing'][0];
			
						$tmp[$tmp_key] = $pid.'----['.$pid.'--' . $price_variant . '] ' . $options['najem_' . $i . '_najem_offer'][0] . ' ' . $options['najem_' . $i . '_najem_description'][0];
			}
		}
		
		$tmp = array_merge($result, $tmp);
		return ($key ? $tmp[$key] : $tmp);
	}
	public function getDefaultValue($car_id, $type) {
		switch($type) {
			case 'leasing':
				$options = $this->filterLeaseOffer($car_id);
				$leasing_setup = get_post_meta($car_id, 'lease_car', true); //get_field('lease_car',$car_id);
				if (is_array($options) && !$leasing_setup || $leasing_setup == 'none' && count($options) > 1 ) {		

					return array_key_last($options);
				}

			break;
			case 'najem':
				$options = $this->filterNajemOffer($car_id);
				$najem_setup = get_post_meta($car_id, 'najem_car', true); //get_field('najem_car',$car_id);
				if (is_array($options) && !$najem_setup || $najem_setup == 'none' && count($options) > 1 ) {		

					return array_key_last($options);
				}
			break;
		}
		
		
		
		

		return 0;

	}
	public function filterLeaseOffer($car_id,$key=null) {
		
		$result['none'] = 'Brak finansowania';
		$options        = getBasicOptions(0);
		
		$model = (get_field('model_1', $car_id) ? get_field('model_1', $car_id) : get_field('model', $car_id));
		if (!$model || $model == '') {
			return;
		}
		$state = get_field('cartype', $car_id);
		$leasing_products = $this->getLeasingProducts(true);
		
		$lease_number = (int) $options['leasing'][0];
		$tmp = [];
		for ($i = 0; $lease_number > $i; $i++) {
			$limits_excl = [];
			$limits_include = [];
			$x             = $i + 1;
			$excluded_cars = $options['leasing_' . $i . '_exclude_cars'][0];
			$tmp_key = $options['leasing_'. $i .'_leasing_offer'][0];
			if ($excluded_cars) {
				$excluded_cars = unserialize($excluded_cars);
				$limits = array_merge($limits_excl, $excluded_cars);
			} else {
				$excluded_cars = [];
			}
			$excluded_type = $options['leasing_' . $i . '_exclude_state'][0];
			
			if ($excluded_type && $excluded_type !== 'null') {
			
				array_push($limits_include, $excluded_type);
			} else {
				$limits_include = ['nowy','used','null' ];
			}
			
			$price_variant = $options['leasing_' . $i . '_leasing_pricing'][0];
			if (is_string($excluded_cars)) {
				$excluded_cars = [];
			}
			if (!in_array($model, $excluded_cars) && in_array($state, $limits_include) ) {
					$pid = $leasing_products[$tmp_key];
				
					$tmp[$tmp_key] = $pid.'----[' . $price_variant . '] ' . $options['leasing_' . $i . '_leasing_offer'][0] . ' ' . $options['leasing_' . $i . '_leasing_description'][0];
			}
		}
		
		
		$tmp = array_merge($result, $tmp);
		return ($key ? $tmp[$key] : $tmp);
	}
	public function getLeaseOffer($key = null)
	{
		$result['none'] = 'Brak finansowania';
		$options        = getBasicOptions(0);

		$tmp          = array();
		$lease_number = (int) $options['leasing'][0];
		$tmp[0]       = 'Brak finansowania';
		$leasing_products = $this->getLeasingProducts();	
		// for ($i = 0; $lease_number > $i; $i++) {
		// 	$x             = $i + 1;
		// 	$price_variant = $options['leasing_' . $i . '_leasing_pricing'][0];

		// 	$tmp[$x] = '[' . $price_variant . '] ' . $options['najem_' . $i . '_najem_offer'][0] . ' ' . $options['najem_' . $i . '_najem_description'][0];
		// }
		
		$tmp[0] = 'Brak finansowania';
		foreach($leasing_products as $k=>$p) {
			$tmp[$k] = $p;
		}

		return ($key ? $tmp[$key] : $tmp);
	}
	public function getNajemOffer($key = null)
	{	
		$leasing_products = $this->getLeasingProducts();
		
		$result['none'] = 'Brak finansowania';
		$options        = getBasicOptions(0);
		$tmp            = array();
		$lease_number   = (int) $options['najem'][0];
		
		$tmp[0] = 'Brak finansowania';
		foreach($leasing_products as $k=>$p) {
			$tmp[$k] = $p;
		}
		
		// for ($i = 0; $lease_number > $i; $i++) {
		// 	$x             = $i + 1;
		// 	$price_variant = $options['najem_' . $i . '_najem_pricing'][0];
		// 	$tmp[$x]     = '[' . $price_variant . '] ' . $options['najem_' . $i . '_najem_offer'][0] . ' ' . $options['najem_' . $i . '_najem_description'][0];
		// }

		return ($key ? $tmp[$key] : $tmp);
	}
	private function leasingOffer()
	{
		$products = $this->getLeasingProducts();

		return $products;
	}

	private static $models = array(
		'XC40'              => 'XC40',
		'V50'				=> 'V50',
		'XC60'              => 'XC60',
		'XC90'              => 'XC90',
		'V90'               => 'V90',
		'V90 Cross Country' => 'V90 Cross Country',
		'V60'               => 'V60',
		'V60 Cross Country' => 'V60 Cross Country',
		'S90'               => 'S90',
		'S60'               => 'S60',
		'V40'               => 'V40',
		'C40'               => 'C40',
		'EX90'              => 'EX90',
		'EX30'              => 'EX30',
	);

	private static $modelCategories = array(
		'SUV'   => 'SUV',
		'Crossover' => 'Crossover',
		'Sedan' => 'Sedan',
		'Kombi' => 'Kombi',
	);

	private static $colors = array(
		'019 Black Stone'                              => '019 Black Stone',
		'612 Passion Red'                              => '612 Passion Red',
		'614 Ice White'                                => '614 Ice White',
		'619 Rebel Blue'                               => '619 Rebel Blue',
		'621 Amazon Blue'                              => '621 Amazon Blue',
		'467 Magic Blue'                               => '467 Magic Blue',
		'484 Seashell metallic'                        => '484 Seashell metallic',
		'492 Savile Grey'                              => '492 Savile Grey',
		'700 Twilight Bronze'                          => '700 Twilight Bronze',
		'702 Flamenco Red'                             => '702 Flamenco Red',
		'706 OCEAN BLUE II'                            => '706 OCEAN BLUE II',
		'707 Inscription Crystal White Pearl'          => '707 Inscription Crystal White Pearl',
		'708 Raw Copper'                               => '708 Raw Copper',
		'710 Misty'                                    => '710 Misty',
		'711 Bright Silver'                            => '711 Bright Silver',
		'712 Rich Java'                                => '712 Rich Java',
		'713 Power Blue'                               => '713 Power Blue',
		'714 Osmium Grey'                              => '714 Osmium Grey',
		'717 Onyx Black'                               => '717 Onyx Black',
		'719 Luminous Sand'                            => '719 Luminous Sand',
		'720 Bursting Blue'                            => '720 Bursting Blue',
		'721 Mussel Blue'                              => '721 Mussel Blue',
		'722 Maple Brown'                              => '722 Maple Brown',
		'723 Denim Blue'                               => '723 Denim Blue',
		'724 Pine Grey'                                => '724 Pine Grey',
		'725 Fusion Red'                               => '725 Fusion Red',
		'726 Birch Light'                              => '726 Birch Light',
		'727 Pebble Grey'                              => '727 Pebble Grey',
		'728 Thunder Grey'                             => '728 Thunder Grey',
		'729 Glacier Silver'                           => '729 Glacier Silver',
		'729 Magnesium'								   => '729 Magnesium',
		'731 Platinum Grey'                            => '731 Platinum Grey',
		'734 Fjord Blue'                               => '734 Fjord Blue',
		'735 Silver Dawn'                              => '735 Silver Dawn',
		'800062 Bursting Blue wykończony matową folią' => '800062 Bursting Blue wykończony matową folią',
		'477 Inscription Electric Silver'              => '477 Inscription Electric Silver',
		'487 Ember black pearl'                        => '487 Ember black pearl',
		'707 Crystal White'                            => '707 Crystal White',
		'733 Sage Green'                               => '733 Sage Green',
		'740 Vapour Grey'                              => '740 Vapour Grey',
		'626 Cloud Blue'                               => '626 Cloud Blue',
		'625 Moss Yellow'                              => '625 Moss Yellow',
		'743 Sand Dune'                                => '743 Sand Dune',
		'739 Mulberry Red'                             => '739 Mulberry Red',
		'73600 Bright Dusk'                            => '73600 Bright Dusk',

	);

	private static $engines = array(
		'T2 benzyna'                    => 'T2 benzyna',
		'T3'                            => 'T3',
		'T4'                            => 'T4',
		'T5 benzyna'                    => 'T5 benzyna',
		'T6 benzyna'                    => 'T6 benzyna',
		'D2'                            => 'D2',
		'D3'                            => 'D3',
		'D4'                            => 'D4',
		'D5'                            => 'D5',
		'B3 diesel'                     => 'B3 diesel',
		'B3 benzyna'                    => 'B3 benzyna',
		'B4 diesel'                     => 'B4 diesel',
		'B4 benzyna'                    => 'B4 benzyna',
		'B4 AWD diesel'                 => 'B4 AWD diesel',
		'B5 diesel'                     => 'B5 diesel',
		'B5 benzyna'                    => 'B5 benzyna',
		'B5 AWD diesel'                 => 'B5 AWD diesel',
		'B5 AWD benzyna'                => 'B5 AWD benzyna',
		'B6 AWD benzyna'                => 'B6 AWD benzyna',
		'T4 plug-in hybrid'             => 'T4 plug-in hybrid',
		'T5 plug-in hybrid'             => 'T5 plug-in hybrid',
		'T6 AWD plug-in hybrid'         => 'T6 AWD plug-in hybrid',
		'T8 AWD plug-in hybrid'         => 'T8 AWD plug-in hybrid',
		'T8 AWD Polestar Engineered'    => 'T8 AWD Polestar Engineered',
		'Twin elektryczny'              => 'Twin elektryczny',
		'Single elektryczny'            => 'Single elektryczny',
		'Single elektryczny Ext. range' => 'Single elektryczny Ext. range',
	);

	private static $versions = array(
		'Standard'            => 'Standard',
		'Momentum Core'       => 'Momentum Core',
		'Momentum'            => 'Momentum',
		'Momentum Pro'        => 'Momentum Pro',
		'R-Design'            => 'R-Design',
		'Inscription'         => 'Inscription',
		'Cross Country'       => 'Cross Country',
		'Cross Country Pro'   => 'Cross Country Pro',
		'Polestar Engineered' => 'Polestar Engineered',
		'Kinetic'             => 'Kinetic',
		'Summum'              => 'Summum',
		'Excellence'          => 'Excellence',
		'Essential'           => 'Essential',
		'Core'                => 'Core',
		'Plus Bright'         => 'Plus Bright',
		'Plus Dark'           => 'Plus Dark',
		'Ultimate Bright'     => 'Ultimate Bright',
		'Ultimate Dark'       => 'Ultimate Dark',
		'Plus'                => 'Plus',
		'Ultimate'            => 'Ultimate',
		'Recharge'            => 'Recharge',
		'Recharge Pro'        => 'Recharge Pro',
		'Ultra'               => 'Ultra',



	);

	private static $versionsDescriptions = array(
		'Momentum Core'          => 'Podstawowa wersja wyposażenia z czarnymi detalami zewnętrznymi i wnętrzem z siedzeniami z tapicerką tekstylną w tonacji czerni.',
		'Momentum'               => 'Będący znakiem rozpoznawczym komfort z najwyżej klasy detalami i dobrze wyposażonym standardowym wnętrzem.',
		'Momentum Pro'           => 'Będący znakiem rozpoznawczym komfort z najwyżej klasy detalami i dobrze wyposażonym standardowym wnętrzem oraz udogodnieniami klasy premium.',
		'Inscription Expression' => 'Esencja wyrafinowanej elegancji i odpowiedzialnego luksusu. Z chromowanymi elementami zewnętrznymi i naszym standardowym wnętrzem w wersji Momentum.',
		'Inscription'            => 'Najbardziej wyrafinowane wyposażenie. Chromowane detale zewnętrzne, zaawansowane funkcje komfortowe i ekskluzywne skórzane siedzenia tworzą absolutny skandynawski luksus.',
		'R-Design'               => 'Stworzony do aktywnej jazdy. Z czarnymi błyszczącymi elementami zewnętrznymi, sportowym zawieszeniem i stylowo wykończonym wnętrzem.',
		'Cross Country'          => 'Surowy, a zarazem elegancki wygląd. Duży prześwit, duże koła, unikatowy przód, kontrastujące nakładki nadkoli i osłony podwozia.',
		'Cross Country Pro'      => 'Przygotowany na przygody. Surowe, a zarazem eleganckie wzornictwo nadwozia, duży prześwit i napęd na wszystkie koła.',
		'Polestar Engineered'    => 'Czyste osiągi i wyrafinowana dynamika jazdy. Wyposażony w zawieszenie Öhlins, kute felgi i hamulce Brembo.',
	);

	private static $inlays = array(
		'Tekstylna'                  => 'Tekstylna',
		'Tekstylna premium'          => 'Tekstylna premium',
		'Tekstylno-winylowa'         => 'Tekstylno-winylowa',
		'Wełniania'                  => 'Wełniania',
		'Ze skóry ekologicznej'      => 'Ze skóry ekologicznej',
		'Ze skóry nappa'             => 'Ze skóry nappa',
		'Z tkaniny nubuk'            => 'Z tkaniny nubuk',
		'Skórzana'                   => 'Skórzana',
		'Skórzano-tekstylna'         => 'Skórzano-tekstylna',
		'Nubuck i skóra'             => 'Nubuck i skóra',
		'Wentylowana ze skóry Nappa' => 'Wentylowana ze skóry Nappa',
		'Alcantara'                  => 'Alcantara',
	);

	private static $gearboxes = array(
		'Automatyczna' => 'Automatyczna',
		'Manualna'     => 'Manualna',
	);


	public static function prepareVersions()
	{

		$opt = getBasicOptions(0);
		$versions = [];
		$version_number = $opt['taxonomy_versions_taxonomy_version_details'][0];
		for ($i = 0; $i < $version_number; $i++) {
			$v = $opt['taxonomy_versions_taxonomy_version_details_' . $i . '_taxonomy_version_color'][0];
			$versions[$v] = $v;
		}
		return $versions;
	}

	public static function prepareInlay()
	{

		$opt = getBasicOptions(0);
		$inlay = [];
		$inlay_number = $opt['taxonomy_layouts_taxonomy_layouts_details'][0];
		for ($i = 0; $i < $inlay_number; $i++) {
			$v = $opt['taxonomy_layouts_taxonomy_layouts_details_' . $i . '_taxonomy_layouts_color'][0];
			$inlay[$v] = $v;
		}

		return $inlay;
	}

	public static function prepareModels(string $category_slug = null)
	{
		global $wpdb;
		switch_to_blog(1);
		$car_ids = [];
		$models = [];
		
		if ($category_slug) {	
			global $wpdb;

			$SQLquery = "
				SELECT $wpdb->posts.ID FROM $wpdb->posts 
				LEFT JOIN $wpdb->term_relationships 
				ON ( $wpdb->posts.ID = $wpdb->term_relationships.object_id  ) 
				WHERE 1=1 
				AND ( $wpdb->term_relationships.term_taxonomy_id IN ( 2 ) ) 
				AND $wpdb->posts.post_type = 'model' 
				AND ( ( $wpdb->posts.post_status = 'publish' ) ) 
				GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC
			";

			$SQLquery = $wpdb->get_results($SQLquery,ARRAY_A);
			foreach($SQLquery as $car) {
				$id = $car['ID'];
				// $post = get_post($id);
				$name = get_field('short-name',$id);
				if (!array_key_exists($name,$models)) {
					$models[$name] = $name;
				}
			}
		} else {
			$data = getBasicOptions(1);
			// echo '<pre>';
			// var_dump($data);
			// exit();
			foreach($data as $k=>$v) {
				if ($k[0] !== '_' && strpos($k,'taxonomy_models_taxonomy_model_details_') !== false) {
					
					$models[$v] = $v;
					
				}
			}
			
			
		}
			
		restore_current_blog();
		return $models;
	}

	
  public static function prepareEngines(bool $electric = false) {
       
        $opt = getBasicOptions(0);
		
        $engines = [];
        $engine_number = $opt['taxonomy_engines_taxonomy_engines_details'][0];



		
        for($i=0; $i<$engine_number; $i++) {
            $v = $opt['taxonomy_engines_taxonomy_engines_details_'.$i.'_taxonomy_single_engine'][0];
		// var_dump($electric);
		// if (!$electric ) {
			
				$engines[$v] = $v;
			// }
        }
	
        return $engines;
    }
    public static function prepareColors() {
    
        $opt = getBasicOptions(0);
        $colors = [];
        $colors_number = $opt['taxonomy_colors_taxonomy_color_details'][0];
        for($i=0; $i<$colors_number; $i++) {
            $v = $opt['taxonomy_colors_taxonomy_color_details_'.$i.'_taxonomy_single_color'][0];
            $colors[$v] = $v;
        }
        return $colors;
    }
	public function prepareBoxes() {
		$options = getBasicOptions( 0 );

		$box_years = $options['vinomat_box'][0];

		$box_years_info = array();

		for ($i = 0; $i < $box_years; $i++) {
			$t = unserialize($options['vinomat_box_' . $i . '_vinomat_box_years'][0]);

			foreach ($t as $a) {
				$box_years_info[$options['vinomat_box_' . $i . '_vinomat_box_title'][0]] = $options['vinomat_box_' . $i . '_vinomat_box_title'][0];
			}
		}

		return $box_years_info;
	}
	public function getVinomatBoxes()
	{
		return self::prepareBoxes();
	}


	public static function getModels(string $category_slug = null): array
	{
			
			return self::prepareModels($category_slug);
		
	}

	public static function getColors(): array
    {
        return self::prepareColors();
    }

    public static function getEngines(bool $electric = false): array
    {
        return self::prepareEngines($electric);
    }

    public static function getVersions(): array
    {
        return self::prepareVersions();
    }

    public static function getInlays(): array
    {
        return self::prepareInlay();
    }

	public static function getGearboxes(): array {

		return self::$gearboxes;
	}

	public static function getModelCategories()
	{
		return self::$modelCategories;
	}

	public static function getVersionsDescriptions(): array
	{
		return self::$versionsDescriptions;
	}

	public static function getVersionDescription($version): ?string
	{
		return self::$versionsDescriptions[$version] ?? null;
	}
	public function getLeasingOffer(): array
	{
		return $this->leasingOffer();
	}
}
