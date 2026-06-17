<?php

namespace Classes;

use Classes\Exception\CarSpecificationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

use function Env\env;

class CarSpecificationDataImporter
{

	private $client;
	private $baseUrl;
	private $apiKey;

	public function __construct(Client $client)
	{

		$this->client = $client;
		if (env('WP_ENV') === 'production') {
			$this->baseUrl = 'https://volvo-dealer.pl/api/car-specification';
			$this->apiKey = '3bhupKFlkKKxsh39Ns1EMYaJUYxkVIUN6g5SXBl6SO4NFEke7.b06e6c60-c8c8-4508-84f3-6a186c48abf7';
		} else {
			// $this->baseUrl = 'https://volvo-dealer.pl/api/car-specification';
			$this->baseUrl = 'https://test.volvo-dealer.pl/api/car-specification';
			$this->apiKey = '3bhupKFlkKKxsh39Ns1EMYaJUYxkVIUN6g5SXBl6SO4NFEke7.e328d726-b51f-47c6-adeb-af5c32d22bfc';
		}
	}

	public function import($vin = null, $con = null): array
	{
		if (!$vin && !$con) {
			$this->throwException('No VIN or CON provided');
		}

		$DOLData = $this->getDOLData($vin, $con);

		return $this->groupDOLData($DOLData);
	}

	public function groupDOLData($data): array
	{
		$groupedData = array();
		$sections = array();

		if (array_key_exists('items', $data) && $data['items']) {
			foreach ($data['items'] as $item) {
				if (
					!$item['sectionName'] || $item['sectionName'] === 'UNDEFINED'
					|| !$item['sectionId'] || $item['sectionId'] === 'UNDEFINED'
					|| !$item['name'] || $item['name'] === 'UNDEFINED'
				) {
					continue;
				}

				if (!array_key_exists($item['sectionId'], $sections)) {
					$sections[$item['sectionId']] = array(
						'name' => $item['sectionName'],
						'items' => array(),
					);
				}

				$sections[$item['sectionId']]['items'][] = array(
					'code' => $item['code'],
					'name' => $item['name'],
				);
			}

			if (array_key_exists('OPTION_OTHERS', $sections)) {
				$this->moveToEndOfArray($sections, 'OPTION_OTHERS');
			}

			$groupedData['sections'] = $sections;
		}

		if (array_key_exists('tyreLabels', $data) && $data['tyreLabels']) {
			$tyreLabels = array();

			foreach ($data['tyreLabels'] as $label) {
				if (!array_key_exists($label['season'], $tyreLabels)) {
					$tyreLabels[$label['season']] = array();
				}
				$tyreLabels[$label['season']][] = array(
					'position' => $label['position'],
					'url' => $label['url'],
				);
			}

			$groupedData['tyreLabels'] = $tyreLabels;
		}

		if (array_key_exists('wltpFuelConsumption', $data) && $data['wltpFuelConsumption']) {
			$fuelConsumption = array();

			foreach ($data['wltpFuelConsumption'] as $item) {
				if ($item['name'] === 'fuelConsumption' || $item['name'] === 'weightedFuelConsumption') {
					$fuelConsumption['unit'] = str_replace(' ', '', $item['unit']);
					$fuelConsumption['value'] = $item['value'];
					break;
				}
			}
			$groupedData['fuelConsumption'] = $fuelConsumption;
		}


		return $groupedData;
	}
	public function getVinomatDol($vin, $type = false) {

        $query = [];

        if ($vin) {
            $query['vin'] = $vin;
            $filename = $vin;
        }
        $token = file_get_contents('/var/www/volvocars-partner.pl/partners-site/web/wikicars/token.json');
		
        if ($token) {
            $token = json_decode($token);
            $access_token = $token->access_token;
           
        }
	
        if (env('WP_ENV') === 'production') { 
            $path = '/var/www/volvocars-partner.pl/partners-site_v2/web/wikicars'.$filename.'-'.env('WP_ENV');
        } else {
            $path = '/home/volvotest.pl/public_html/web/wikicars/'.$filename.'-'.env('WP_ENV');
        }
		//var_dump($access_token);
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
	
		if ($type) {
			return $response->responseDetails;
		}
		if ($response) {
			
        return $response->responseDetails->vehicle->deliveryDate;
		}
		return [];
    }

	//     public function getDOLDataVin($vin = null, $con = null)
//     {
//         try {
//             $query = [];

	//             if ($vin) {
//                 $query['vin'] = $vin;
//                 $filename = $vin;
//             }

	//             if ($con) {
//                 $query['con'] = $con;
//                 $filename = $con;
//             }

	//             $path = ABSPATH . '../' .$filename.'-'.env('WP_ENV');


	//             $response = $this->client->request('GET', $this->baseUrl, [
//                 'query' => $query,
//                 'headers' => [
//                     'Content-Type' => 'application/json;charset=UTF-8',
//                     'Accept' => 'application/json',
//                     'ApiKey' => $this->apiKey,
//                   'http_errors' => false,
//             ],
//             'http_errors' => false

	//             ]);

	//             $body = $response->getBody();
//             $data = $body->getContents();
//             $f = fopen($path,'w');
//             fwrite($f,$data);
//             fclose($f);
//             // error_log('storing data',0);
//             if (is_array($data)) {
//               $results = false;
//           } else {

	//             $results = json_decode($data, true);
//         }


	//       	  return $results;
//     }
// }
	public function getDOLData($vin = null, $con = null)
	{
		try {
			$query = array();
			if ($con) {
				$query['con'] = $con;
				$filename = $con;
				$type = 'con';
			}
			if ($vin) {
				$query['vin'] = $vin;
				$filename = $vin;
				$type = 'vin';
			}

			if (env('WP_ENV') === 'production') {
				$path = '/var/www/volvocars-partner.pl/partners-site/web/wikicars/' . $filename . '-' . env('WP_ENV');
			} else {
				$path = '/home/volvotest.pl/public_html/web/wikicars/' . $filename . '-' . env('WP_ENV');
			}

			$response = $this->client->request(
				'GET',
				$this->baseUrl,
				array(
					'query' => $query,
					'headers' => array(
						'Content-Type' => 'application/json;charset=UTF-8',
						'Accept' => 'application/json',
						'ApiKey' => $this->apiKey,
					),
				)
			);

			$body = $response->getBody();
			$data = $body->getContents();
		
			$f = fopen($path, 'w');
			fwrite($f, $data);
			fclose($f);

			$results = json_decode($data, true);

			if (file_exists($path)) {
				// error_log('checking file',0);
				$pn = json_decode(file_get_contents($path));
				// error_log($pn->pno12,0);
				$pno12 = $pn->pno12;

			}
			$blog_id = get_current_blog_id();
			switch_to_blog($blog_id);
			$posts = get_posts(
				array(
					'numberposts' => -1,
					'post_type' => 'stock-car',
					'meta_key' => $type,
					'meta_value' => $filename,
				)
			);
			// error_log('checking '.$posts,0);
			if (!empty($posts)) {

				$post_id = $posts[0]->ID;
				// error_log(json_encode(get_post_meta($posts[0]->ID,'pno')));
				// error_log('updating file '.get_current_blog_id(),0);
				update_field('pno', $pno12, $post_id);
				update_post_meta($post_id, 'pno', $pno12);

				// error_log(update_post_meta($post_id, 'field_pno',$pno12),0);

			}

			wp_reset_postdata();

			restore_current_blog();

			return $results;
		} catch (ClientException $e) {
			$this->returnJsonError('Car not found', $e->getCode());
		} catch (GuzzleException $e) {
			$this->returnJsonError('Internal server error', $e->getCode());
		}
	}

	public function returnJsonError($data = null, $code = null, $options = 0): void
	{
		wp_send_json_error($data, $code, $options);
	}

	private function moveToEndOfArray(&$array, $element): void
	{
		$x = $array[$element];
		unset($array[$element]);
		$array[$element] = $x;
	}

	private function throwException($message, $code = 400): void
	{
		throw new CarSpecificationException($message, $code);
	}
}
