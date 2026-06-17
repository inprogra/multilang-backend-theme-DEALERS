<?php

namespace Classes;

use Classes\MultisiteFixer;
use Classes\ProductTagsMetaBox; 
use GuzzleHttp\Client;
use Shuchkin\SimpleXLSXGen;
use Spatie\ArrayToXml\ArrayToXml;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\DeliveryCategory;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;



class YouLead
{

	public function __construct()
	{

		add_action('parse_request', array($this, 'generateProductFeed'), 1, 100);
	}

	public static function sendLead($leadId)
	{

		$credentials = self::getCredentials();
		if (empty($credentials)) {
			return false;
		}
		$check = get_field('originUrl', $leadId);
		$parts = parse_url($check);
		parse_str($parts['query'], $query);

		$url  = 'http://a-' . $credentials['clientId'] . '.youlead.pl/api/Command/Contact/FillForm';
		$time = time();

		$lead      = self::getLead($leadId);

		$signature = sha1($credentials['clientId'] . $credentials['appId'] . $credentials['appSecretKey'] . $time);

		$headers = array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
			'YL-ClientId'  => $credentials['clientId'],
			'YL-AppId'     => $credentials['appId'],
			'YL-TimeStamp' => $time,
			'YL-Signature' => $signature,
		);

		$formData = array(
			'name'                => $lead['name'],
			'lastName'            => $lead['surname'],
			'phone'               => $lead['phoneNumber'],
			'email'               => $lead['email'],
			'message'             => $lead['message'],
			'referrer'            => $lead['referrer'],
			'zgoda_przetwarzanie' => $lead['dataProcessingConsent'],
			'zgoda_komunikacja'   => $lead['marketingContactConsent'],
			'zgoda_marketing'     => $lead['tradeContactConsent'],
			'utm_campaign'            => (array_key_exists('utm_campaign', $query) ? $query['utm_campaign'] : ''),
			'utm_medium'              => (array_key_exists('utm_medium', $query) ? $query['utm_medium'] : ''),
			'utm_source'              => (array_key_exists('utm_source', $query) ? $query['utm_source'] : ''),
			'utm_content'             => (array_key_exists('utm_content', $query) ? $query['utm_content'] : ''),

		);

		if (Showroom::isMultiShowroomAndService()) {
			$formData['showroom'] = $lead['showroom'];
		}

		if ($lead['origin'] === 'service') {
			$formData = array_merge(
				$formData,
				array(
					'vin'            => $lead['vin'],
					'productionYear' => $lead['productionYear'],
					'model'          => $lead['model'],
					'services'       => $lead['services'],
				)
			);
		}

		$body = array(
			'ylid'      => $lead['youLeadData']['ylid'],
			'formData'  => $formData,
			'sessionId' => $lead['youLeadData']['ylssid'],
			'pageUrl'   => $lead['originUrl'],
			'referrer'  => $lead['referrer'],
			'utm'       => $lead['youLeadData']['ylutm'],
			'formId'    => $lead['source'],
		);

		$client   = new Client();
		$response = $client->request(
			'POST',
			$url,
			array(
				'headers' => $headers,
				'body'    => json_encode($body),
			)
		);

		$options = get_fields('options-dealer');
		if ($options['facebook_int_settings'] && $options['facebook_int_settings']['fb_pixel'] && $options['facebook_int_settings']['fb_token']) {
			$pixel_id = $options['facebook_int_settings']['fb_pixel'];
			$access_token = $options['facebook_int_settings']['fb_token'];
			$api = Api::init(null, null, $access_token);
			$api->setLogger(new CurlLogger());

			$user_data = (new UserData())
				->setEmails(array(hash('sha256', $lead['email'])))
				->setPhones(array(hash('sha256', $lead['phoneNumber'])))
				->setLastNames(array(hash('sha256', $lead['surname'])))
				->setFirstNames(array(hash('sha256', $lead['name'])))
				->setClientIpAddress($_SERVER['REMOTE_ADDR'])
				->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);





			$event = (new Event())
				->setEventName('Lead')
				->setEventTime(time())
				->setEventSourceUrl($lead['originUrl'])
				->setUserData($user_data)
				->setActionSource(ActionSource::WEBSITE);

			$events = array();
			array_push($events, $event);

			$request = (new EventRequest($pixel_id))
				->setEvents($events);
			$send_fb = $request->execute();
		}




		return $response->getBody()->getContents();
	}
	public function getProductTags($post_id)
	{
  
    	$tags = wp_get_post_tags($post_id);
    	$tag_names = array();

	    if ($tags) {
    	    foreach ($tags as $tag) {
        	    $tag_names[] = $tag->name;  
        	}
    	}

    	return $tag_names;
	}

	public function generateProductFeed($query)
	{

		if ($query->request == 'api/product-feed-fb') {

			//	$resource = fopen( 'php://memory', 'w' );
			$products = $this->getProductsFB();
			$feed = new \Shuchkin\SimpleCSV();
			//	$xlsx = $feed::export($products);
			$output = fopen("php://output", 'w') or die("Can't open php://output");
			header("Content-Type:application/csv");
			header("Content-Disposition:attachment;filename=feed.csv");

			foreach ($products as $product) {
				fputcsv($output, $product);
			}

			fpassthru($output);
			// fclose($output) or die("Can't close php://output");
			exit();
		}
		if ($query->request == 'api/product-feed') {

			$resource = fopen('php://memory', 'w');
			$products = $this->getProducts();

			foreach ($products as $row) {
				fputcsv($resource, $row, ';');
			}
			fseek($resource, 0);
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="products.csv";');
			fpassthru($resource);
			die();
		}
	}
	public function getProductsFB()
	{

		//	switch_to_blog( MultisiteFixer::getCurrentBlogId() );

		$query = new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => '-1',
				'paged'          => 1,
			)
		);

		$fields = array(
			'id' 		   => 'id',
			'title'       => 'title',
			'description'  => 'description',
			'availability' => 'availability',
			'condition'    => 'condition',
			'price'    => 'price',
			'link'         => 'link',
			'image_link'   => 'image_link',
			'brand'     => 'brand',
			'tags'           => 'tags',
		);
		$csv    = array();
		$csv[]  = $fields;
		$id = 0;
		foreach ($query->posts as $post) {

			$row = array();

			foreach ($fields as $field => $fieldHeading) {
				if ($field == 'price') {
					$row[] = $this->getPrice($post->ID);
				} elseif ($field == 'image_link') {
					$imageUrl = $this->getProductImageUrl($post);

					if ($imageUrl) {
						$row[] = $imageUrl;
					} else {
						$row[] = '';
					}
				} elseif ($field == 'description') {
					$desc = (get_field('engine', $post->ID) ? get_field('engine', $post->ID) : get_field('engine_1', $post->ID));
					if (!$desc || $desc == '') {
						$dest = get_field('fuel-type', $post->ID);
					}
					$row[] = $desc;
				} elseif ($field == 'availability') {
					$row[] = 'in stock';
				} elseif ($field == 'brand') {
					$row[] = get_bloginfo('name');
				} elseif ($field == 'id') {
					$row[] = 'offer_' . $post->ID;
				} elseif ($field == 'link') {
					$row[] = MultisiteFixer::buildUrl(get_permalink($post->ID));
				} elseif ($field == 'tags') {
					$tags = $this->getProductTags($post->ID); 
					$formatted_tags = !empty($tags) ? "'" . implode("','", $tags) . "'" : "'Brak tagów'"; 
					$row[] = $formatted_tags;
				} else {
					if ($field == 'title') {
						$row[] = $post->post_title;
					}
					if ($field == 'condition') {
						$row[] = (get_field('field_car_type', $post->ID) == 'nowy' ? 'new' : 'used');
					}
					// $row[] = get_field( $field, $post->ID );
				}
			}
			$id++;
			$csv[] = $row;
		}

		restore_current_blog();

		return $csv;
	}
	public function getProductsShorDefaultXml($type = null)
	{
		if ($type == null) {
			$type = 'default';
		} else {
			$type = 'used';
		}

		$data = $this->getProductsShortWithKeys($type);

		return $data;
	}
	public function getProductsShortXml($type)
	{	
		
		$data = $this->getProductsShortWithKeys($type);


		return $data;
	}
	public function getProductsShortXmlCustom()
	{
		$data = $this->getProductsShortWithKeysCustom();


		return $data;
	}
	public function getProductsShortWithKeysCustom()
	{
		$query = new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => '-1',
				'paged'          => 1,
			)
		);


		$x = 0;
		$ch = [];
		$rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
		// $rssfeed .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">';
		$rssfeed .= '<listings>';
		$rssfeed .= '<title>Cars feed</title>';	
		$productTagsMetaBox = new ProductTagsMetaBox();	
		foreach ($query->posts as $post) {
			$price = get_field('regular-price', $post->ID);
			$discount_price = get_field('discount-price', $post->ID);
			$imageUrl = $this->getProductMainImageDefaultUrl($post);
			$tags = $productTagsMetaBox->get_post_tags($post->ID);
			$formatted_tags = (!empty($tags) && is_array($tags)) ? "'" . implode("','", $tags) . "'" : 'Brak tagów';

			
			$rssfeed .= '<listing>';
			$rssfeed .= '<vehicle_id>' . get_field('offer-number', $post->ID) . '</vehicle_id>';
			$rssfeed .= '<title>' . get_field('model', $post->ID) . '</title>';
			$rssfeed .= '<offer_description>' . get_field('model', $post->ID) . '</offer_description>';
			$rssfeed .= '<mileage>';
			$rssfeed .= '<unit>KM</unit>';
			$rssfeed .= '<value>0</value>';
			$rssfeed .= '</mileage>';
			$rssfeed .= '<body_style>OTHER</body_style>';
			$rssfeed .= '<fuel>PETROL</fuel>';
			$rssfeed .= '<product_tags_1>' .			get_field('fuel-type', $post->ID) . '</product_tags_1>';
			$rssfeed .= '<product_tags_2>' . get_field('gearbox', $post->ID) . '</product_tags_2>';
			$rssfeed .= '<description>' . get_the_title($post->ID) . '</description>';
			$rssfeed .= '<year>' . get_field('production-year', $post->ID) . '</year>';
			$rssfeed .= '<price>' . $price . '</price>';
			$rssfeed .= '<product_sale_price>' . $discount_price . '</product_sale_price>';
			$rssfeed .= '<image><url>' . $imageUrl . '</url></image>';
			$rssfeed .= '<url>' . MultisiteFixer::buildUrl(get_permalink($post->ID)) . '</url>';
			$rssfeed .= '<address format="simple"><component name="country">Poland</component></address>';
			$rssfeed .= '<make>Volvo</make>';
			$rssfeed.= '<state_of_vehicle>' . (get_field('field_car_type', $post->ID) == 'nowy' ? 'NEW' : 'USED') . '
            </state_of_vehicle>';
			$rssfeed .= '<availability>in Stock</availability>';
			$rssfeed .= '<product_tags>' . $formatted_tags . '</product_tags>';
			$rssfeed .= '</listing>';

			$x++;
		}
		$rssfeed .= '</listings>';
		// $rssfeed .= '</rss>';
		restore_current_blog();


		return $rssfeed;
	}
	public function getProductsShortWithKeys($type = null)
	{

		
		if ($type !== 'default') {
		$query = new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => '-1',
				'paged'          => 1,
				'meta_query'    => array(
					'relation'      => 'AND',
					array(
						'key'       => 'cartype',
						'value'     => 'used',
						'compare'   => '='
					),
				)
			)
		);
	} else {
		$query = new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => '-1',
				'paged'          => 1,
				
			)
		);
	}
		$fields = array(
			'vehicle_id'    => 'ID (nr oferty)',
			'title'			=> 'Title',
			'description'	=> 'Description',

			'price'			=> 'Price',
			'sale_price' => 'Cena promocyjna',
			'image'         => 'URL zdjęcia',
			'url'        => 'URL strony',
			'version'         => 'Wersja wyposażenia',
			'active'		  => 'Status',
			'brand'			  => 'Marka',
			'status'		  => 'Status pojazdu'
		);
		$csv    = array();
		$csv[]  = $fields;
		$x = 0;
		$ch = [];
		$rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
		$rssfeed .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">';
		$rssfeed .= '<channel>';
		if ($_GET['preview']) {
			$rssfeed = '<div style="display:flex;flex-wrap: wrap;">';
		}
		$productTagsMetaBox = new ProductTagsMetaBox();

		foreach ($query->posts as $post) {
			$price = get_field('regular-price', $post->ID);
			$discount_price = get_field('discount-price', $post->ID);
			if ( $type == 'default') {
				$imageUrl = $this->getProductMainImageDefaultUrl($post);
			} else {
				$imageUrl = $this->getProductMainImageUrl($post);
			}
			$tags = $productTagsMetaBox->get_post_tags($post->ID);
      	$formatted_tags = !empty($tags) ? "'" . implode("','", $tags) . "'" : '';

		  if ($_GET['preview']) {
			$rssfeed .= '<ul style="max-width:25%;">';
			$rssfeed .= '<li>' . get_field('offer-number', $post->ID) . '</li>';
			$rssfeed .= '<li>' . get_field('model', $post->ID) . '</li>';
			$rssfeed .= '<li>' . get_the_title($post->ID) . '</li>';
			$rssfeed .= '<li>' . $price . '</li>';
			$rssfeed .= '<li>' . $discount_price . '</li>';
			$rssfeed .= '<li><img style="max-width:300px;" src="' . $imageUrl . '"/></li>';
			$rssfeed .= '<li><a  target="_blank" href="' . MultisiteFixer::buildUrl(get_permalink($post->ID)) . '">Link do samochodu</a></li>';
			$rssfeed .= '<li>' . (get_field('version', $post->ID) ? get_field('version', $post->ID) :
				get_field('version_1', $post->ID)) . '</li>';
			$rssfeed .= '<li>' . get_field('category', $post->ID) . '</li>';
			$rssfeed .= '<li>' . get_field('gearbox', $post->ID) . '</li>';
			$rssfeed .= '<li>' .
				get_field('fuel-type', $post->ID) . '</li>';
			$rssfeed .= '<li>' . get_field('production-year', $post->ID) . '</li>';
			$rssfeed .= '<li>Volvo</li>';
			$rssfeed .= '<li>' . (get_field('field_car_type', $post->ID) == 'nowy' ? 'new' : 'used') . '
            </g:condition>';
			$rssfeed .= '<li>in Stock</li>';
			$rssfeed .= '<li>' . $formatted_tags . '</internal_label>'; 


			$rssfeed .= '</ul>';
		  } else {
			$rssfeed .= '<item>';
			$rssfeed .= '<g:id>' . get_field('offer-number', $post->ID) . '</g:id>';
			$rssfeed .= '<g:title>' . get_field('model', $post->ID) . '</g:title>';
			$rssfeed .= '<g:description>' . get_the_title($post->ID) . '</g:description>';
			$rssfeed .= '<g:price>' . $price . '</g:price>';
			$rssfeed .= '<g:sale_price>' . $discount_price . '</g:sale_price>';
			$rssfeed .= '<g:image_link>' . $imageUrl . '</g:image_link>';
			$rssfeed .= '<g:link>' . MultisiteFixer::buildUrl(get_permalink($post->ID)) . '</g:link>';
			$rssfeed .= '<g:version>' . (get_field('version', $post->ID) ? get_field('version', $post->ID) :
				get_field('version_1', $post->ID)) . '</g:version>';
			$rssfeed .= '<g:type>' . get_field('category', $post->ID) . '</g:type>';
			$rssfeed .= '<g:gearbox>' . get_field('gearbox', $post->ID) . '</g:gearbox>';
			$rssfeed .= '<g:fuel>' .
				get_field('fuel-type', $post->ID) . '</g:fuel>';
			$rssfeed .= '<g:year>' . get_field('production-year', $post->ID) . '</g:year>';
			$rssfeed .= '<g:brand>Volvo</g:brand>';
			$rssfeed .= '<g:condition>' . (get_field('field_car_type', $post->ID) == 'nowy' ? 'new' : 'used') . '
            </g:condition>';
			$rssfeed .= '<g:availability>in Stock</g:availability>';
			$rssfeed .= '<internal_label>' . $formatted_tags . '</internal_label>'; 

			$rssfeed .= '</item>';
		  }
			
			// $row = array();

			// unset($row['availability']);
			// unset($row['status']);
			// unset($row['brand']);
			// // unset($row[6]);
			// //$row = array_values($row);
			// if ($x == 0 ) {
			// $csv['item'] = $row;
			// } else {
			// $csv['item_'.$x] = $row;
			// }

			$x++;
		}
		if ($_GET['preview']) {
			$rssfeed .= '</div>';
			echo $rssfeed;
			exit('');
		} else {
			$rssfeed .= '</channel>';
			$rssfeed .= '</rss>';
		}
		
		restore_current_blog();


		return $rssfeed;
	}
	public function getProductsShort()
{
	$query = new \WP_Query(
		array(
			'post_type' => 'stock-car',
			'posts_per_page' => '-1',
			'paged' => 1,
		)
	);

	$fields = array(
		'offer-number' => 'ID (nr oferty)',
		'model'        => 'Nazwa',
		'gallery'      => 'URL zdjęcia',
		'page-url'     => 'URL strony',
		'version'      => 'Wersja wyposażenia',
		'active'       => 'Status',
		'brand'        => 'Marka',
		'status'       => 'Status pojazdu',
		'tags'         => 'Tagi'
	);

	$csv = array();
	$csv[] = $fields;

	$productTagsMetaBox = new \Classes\ProductTagsMetaBox();

	foreach ($query->posts as $post) {
		$imageUrl = $this->getProductMainImageUrl($post);
		$row = array();

		foreach ($fields as $field => $fieldHeading) {
			switch ($field) {
				case 'brand':
					$row[] = 'Volvo';
					break;

				case 'gallery':
					$row[] = $imageUrl ?: '';
					break;

				case 'page-url':
					$row[] = MultisiteFixer::buildUrl(get_permalink($post->ID));
					break;

				case 'active':
					$row[] = ($post->post_status === 'publish' ? '1' : '0');
					break;

				case 'model':
					$realField = get_field('model', $post->ID) ? 'model' : 'model_1';
					$row[] = get_field($realField, $post->ID);
					break;

				case 'version':
					$realField = get_field('version', $post->ID) ? 'version' : 'version_1';
					$row[] = get_field($realField, $post->ID);
					break;

				case 'status':
					$row[] = get_field('field_car_type', $post->ID);
					break;

				case 'tags':
					$tags = $productTagsMetaBox->get_post_tags($post->ID);
					$row[] = !empty($tags) ? implode(', ', $tags) : '';
					break;

				default:
					$row[] = get_field($field, $post->ID);
					break;
			}
		}

		$csv[] = $row;
	}

	restore_current_blog();

	return $csv;
}

	public function getProducts()
	{

		// switch_to_blog( MultisiteFixer::getCurrentBlogId() );

		$query = new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => '-1',
				'paged' => 1,
			)
		);

		$fields = array(
			'offer-number' => 'ID (nr oferty)',
			'model' => 'Model',
			'category' => 'Rodzaj nadwozia',
			'gearbox' => 'Skrzynia biegów',
			'production-year' => 'Rok',
			'fuel-type' => 'Rodzaj paliwa',
			'version' => 'Wersja wyposażenia',
			'regular-price' => 'Cena (regularna)',
			'discount-price' => 'Cena (promocyjna)',
			'profit-price' => 'Korzyść',
			'gallery' => 'URL zdjęcia',
			'page-url' => 'URL strony',
		);
		$csv = array();
		$csv[] = $fields;

		foreach ($query->posts as $post) {
			$row = array();
			foreach ($fields as $field => $fieldHeading) {
				if ($field == 'profit-price') {
					$row[] = $this->getProfitPrice($post);
				} elseif ($field == 'gallery') {
					$imageUrl = $this->getProductImageUrl($post);

					if ($imageUrl) {
						$row[] = $imageUrl;
					} else {
						$row[] = '';
					}
				} elseif ($field == 'page-url') {
					$row[] = MultisiteFixer::buildUrl(get_permalink($post->ID));
				} else {

					if ($field == 'active') {
						$row[] = ($post->post_status == 'publish' ? '1' : '0');
					}

					if ($field == 'model') {
						$field = (get_field('model', $post->ID) ? 'model' : 'model_1');
					}
					if ($field == 'version') {
						$field = (get_field('version', $post->ID) ? 'version' : 'version_1');
					}
					$tags = $productTagsMetaBox->get_post_tags($post->ID);
					$formatted_tags = !empty($tags) ? "'" . implode("','", $tags) . "'" : '';
				//	if ($field == 'status') {
				//		$row['status'] = get_field('field_car_type', $post->ID);
				//	}

					$row[] = get_field($field, $post->ID);
				}
			}
			unset($row[6]);
			unset($row[8]);
			$row = array_values($row);
			$csv[] = $row;
		}

		restore_current_blog();

		return $csv;
	}

	private static function getCredentials()
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$credentials = get_field('you-lead', 'options-dealer');
		restore_current_blog();

		if (! $credentials['client-id'] || ! $credentials['app-id'] || ! $credentials['app-secret-key']) {
			return array();
		}

		return array(
			'clientId' => $credentials['client-id'],
			'appId' => $credentials['app-id'],
			'appSecretKey' => $credentials['app-secret-key'],
		);
	}

	private static function getLead($leadId)
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$meta = array();
		$metaFields = array(
			'origin',
			'originUrl',
			'source',
			'name',
			'surname',
			'phoneNumber',
			'email',
			'showroom',
			'referrer',
			'message',
			'vin',
			'productionYear',
			'model',
			'services',
			'dataProcessingConsent',
			'tradeContactConsent',
			'marketingContactConsent',
			'youLeadData'
		);
		foreach ($metaFields as $key => $value) {
			$meta[$value] = get_field($value, $leadId);
		}
		$showroomName = get_field('name', $meta['showroom']);
		restore_current_blog();

		return array(
			'origin' => $meta['origin'],
			'originUrl' => $meta['originUrl'],
			'source' => $meta['source'],
			'name' => $meta['name'],
			'surname' => $meta['surname'],
			'phoneNumber' => $meta['phoneNumber'],
			'email' => $meta['email'],
			'showroom' => $showroomName,
			'referrer' => $meta['referrer'],
			'message' => $meta['message'],
			'vin' => $meta['vin'],
			'productionYear' => $meta['productionYear'],
			'model' => $meta['model'],
			'services' => ($meta['services'] ? join(', ', $meta['services']) : ''),
			'dataProcessingConsent' => ! ! $meta['dataProcessingConsent'],
			'tradeContactConsent' => ! ! $meta['tradeContactConsent'],
			'marketingContactConsent' => ! ! $meta['marketingContactConsent'],
			'youLeadData' => json_decode($meta['youLeadData'], true),
		);
	}
	private function getXmlPrice($postId): int
	{
		$regularPrice = get_field('regular-price', $postId);
		$discountPrice = get_field('discount-price', $postId);

		return ($discountPrice ? $discountPrice : $regularPrice);
	}
	private function getProfitPrice($postId): int
	{
		$regularPrice = get_field('regular-price', $postId);
		$discountPrice = get_field('discount-price', $postId);

		return $regularPrice - $discountPrice;
	}
	private function getPrice($postId): int
	{
		$regularPrice = get_field('regular-price', $postId);
		$discountPrice = get_field('discount-price', $postId);

		if (!$discountPrice) {
			return (int)($regularPrice ? $regularPrice : '0');
		} else {
			return (int)($discountPrice ? $discountPrice : '0');
		}
		// return $regularPrice - $discountPrice;
	}
	private function getProductMainImageDefaultUrl($postId): ?string
	{
		$gallery = get_field('images', $postId);

		if (empty($gallery)) {
			return null;
		}

		$image = ($image ? $image : $gallery[0]);
		$image = PictureBuilder::getImage($image, 'full');

		return $image['src'];
	}
	private function getProductMainImageUrl($postId): ?string
	{
		$gallery = get_field('images', $postId);
		$social_image = get_field('social_image', $postId);

		if (empty($gallery)) {
			return null;
		}
		$image = null;
		if (!empty($social_image)) {
			$gallery = $social_image;
		}
		foreach ($gallery as $i) {
			$attachment_meta = get_post_field('post_content', $i);
			if ($attachment_meta == 'fb' || !empty($social_image)) {
				$image = $i;
			}
		}

		$image = ($image ? $image : $gallery[0]);
		$image = PictureBuilder::getImage($image, 'full');

		return $image['src'];
	}
	private function getProductImageUrl($postId): ?string
	{

		$gallery = get_field('gallery', $postId);
		var_dump($gallery);
		if (empty($gallery)) {
			return null;
		}


		$image = $gallery[0];

		$image = PictureBuilder::getImage($image, 'full');

		return $image['src'];
	}
}
