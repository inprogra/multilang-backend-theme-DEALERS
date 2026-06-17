<?php

namespace VGA\Classes;

use VGA\Classes\Exception\CarSpecificationException;
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;
use \GuzzleHttp\Exception\GuzzleException;

use function \Env\env;

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
