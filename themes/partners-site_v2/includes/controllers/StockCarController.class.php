<?php

namespace Controllers;

use Classes\CarDictionary;
use Classes\Controller;
use Classes\FeaturedCars;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;
use Classes\Showroom;
use Hashids\Hashids;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use \Classes\Cache;

use function Env\env;

class StockCarController extends Controller
{

	private $cache;
	private $disable_dol;
	public function __construct()
	{
		$this->cache = new \Classes\Cache();
	}
	public function gcd($a, $b)
	{	
		if ($b == NULL || $b == 0) {
			return false;
		}
		
		return ($a % $b) ? $this->gcd($b, $a % $b) : $b;
	}
	public function ratio($x, $y)
	{
		$gcd = $this->gcd($x, $y);
		
		if ($gcd) {
			return ($x / $gcd) . ':' . ($y / $gcd);
		} 
	}
	public function render(): string
	{
		$pimg = new ImageBuilder(-1, false);
		$blog_id = get_current_blog_id();
		$this->disable_dol = $GLOBALS['disable_dol'];
		if (env('WP_ENV') === 'production') {
			$upl = '/var/www/volvocars-partner.pl/partners-site/web/wikicars/';
		} else {
			$upl = '/home/volvotest.pl/public_html/web/wikicars/';
		}
		$settings_file = $upl . 'leasing.json';

		$settings_data = json_decode(file_get_contents($settings_file));

		$options = getBasicOptions(0);
		$dealer_com = array();
		foreach ($settings_data->Appeals as $s) {
			$dealer_com[$s->Level] = $s->DealerCommission;
		}
		$installments = array();

		foreach ($settings_data->Installments as $i) {
			array_push($installments, $i);
		}

		$model = (get_field('model') ? get_field('model') : get_field('model_1'));
		$imageFormat = (!empty(get_field('picture_format')) ? true : false);
		
		$carImages = get_field('images');
		$carGallery = [];
		$galleryThumbs = [];
		foreach ($carImages as $itemId) {

			$img_id = $itemId;
			$itemId = wp_get_attachment_url($itemId);
			$itemId = $this->clearUrl($itemId);
			$width = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($itemId)['path'])[0];
			$height = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($itemId)['path'])[1];
			
			if (!$imageFormat) {
				$ratio = $this->ratio($width, $height);

				if ($ratio == '4:3') {
					$imageFormat = true;
				}
			}

			$status = false;
			$imageName = basename($itemId);


			if ($imageFormat || (int)$ratio > 16) {									
				$images = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'format_height' => '1024',
						'format_width'  => '768',
						'height' => 1080,
						'width' => 1920,
						'crop' =>  'crop',
						'image' => $itemId,
						'query' => 1680,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,																		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'format_height' => '1024',
						'format_width'  => '768',
						'height' => 700,
						'width' => 1440,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1000,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'format_height' => '1024',
						'format_width'  => '768',
						'height' => false,
						'width' => 1000,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					]
				];
				$imagesThumbs = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 1920,
						'width' => 1080,
						'crop' =>  false,
						'image' => $itemId,
						'query' => 1680,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 1024,
						'width' => 768,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1000,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => false,
						'width' => 500,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					]
				];
				$images = $pimg->prepareImages($images);	
				$carGallery[] = $images;
				$images = $pimg->prepareImages($imagesThumbs);	
				$galleryThumbs[] = $images;
				$images = $pimg->prepareImages($images);	

			} else {
				$images = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'format_height' => '1080',
						'format_width'  => '1920',
						'height' => 1080,
						'width' => 1920,
						'crop' =>  false,
						'image' => $itemId,
						'query' => 1680,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'format_height' => '1080',
						'format_width'  => '1920',
						'height' => 700,
						'width' => 1440,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1000,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'format_height' => '1080',
						'format_width'  => '1920',
						'height' => false,
						'width' => 1000,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					]
				];
				$imagesThumbs = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 1920,
						'width' => 1080,
						'crop' =>  false,
						'image' => $itemId,
						'query' => 1680,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 1024,
						'width' => 768,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1000,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => false,
						'width' => 500,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100,										
						'theight' => 300,
						'twidth' => 200,
						'tcrop' =>  false,		
					]
				];
				$images = $pimg->prepareImages($images);	
				$carGallery[] = $images;
				$images = $pimg->prepareImages($imagesThumbs);	
				$galleryThumbs[] = $images;
				$images = $pimg->prepareImages($images);	
								

			}
		}

		$summerTyreLabels = get_field('summer-tyre-labels');
		if (!$summerTyreLabels) {
			$summerTyreLabels = [];
		}
		foreach ($summerTyreLabels as $label) {
			$carGallery[] = array(
				'image' => array(
					'sizes' => array(
						array(
							'width' => 808,
							'height' => 453,
							'src' => $label['url'],
						),
					),
				),
				'full' => array(
					'width' => 808,
					'height' => 453,
					'src' => $label['url'],
				),
				'thumbnail' => array(
					'sizes' => array(
						array(
							'width' => 258,
							'height' => 143,
							'src' => $label['url'],
						),
					),
				),
			);
		}
//exit();
		$galleryImages = get_field('gallery');
		$gallery = array();
		$gall = [];
		foreach ($galleryImages as $itemId) {
			$img_id = $itemId;
			$itemId = wp_get_attachment_image_url($itemId);
			$itemId = $this->clearUrl($itemId);
			$images = [
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 1080,
					'width' => 1920,
					'crop' =>  false,
					'image' => $itemId,
					'query' => 1680,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 700,
					'width' => 1440,
					'crop' => 'false',
					'image' => $itemId,
					'query' => 1000,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => false,
					'width' => 1000,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 100,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				]
			];
			$imagesThumbs = [
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 1920,
					'width' => 1080,
					'crop' =>  false,
					'image' => $itemId,
					'query' => 1680,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 1024,
					'width' => 768,
					'crop' => 'false',
					'image' => $itemId,
					'query' => 1000,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => false,
					'width' => 500,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 100,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				]
			];
			$images = $pimg->prepareImages($images);	
			$gall[] = $images;
		}
	
		$accordion = $this->mapToAccordion(get_field('accordion'));

		$hasWinterTyreLabels = !empty(get_field('winter-tyre-labels'));

		if ($hasWinterTyreLabels) {
			$accordion[] = $this->getTyreLabelsAccordionElement(get_the_ID());
		}

		$featuredCars = $this->getFeaturedCars(get_the_ID());

		switch_to_blog(1);
		$featuredCarsOptions = get_field('featured-cars', 'options-global');
		restore_current_blog();
		$length = 24;
		$payment = 20;
		$distance = 10;
		$carprice = get_field('regular-price');

		if ($payment !== 0) {
			$price = ($carprice - (($payment / 100) * $carprice)) / 0.7;

			$rate_najem = number_format((((($price / $length)) * ($distance / 1000))) * 10, 2, '.', '');
		}
		$offers = new CarDictionary(new \GuzzleHttp\Client());
		$leasing_variant = get_field('lease_car');
		$najem_variant = get_field('najem_car');

		// $leasing_variant = 0;
		// $najem_variant = 0;
		$lease = null;
		$najem = null;
		$settings = json_decode($offers->getSettings());
		if ($settings) {
			foreach ($settings->global_settings->Result as $s) {

			}
		}

		$leasing_options = unserialize($options['leasing_0_fee_leasing'][0]);
		$default_leasing_fee = $options['leasing_0_default_fee_leasing'][0];
		$default_installment = $options['leasing_0_default_installment_leasing'][0];
		$rates_leasing = unserialize($options['leasing_0_rates_leasing'][0]);

		$najem_settings_file = $upl . 'najem.json';
		$najem_settings = json_decode(file_get_contents($najem_settings_file));
		$car_id = get_the_ID();
		if ($leasing_variant == "none") {
			$leasing_variant = 0;
		}
		if ($leasing_variant > 0) {

			$lease_id = $offers->filterLeaseOffer($car_id, $leasing_variant);
			$lease_id = explode('----', $lease_id)[0];


			$lease = $offers->getLeaseOffer($leasing_variant);
			$lease = explode(' ', $lease);
			foreach ($settings->global_settings->Result as $s) {
				if ($lease[1] == $s->Code) {
					$lease_id = $s->Id;
				}
			}


			$lease = explode(']', $lease[0]);
			$lease = str_replace('[', '', $lease[0]);
			$lease = 'netto';
		} else {
			// $lease    = $offers->getLeaseOffer( 1 );
			// $lease    = explode( ' ', $lease );
			// $lease_id = $lease[1];

			// $lease = explode( ']', $lease[0] );
			// $lease = str_replace( '[', '', $lease[0] );
		}

		if ($najem_variant > 0) {
			$najem_id = $najem_full = $offers->filterNajemOffer($car_id, $najem_variant);
			// var_dump($najem_id);
			$najem_id = explode('----', $najem_id)[0];
			
			$basic_parameters = $offers->getBasicParameters($car_id, $najem_id);
			// var_dump($basic_parameters);
			$x = 0;
			foreach($basic_parameters[1] as $n) {
				$name = $n['najem_offer'];
				if (strpos($najem_full, $name) !== false && $x == 0) {
					$fee_najem_payments = $n['fee_najem'];
					$fee_najem_rates = $n['rates_najem'];
					$fee_najem_milleage = $n['milleage'];
					// var_dump($najem_full);
					//var_dump($n);
					// var_dump($name);
					// var_dump($n);
					// exit();
					$x++;
				}
			}
			$najem = explode(']', $najem[0]);
			$najem = str_replace('[', '', $najem[0]);
			$rates_najem = unserialize($options['najem_0_rates_najem'][0]);
			//var_dump($najem_settings);
			$installments_najem = array();
			foreach ($najem_settings->Installments as $i) {
				array_push($installments_najem, $i);
			}
			
			$default_installment_najem = $options['najem_0_default_installment_najem'][0];

		}

		$normal_price = get_field('regular-price');

		$tax = $normal_price / (1 + 23 / 100);
		$promotion_price_wt = 0;
		$discount_price_wt = get_field('discount-price');
		$normal_price_wt = $tax;
		if ($normal_price_wt !== $discount_price_wt) {
			$discount_price = ($discount_price_wt / (1 + 23 / 100));
			$promotion_price = 0;
		}

		$omnibus = true;

		if (get_post_timestamp(get_the_ID(), 'modified') > strtotime('-30 days')) {
			$omnibus = true;
		}
		$color = explode(' ', get_field('color'));
		$color_wn = '';

		foreach ($color as $key => $c) {
			if ($key > 0) {
				$color_wn .= $c . ' ';
			}
		}

		$eurocode = get_field('eurocode');
		$residalValue = null;
		$residalArray = array();

		if ($eurocode && $leasing_variant > 0) {

			$hasDiscountPrice = get_field('has-discount-price');
			if ($hasDiscountPrice) {
				$default_price = number_format(((int) get_field('discount-price') / (1 + 23 / 100)), 0, '.', '');

			} else {
				$default_price = number_format(((int) get_field('regular-price') / (1 + 23 / 100)), 0, '.', '');

			}

			$data = array(
				'DealerProductId' => $lease_id,
				'Eurocode' => $eurocode,
				'Price' => $default_price,
				'InstalmentNumber' => ($installments[$default_installment] ? $installments[$default_installment] : $installments[0]),
				'ManufacturingYear' => date('Y'),
			);

			$importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());
			// $token = $importTool->getToken();

			// $residalValue = $importTool->getResidalValue($data,$token);
			$residalValue = $importTool->getResidalValue($data, null);

			if (is_object($residalValue)) {
				array_push($residalArray, $residalValue->Min, (round($residalValue->Max / 2)), $residalValue->Max);
			}
		}
		if ($eurocode && $leasing_variant == null) {
			array_push($residalArray, 1, 5, 19);
		}
		// sprawdzenie oferty domyślnej

		// sprawdzenei oferty domyślnej

		
		$lease_active = (get_field('lease_car') !== "none" && get_field('lease_car') !== '1' && get_field('lease_car') ? get_field('lease_car') : null);
		return $this->view(
			'layouts/stock-car-single/stock-car-single',
			array(
				'siteHeading' => array(
					'heading' => 'Volvo ' . $model,
					'description' => get_field('pickup-time'),
				),
				'car' => array(
					'singleView' => true,
					'url_link' => get_permalink(),
					'model' => $model,
					'imagesFormat' => $imageFormat,

					'simulation' => $rate_najem,
					'lease' => ($this->disable_dol ? null : $lease_active),

					// parametry leasingu
					// atrakcyjność auta
					'leasing_id' => $lease_id,
					'income_leasing' => get_field('income'),
					'leasing_fees' => $leasing_options,
					'default_leasing_fee' => $default_leasing_fee,
					'dealer_com_leasing' => $dealer_com,
					'installments_leasing' => $installments,
					'default_installment' => $default_installment,
					'installments_najem' => $installments_najem,
					'rates_leasing' => $rates_leasing,
					'eurocode' => $eurocode,
					'residalValues' => $residalArray,
					// $fee_najem_payments = $n['fee_najem'];
					// $fee_najem_rates = $n['rates_najem'];
					// $fee_najem_milleage = $n['milleage'];
					// parametry leasingu
					// parametry najmu
					'income_najem' => get_field('income_najem'),
					'najem_id' => $najem_id,
					'rates_najem' => $rates_najem,
					'default_installment_najem' => $default_installment_najem,
					// parametry najmu  
					'pno12' => get_field('pno'),
					'vin' => get_field('vin'),
					'con' => get_field('con'),
					'price_with_tax' => $normal_price,
					'price_without_tax' => $normal_price_wt,
					'price_discount_with_tax' => $discount_price_wt,
					'price_discount_without_tax' => $discount_price,
					'omnibus_price' => get_field('omnibus_price'),
					'lease_type' => $lease,
					'najem' => ($this->disable_dol ? null : get_field('najem_car')),
					'najem_type' => $najem,
					'fee_najem_payments' => $fee_najem_payments,
					'fee_najem_rates' => $fee_najem_rates,
					'fee_najem_milleage' => $fee_najem_milleage,
					'offerNumber' => get_field('offer-number'),
					'salesPhone' => get_field('sales-phone'),
					'dealer' => get_field('name', 'options-dealer'),
					'regularPrice' => get_field('regular-price'),
					'regularPriceDefault' => get_field('regular-price'),
					'hasDiscountPrice' => (get_field('has-discount-price') && get_field('discount-price') !== '' ? get_field('has-discount-price') : false),
					'discountPrice' => get_field('discount-price'),
					'productionYear' => get_field('production-year'),
					'engine' => (get_field('engine') ? get_field('engine') : get_field('engine_1')),
					'version' => (get_field('version') ? get_field('version') : get_field('version_1')),
					'gearbox' => get_field('gearbox'),
					'color' => ($color_wn ? $color_wn : get_field('color_1')),
					'maxPowerText' => get_field('max-power-text'),
					'fuelType' => get_field('fuel-type'),
					'acceleration' => get_field('acceleration'),
					'fuelConsumptionUnit' => get_field('fuel-consumption-unit'),
					'fuelConsumption' => get_field('fuel-consumption'),
					'maxSpeed' => get_field('max-speed'),
					'car_state' => get_field('cartype'),
					'in_archive' => get_field('archive'),
					'pickup_time' => get_field('pickup-time'),
					'distance' => get_field('car-distance'),
					'cargoCapacity' => get_field('cargo-capacity'),
					'seats' => get_field('seats'),
					'height' => get_field('height'),
					'length' => get_field('length'),
					'width' => get_field('width'),
					'groundClearance' => get_field('ground-clearance'),
					'erange' => get_field('erange'),
					'omnibus' => $omnibus,
					'gallery' => $carGallery,
					'galleryThumbs' => $galleryThumbs,
					'versionDescription' => CarDictionary::getVersionDescription(get_field('version')),
					'button' => array(
						'text' => 'Jazda testowa',
						'permalink' => MultisiteFixer::getHomeUrl() . '/jazda-testowa?s_model=' . $model,
					),
				),
				'accordionHeading' => get_field('accordion-heading'),
				'accordion' => $accordion,
				'gallery' => $gall,
				'featuredCars' => array(
					'heading' => $featuredCarsOptions['single-car-heading'],
					'cars' => $featuredCars,
				),
			)
		);
	}
	public function clearUrl($url)
	{
		$domain = get_blogaddress_by_id(MultisiteFixer::getCurrentBlogId());

		$url = str_replace('https://main.volvocars-partner.pl/', $domain, $url);

		return $url;
	}
	public function exportSync($blog_id)
	{
		switch_to_blog($blog_id);

		$query = new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => '-1',
				'post_status' => 'publish',
				'cache_results' => false,

			)
		);

		$cars = [];


		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();

				$odreki_status = get_field('odreki_sync');
				if ($odreki_status == '2') {
					$carImages = get_field('images');
					$carGallery = array();
					foreach ($carImages as $itemId) {
						$image = new ImageBuilder($itemId);
						$image->addSize(array(808, 453), false);
						$image->addSize(array(392, 294));
						$image->addSize(array(450, null));
						$image->addSize(array(900, null));
						$image->addSize(array(1350, null));

						$image->addSize(array(721, null));
						$image->addSize(array(1442, null));
						$image->addSize(array(2163, null));

						$image->addSize(array(959, null));
						$image->addSize(array(1918, null));
						$image->addSize(array(2877, null));

						$image->addMediaQuery(null, '100vw', true);
						$image->addMediaQuery('(min-width: 992px)', '808px');

						$full = PictureBuilder::getImage($itemId, 'full');

						$thumbnail = new ImageBuilder($itemId);
						$thumbnail->addSize(array(258, 143));
						$thumbnail->addSize(array(516, 286));
						$thumbnail->addSize(array(774, 429));

						$carGallery[] = array(
							'image' => $image->get(),
							'full' => $full,
							'thumbnail' => $thumbnail->get(),
							'domain' => get_site_url(),
						);
					}

					$summerTyreLabels = get_field('summer-tyre-labels');

					foreach ($summerTyreLabels as $label) {
						$carGallery[] = array(
							'image' => array(
								'sizes' => array(
									array(
										'width' => 808,
										'height' => 453,
										'src' => MultisiteFixer::buildUrl($label['url'], null, true),
									),
								),
							),
							'full' => array(
								'width' => 808,
								'height' => 453,
								'src' => MultisiteFixer::buildUrl($label['url'], null, true),
							),
							'thumbnail' => array(
								'sizes' => array(
									array(
										'width' => 258,
										'height' => 143,
										'src' => MultisiteFixer::buildUrl($label['url'], null, true),
									),
								),
							),
						);
					}
					$galleryImages = get_field('gallery');
					$gallery = array();
					foreach ($galleryImages as $itemId) {
						$mobileImage = new ImageBuilder($itemId);
						$mobileImage->addSize(array(218, 164));
						$mobileImage->addSize(array(436, 328));
						$mobileImage->addSize(array(654, 492));

						$mobileImage->addSize(array(306, 164));
						$mobileImage->addSize(array(612, 328));
						$mobileImage->addSize(array(918, 492));

						$mobileImage->addMediaQuery(null, '218px', true);
						$mobileImage->addMediaQuery('(min-width: 720px)', '306px');

						$desktopImage = new ImageBuilder($itemId);
						$desktopImage->addSize(array(322, 248));
						$desktopImage->addSize(array(392, 294));
						$desktopImage->addSize(array(644, 496));
						$desktopImage->addSize(array(966, 744));

						$full = PictureBuilder::getImage($itemId, 'full');

						$gallery[] = array(
							'mobileImage' => $mobileImage->get(),
							'desktopImage' => $desktopImage->get(),
							'full' => $full,
							'domain' => get_site_url(),
						);
					}

					$showroomsIds = Showroom::getShowrooms();
					$showroomId = $showroomsIds[0];

					if (Showroom::isMultiShowroom()) {
						$showroomId = get_field('showroom');
					}

					$provider = get_field('showroomId', $showroomId);

					$showroomLocation = get_field('showroom-location', $showroomId);

					$dateCreated = get_post_meta(get_the_ID(), '_wp_old_date', true);

					$winterTyreLabels = get_field('winter-tyre-labels');

					$formattedWinterTyreLabels = array();

					foreach ($winterTyreLabels as $label) {
						$formattedWinterTyreLabels[] = array(
							'url' => MultisiteFixer::buildUrl($label['url'], null, true),
						);
					}
					$length = 24;
					$payment = 20;
					$distance = 10;
					$carprice = get_field('regular-price');

					if ($payment !== 0) {
						$price = ($carprice - (($payment / 100) * $carprice)) / 0.7;

						$rate_najem = number_format((((($price / $length)) * ($distance / 1000))) * 10, 2, '.', '');
					}
					$color = explode(' ', get_field('color'));

					$color_wn = '';

					foreach ($color as $key => $c) {
						if ($key > 0) {
							$color_wn .= $c . ' ';
						}
					}
					// update_field('odreki_sync', 1, get_the_ID());
					$cars[] = array(
						'id' => get_the_ID(),
						'model' => (get_field('model_1') ? get_field('model_1') : get_field('model')),
						'income' => get_field('income'),
						'income_najem' => get_field('income_najem'),
						'pno12' => get_field('pno'),
						'vin' => get_field('vin'),
						'con' => get_field('con'),
						'offerNumber' => get_field('offer-number'),
						'eurocode' => (get_field('eurocode') ? get_field('eurocode') : false),
						'regularPrice' => get_field('regular-price'),
						'regularPriceDefault' => get_field('regular-price'),
						'hasDiscountPrice' => get_field('has-discount-price'),
						'discountPrice' => get_field('discount-price'),
						'simulation' => $rate_najem,
						'lease_car' => get_field('lease_car'),
						'najem_car' => get_field('najem_car'),
						'pickupTime' => get_field('pickup-time'),
						'productionYear' => get_field('production-year'),
						'color' => (get_field('color_1') ? get_field('color_1') : $color_wn),
						'inlay' => (get_field('inlay_1') ? get_field('inlay_1') : get_field('inlay')),
						'engine' => (get_field('engine_1') ? get_field('engine_1') : get_field('engine')),
						'version' => (get_field('version_1') ? get_field('version_1') : get_field('version')),
						'gearbox' => get_field('gearbox'),
						'maxPowerText' => get_field('max-power-text'),
						'maxPower' => get_field('max-power'),
						'fuelType' => get_field('fuel-type'),
						'acceleration' => get_field('acceleration'),
						'mileage' => get_field('mileage'),
						'cartype' => get_field('cartype'),
						'fuelConsumptionUnit' => get_field('fuel-consumption-unit'),
						'fuelConsumption' => get_field('fuel-consumption'),
						'maxSpeed' => get_field('max-speed'),
						'cartype' => get_field('cartype'),
						'distance' => get_field('car-distance'),
						'cargoCapacity' => get_field('cargo-capacity'),
						'seats' => get_field('seats'),
						'height' => get_field('height'),
						'length' => get_field('length'),
						'width' => get_field('width'),
						'groundClearance' => get_field('ground-clearance'),
						'erange' => get_field('erange'),
						'omnibus_date' => get_post_timestamp(get_the_ID(), 'modified'),
						'gallery' => $carGallery,
						'accordionHeading' => get_field('accordion-heading'),
						'accordion' => get_field('accordion'),
						'dealerName' => get_field('name', 'options-dealer'),
						'provider' => $provider,
						'location' => $showroomLocation,
						'winterTyreLabels' => $formattedWinterTyreLabels,
						'omnibus_price' => get_field('omnibus_price'),
						'createdDate' => $dateCreated ?: get_the_date('Y-m-d'),
						'eurocode' => get_field('eurocode'),
						'dealerphone' => get_field('sales-phone'),
					);
				}
				update_field('odreki_sync', 1, get_the_ID());
			}



			return $cars;
			restore_current_blog();
		} else {
			restore_current_blog();
			return array();
		}

	}
	public function exportUpdated($id)
	{
		// if (env('WP_ENV') === 'production') {			
		// 	$url = 'https://main.volvocars-partner.pl/api/getDealers';
		// } else {
		// 	$url = 'https://karlik.volvotest.pl/api/getDealers';
		// }
		// $query = json_decode(file_get_contents($url));
		// unset($query[0]);
		$cars = [];
		$data = $this->exportSync($id);
		if (!empty($data)) {
			$cars = array_merge($cars, $data);

		}

		return $cars;
	}
	public function exportAllById($id, $offset)
	{
		switch_to_blog($id);
		$query = new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => '60',
				'offset' => $offset,
				'post_status' => 'publish',
				'cache_results' => false,
			)
		);
		$offers = new CarDictionary();

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$odreki_status = get_field('odreki_sync');
				if ((int) $odreki_status !== 2) {
					update_field('odreki_sync', 1, get_the_ID());
				}
				$carImages = get_field('images');
				$carGallery = array();
				foreach ($carImages as $itemId) {
					$image = new ImageBuilder($itemId);
					$image->addSize(array(1920, 1080), false);
					$image->addSize(array(392, 284));
					$image->addSize(array(450, null));
					$image->addSize(array(900, null));
					$image->addSize(array(1350, null));

					$image->addSize(array(721, null));
					$image->addSize(array(1442, null));
					$image->addSize(array(2163, null));

					$image->addSize(array(959, null));
					$image->addSize(array(1918, null));
					$image->addSize(array(2877, null));

					$image->addMediaQuery(null, '100vw', true);
					$image->addMediaQuery('(min-width: 992px)', '808px');

					$full = PictureBuilder::getImage($itemId, 'full');

					$thumbnail = new ImageBuilder($itemId);
					$thumbnail->addSize(array(258, 143));
					$thumbnail->addSize(array(516, 286));
					$thumbnail->addSize(array(774, 429));

					$carGallery[] = array(
						'image' => $image->get(),
						'full' => $full,
						'thumbnail' => $thumbnail->get(),
						'domain' => get_site_url(),
					);
				}

				$summerTyreLabels = get_field('summer-tyre-labels');

				foreach ($summerTyreLabels as $label) {
					$carGallery[] = array(
						'image' => array(
							'sizes' => array(
								array(
									'width' => 808,
									'height' => 453,
									'src' => MultisiteFixer::buildUrl($label['url'], null, true),
								),
							),
						),
						'full' => array(
							'width' => 808,
							'height' => 453,
							'src' => MultisiteFixer::buildUrl($label['url'], null, true),
						),
						'thumbnail' => array(
							'sizes' => array(
								array(
									'width' => 258,
									'height' => 143,
									'src' => MultisiteFixer::buildUrl($label['url'], null, true),
								),
							),
						),
					);
				}
				$galleryImages = get_field('gallery');
				$gallery = array();
				foreach ($galleryImages as $itemId) {
					$mobileImage = new ImageBuilder($itemId);
					$mobileImage->addSize(array(218, 164));

					$mobileImage->addSize(array(436, 328));
					$mobileImage->addSize(array(654, 492));

					$mobileImage->addSize(array(306, 164));
					$mobileImage->addSize(array(612, 328));
					$mobileImage->addSize(array(918, 492));

					$mobileImage->addMediaQuery(null, '218px', true);
					$mobileImage->addMediaQuery('(min-width: 720px)', '306px');

					$desktopImage = new ImageBuilder($itemId);
					$desktopImage->addSize(array(322, 248));
					$desktopImage->addSize(array(392, 294));
					$desktopImage->addSize(array(644, 496));
					$desktopImage->addSize(array(966, 744));

					$full = PictureBuilder::getImage($itemId, 'full');

					$gallery[] = array(
						'mobileImage' => $mobileImage->get(),
						'desktopImage' => $desktopImage->get(),
						'f$width = (int)getimagesize($itemId)[0];
						$height = (int)getimagesize($itemId)[1];
						if ($height < $width) {ull' => $full,
						'domain' => get_site_url(),
					);
				}

				$showroomsIds = Showroom::getShowrooms();
				$showroomId = $showroomsIds[0];

				if (Showroom::isMultiShowroom()) {
					$showroomId = get_field('showroom');
				}

				$provider = get_field('showroomId', $showroomId);

				$showroomLocation = get_field('showroom-location', $showroomId);

				$dateCreated = get_post_meta(get_the_ID(), '_wp_old_date', true);

				$winterTyreLabels = get_field('winter-tyre-labels');

				$formattedWinterTyreLabels = array();

				foreach ($winterTyreLabels as $label) {
					$formattedWinterTyreLabels[] = array(
						'url' => MultisiteFixer::buildUrl($label['url'], null, true),
					);
				}
				$length = 24;
				$payment = 20;
				$distance = 10;
				$carprice = get_field('regular-price');

				if ($payment !== 0) {
					$price = ($carprice - (($payment / 100) * $carprice)) / 0.7;

					$rate_najem = number_format((((($price / $length)) * ($distance / 1000))) * 10, 2, '.', '');
				}
				$color = explode(' ', get_field('color'));
				$color_wn = '';

				foreach ($color as $key => $c) {
					if ($key > 0) {
						$color_wn .= $c . ' ';
					}
				}
				$lease_id = null;
				$leasing_variant = (get_field('lease_car') !== "0" ? get_field('lease_car') : 0);
				if ($leasing_variant == "none") {
					$leasing_variant = 0;
				}
				if ($leasing_variant > 0) {

					$lease = $offers->getLeaseOffer($leasing_variant);
					$lease = explode(' ', $lease);
					$lease_id = $lease[0];

				}

				$najem_variant = (get_field('najem_car') !== "0" ? get_field('najem_car') : 0);
				$najem_id = null;
				if ($najem_variant == "none") {
					$najem_variant = 0;
				}
				if ($najem_variant > 0) {

					$najem = $offers->getNajemOffer($najem_variant);

					$najem = explode(' ', $najem);
					$najem_id = $najem[0];


				}
				$cars[] = array(
					'id' => get_the_ID(),
					'model' => (get_field('model_1') ? get_field('model_1') : get_field('model')),
					'income' => get_field('income'),
					'income_najem' => get_field('income_najem'),
					'pno12' => get_field('pno'),
					'offerNumber' => get_field('offer-number'),
					'eurocode' => (get_field('eurocode') ? get_field('eurocode') : false),
					'regularPrice' => get_field('regular-price'),
					'regularPriceDefault' => get_field('regular-price'),
					'hasDiscountPrice' => get_field('has-discount-price'),
					'discountPrice' => get_field('discount-price'),
					'simulation' => $rate_najem,
					'lease_car' => $lease_id,
					'najem_car' => $najem_id,
					'imageFormat' => get_field('picture_format'),
					'pickupTime' => get_field('pickup-time'),
					'productionYear' => get_field('production-year'),
					'color' => ($color_wn ? get_field('color_1') : get_field('color_1')),
					'inlay' => get_field('inlay'),
					'engine' => (get_field('engine') ? get_field('engine') : get_field('engine_1')),
					'version' => (get_field('version') ? get_field('version') : get_field('version_1')),
					'gearbox' => get_field('gearbox'),
					'maxPowerText' => get_field('max-power-text'),
					'maxPower' => get_field('max-power'),
					'fuelType' => get_field('fuel-type'),
					'acceleration' => get_field('acceleration'),
					'mileage' => get_field('mileage'),
					'cartype' => get_field('cartype'),
					'fuelConsumptionUnit' => get_field('fuel-consumption-unit'),
					'fuelConsumption' => get_field('fuel-consumption'),
					'maxSpeed' => get_field('max-speed'),
					'cartype' => get_field('cartype'),
					'distance' => get_field('car-distance'),
					'cargoCapacity' => get_field('cargo-capacity'),
					'seats' => get_field('seats'),
					'height' => get_field('height'),
					'length' => get_field('length'),
					'width' => get_field('width'),
					'groundClearance' => get_field('ground-clearance'),
					'erange' => get_field('erange'),
					'omnibus_date' => get_post_timestamp(get_the_ID(), 'modified'),
					'gallery' => $carGallery,
					'accordionHeading' => get_field('accordion-heading'),
					'accordion' => get_field('accordion'),
					'dealerName' => get_field('name', 'options-dealer'),
					'provider' => $provider,
					'location' => $showroomLocation,
					'winterTyreLabels' => $formattedWinterTyreLabels,
					'createdDate' => $dateCreated ?: get_the_date('Y-m-d'),
					'eurocode' => get_field('eurocode'),
					'dealerphone' => get_field('sales-phone'),
				);
				update_field('odreki_sync', 1, get_the_ID());
			}
		} else {
			return array();
		}
		restore_current_blog();

		return $cars;

	}
	public function exportAll()
	{
		$blog_id = MultisiteFixer::getCurrentBlogId();
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$query = new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => '-1',
				'post_status' => 'publish',
				'cache_results' => false,
			)
		);

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();

				$carImages = get_field('images');
				$carGallery = array();
				foreach ($carImages as $itemId) {
					$image = new ImageBuilder($itemId);
					$image->addSize(array(808, 453), false);
					$image->addSize(array(392, 294));
					$image->addSize(array(450, null));
					$image->addSize(array(900, null));
					$image->addSize(array(1350, null));

					$image->addSize(array(721, null));
					$image->addSize(array(1442, null));
					$image->addSize(array(2163, null));

					$image->addSize(array(959, null));
					$image->addSize(array(1918, null));
					$image->addSize(array(2877, null));

					$image->addMediaQuery(null, '100vw', true);
					$image->addMediaQuery('(min-width: 992px)', '808px');

					$full = PictureBuilder::getImage($itemId, 'full');

					$thumbnail = new ImageBuilder($itemId);
					$thumbnail->addSize(array(258, 143));
					$thumbnail->addSize(array(516, 286));
					$thumbnail->addSize(array(774, 429));

					$carGallery[] = array(
						'image' => $image->get(),
						'full' => $full,
						'thumbnail' => $thumbnail->get(),
						'domain' => get_site_url(),
					);
				}

				$summerTyreLabels = get_field('summer-tyre-labels');

				foreach ($summerTyreLabels as $label) {
					$carGallery[] = array(
						'image' => array(
							'sizes' => array(
								array(
									'width' => 808,
									'height' => 453,
									'src' => MultisiteFixer::buildUrl($label['url'], null, true),
								),
							),
						),
						'full' => array(
							'width' => 808,
							'height' => 453,
							'src' => MultisiteFixer::buildUrl($label['url'], null, true),
						),
						'thumbnail' => array(
							'sizes' => array(
								array(
									'width' => 258,
									'height' => 143,
									'src' => MultisiteFixer::buildUrl($label['url'], null, true),
								),
							),
						),
					);
				}
				$galleryImages = get_field('gallery');
				$gallery = array();
				foreach ($galleryImages as $itemId) {
					$mobileImage = new ImageBuilder($itemId);
					$mobileImage->addSize(array(218, 164));
					$mobileImage->addSize(array(436, 328));
					$mobileImage->addSize(array(654, 492));

					$mobileImage->addSize(array(306, 164));
					$mobileImage->addSize(array(612, 328));
					$mobileImage->addSize(array(918, 492));

					$mobileImage->addMediaQuery(null, '218px', true);
					$mobileImage->addMediaQuery('(min-width: 720px)', '306px');

					$desktopImage = new ImageBuilder($itemId);
					$desktopImage->addSize(array(322, 248));
					$desktopImage->addSize(array(392, 294));
					$desktopImage->addSize(array(644, 496));
					$desktopImage->addSize(array(966, 744));

					$full = PictureBuilder::getImage($itemId, 'full');

					$gallery[] = array(
						'mobileImage' => $mobileImage->get(),
						'desktopImage' => $desktopImage->get(),
						'full' => $full,
						'domain' => get_site_url(),
					);
				}

				$showroomsIds = Showroom::getShowrooms();
				$showroomId = $showroomsIds[0];

				if (Showroom::isMultiShowroom()) {
					$showroomId = get_field('showroom');
				}

				$provider = get_field('showroomId', $showroomId);

				$showroomLocation = get_field('showroom-location', $showroomId);

				$dateCreated = get_post_meta(get_the_ID(), '_wp_old_date', true);

				$winterTyreLabels = get_field('winter-tyre-labels');

				$formattedWinterTyreLabels = array();

				foreach ($winterTyreLabels as $label) {
					$formattedWinterTyreLabels[] = array(
						'url' => MultisiteFixer::buildUrl($label['url'], null, true),
					);
				}
				$length = 24;
				$payment = 20;
				$distance = 10;
				$carprice = get_field('regular-price');

				if ($payment !== 0) {
					$price = ($carprice - (($payment / 100) * $carprice)) / 0.7;

					$rate_najem = number_format((((($price / $length)) * ($distance / 1000))) * 10, 2, '.', '');
				}
				$color = explode(' ', get_field('color'));
				$color_wn = '';

				foreach ($color as $key => $c) {
					if ($key > 0) {
						$color_wn .= $c . ' ';
					}
				}

				$cars[] = array(
					'id' => get_the_ID(),
					'model' => (get_field('model_1') ? get_field('model_1') : get_field('model')),
					'income' => get_field('income'),
					'income_najem' => get_field('income_najem'),
					'pno12' => get_field('pno'),
					'offerNumber' => get_field('offer-number'),
					'eurocode' => (get_field('eurocode') ? get_field('eurocode') : false),
					'regularPrice' => get_field('regular-price'),
					'regularPriceDefault' => get_field('regular-price'),
					'hasDiscountPrice' => get_field('has-discount-price'),
					'discountPrice' => get_field('discount-price'),
					'simulation' => $rate_najem,
					'imageFormat' => get_field('picture_format'),
					'lease_car' => get_field('lease_car'),
					'najem_car' => get_field('najem_car'),
					'pickupTime' => get_field('pickup-time'),
					'productionYear' => get_field('production-year'),
					'color' => $color_wn,
					'inlay' => get_field('inlay'),
					'engine' => (get_field('engine') ? get_field('engine') : get_field('engine_1')),
					'version' => (get_field('version') ? get_field('version') : get_field('version_1')),
					'gearbox' => get_field('gearbox'),
					'maxPowerText' => get_field('max-power-text'),
					'maxPower' => get_field('max-power'),
					'fuelType' => get_field('fuel-type'),
					'acceleration' => get_field('acceleration'),
					'mileage' => get_field('mileage'),
					'cartype' => get_field('cartype'),
					'fuelConsumptionUnit' => get_field('fuel-consumption-unit'),
					'fuelConsumption' => get_field('fuel-consumption'),
					'maxSpeed' => get_field('max-speed'),
					// 'cartype'             => get_field( 'cartype' ),
					'distance' => get_field('car-distance'),
					'cargoCapacity' => get_field('cargo-capacity'),
					'seats' => get_field('seats'),
					'height' => get_field('height'),
					'length' => get_field('length'),
					'width' => get_field('width'),
					'groundClearance' => get_field('ground-clearance'),
					'erange' => get_field('erange'),
					'omnibus_date' => get_post_timestamp(get_the_ID(), 'modified'),
					'gallery' => $carGallery,
					'accordionHeading' => get_field('accordion-heading'),
					'accordion' => get_field('accordion'),
					'dealerName' => get_field('name', 'options-dealer'),
					'provider' => $provider,
					'location' => $showroomLocation,
					'winterTyreLabels' => $formattedWinterTyreLabels,
					'createdDate' => $dateCreated ?: get_the_date('Y-m-d'),
					'eurocode' => get_field('eurocode'),
					'dealerphone' => get_field('sales-phone'),
				);
			}
		} else {
			return array();
		}
		restore_current_blog();

		return $cars;
	}
	
	// public function getAll(): array
	// {

	// 	switch_to_blog(MultisiteFixer::getCurrentBlogId());
	// 	$query = new \WP_Query(
	// 		array(
	// 			'post_type' => 'stock-car',
	// 			'posts_per_page' => '-1',
	// 			'post_status' => 'publish',
	// 			'cache_results' => false,
	// 		)
	// 	);

	// 	if ($query->have_posts()) {
	// 		while ($query->have_posts()) {
	// 			$query->the_post();

	// 			$carImages = get_field('images');
	// 			$carGallery = array();
	// 			foreach ($carImages as $itemId) {

	// 				$image = new ImageBuilder($itemId);
	// 				$image->addSize(array(808, 453), false, true);
	// 				image->addSize(array(392, 294), true, true);
	// 				$image->addSize(array(450, null), true, true);
	// 				$image->addSize(array(900, null), true, true);
	// 				$image->addSize(array(1350, null), true, true);

	// 				$image->addSize(array(721, null), true, true);
	// 				$image->addSize(array(1442, null), true, true);
	// 				$image->addSize(array(2163, null), true, true);

	// 				$image->addSize(array(959, null), true, true);
	// 				$image->addSize(array(1918, null), true, true);
	// 				$image->addSize(array(2877, null), true, true);

	// 				$image->addMediaQuery(null, '100vw', true);
	// 				$image->addMediaQuery('(min-width: 992px)', '808px');

	// 				$full = PictureBuilder::getImage($itemId, 'full', true, true);

	// 				$thumbnail = new ImageBuilder($itemId);
	// 				$thumbnail->addSize(array(258, 143), true, true);
	// 				$thumbnail->addSize(array(516, 286), true, true);
	// 				$thumbnail->addSize(array(774, 429), true, true);

	// 				$carGallery[] = array(
	// 					'image' => $image->get(),
	// 					'full' => $full,
	// 					'thumbnail' => $thumbnail->get(),
	// 				);
	// 			}

	// 			$summerTyreLabels = get_field('summer-tyre-labels');

	// 			foreach ($summerTyreLabels as $label) {
	// 				$carGallery[] = array(
	// 					'image' => array(
	// 						'sizes' => array(
	// 							array(
	// 								'width' => 808,
	// 								'height' => 453,
	// 								'src' => MultisiteFixer::buildUrl($label['url'], null, true),
	// 							),
	// 						),
	// 					),
	// 					'full' => array(
	// 						'width' => 808,
	// 						'height' => 453,
	// 						'src' => MultisiteFixer::buildUrl($label['url'], null, true),
	// 					),
	// 					'thumbnail' => array(
	// 						'sizes' => array(
	// 							array(
	// 								'width' => 258,
	// 								'height' => 143,
	// 								'src' => MultisiteFixer::buildUrl($label['url'], null, true),
	// 							),
	// 						),
	// 					),
	// 				);
	// 			}

	// 			$showroomsIds = Showroom::getShowrooms();
	// 			$showroomId = $showroomsIds[0];

	// 			if (Showroom::isMultiShowroom()) {
	// 				$showroomId = get_field('showroom');
	// 			}

	// 			$provider = get_field('showroomId', $showroomId);

	// 			$showroomLocation = get_field('showroom-location', $showroomId);

	// 			$dateCreated = get_post_meta(get_the_ID(), '_wp_old_date', true);

	// 			$winterTyreLabels = get_field('winter-tyre-labels');

	// 			$formattedWinterTyreLabels = array();

	// 			foreach ($winterTyreLabels as $label) {
	// 				$formattedWinterTyreLabels[] = array(
	// 					'url' => MultisiteFixer::buildUrl($label['url'], null, true),
	// 				);
	// 			}
	// 			$length = 24;
	// 			$payment = 20;
	// 			$distance = 10;
	// 			$carprice = get_field('regular-price');

	// 			if ($payment !== 0) {
	// 				$price = ($carprice - (($payment / 100) * $carprice)) / 0.7;

	// 				$rate_najem = number_format((((($price / $length)) * ($distance / 1000))) * 10, 2, '.', '');
	// 			}
	// 			$color = explode(' ', get_field('color'));
	// 			$color_wn = '';

	// 			foreach ($color as $key => $c) {
	// 				if ($key > 0) {
	// 					$color_wn .= $c . ' ';
	// 				}
	// 			}

	// 			$cars[] = array(
	// 				'id' => get_the_ID(),
	// 				'model' => (get_field('model') ? get_field('model') : get_field('model_1')),
	// 				'income' => get_field('income'),
	// 				'income_najem' => get_field('income_najem'),
	// 				'pno12' => get_field('pno'),
	// 				'offerNumber' => get_field('offer-number'),
	// 				'eurocode' => (get_field('eurocode') ? get_field('eurocode') : false),
	// 				'regularPrice' => get_field('regular-price'),
	// 				'regularPriceDefault' => get_field('regular-price'),
	// 				'hasDiscountPrice' => get_field('has-discount-price'),
	// 				'discountPrice' => get_field('discount-price'),
	// 				'simulation' => $rate_najem,
	// 				'lease_car' => get_field('lease_car'),
	// 				'najem_car' => get_field('najem_car'),
	// 				'pickupTime' => get_field('pickup-time'),
	// 				'productionYear' => get_field('production-year'),
	// 				'color' => $color_wn,
	// 				'inlay' => get_field('inlay'),
	// 				'engine' => get_field('engine'),
	// 				'version' => get_field('version'),
	// 				'gearbox' => get_field('gearbox'),
	// 				'maxPowerText' => get_field('max-power-text'),
	// 				'maxPower' => get_field('max-power'),
	// 				'fuelType' => get_field('fuel-type'),
	// 				'acceleration' => get_field('acceleration'),
	// 				'mileage' => get_field('mileage'),
	// 				'cartype' => get_field('cartype'),
	// 				'fuelConsumptionUnit' => get_field('fuel-consumption-unit'),
	// 				'fuelConsumption' => get_field('fuel-consumption'),
	// 				'maxSpeed' => get_field('max-speed'),
	// 				'cartype' => get_field('cartype'),
	// 				'distance' => get_field('car-distance'),
	// 				'cargoCapacity' => get_field('cargo-capacity'),
	// 				'seats' => get_field('seats'),
	// 				'height' => get_field('height'),
	// 				'length' => get_field('length'),
	// 				'width' => get_field('width'),
	// 				'groundClearance' => get_field('ground-clearance'),
	// 				'erange' => get_field('erange'),
	// 				'omnibus_date' => get_post_timestamp(get_the_ID(), 'modified'),
	// 				'gallery' => $carGallery,
	// 				'accordionHeading' => get_field('accordion-heading'),
	// 				'accordion' => get_field('accordion'),
	// 				'dealerName' => get_field('name', 'options-dealer'),
	// 				'provider' => $provider,
	// 				'location' => $showroomLocation,
	// 				'winterTyreLabels' => $formattedWinterTyreLabels,
	// 				'createdDate' => $dateCreated ?: get_the_date('Y-m-d'),
	// 				'eurocode' => get_field('eurocode'),
	// 			);
	// 		}
	// 	} else {
	// 		return array();
	// 	}

	// 	restore_current_blog();

	// 	return $cars;
	// }
	public function getAll($data,$type,$blog_id): array {
		$simple_title = false;
		if ($blog_id === null) {
			$simple_title = true;
		}
		if ($type == 'all') {
			$blog_ids = [];
			$blogs = wp_get_sites();
			foreach ($blogs as $b) {
				array_push($blog_ids, $b['blog_id']);
			}
		} else {
			$blog_ids = [MultisiteFixer::getCurrentBlogId()];
		}
		if ($blog_id) {
			$blog_ids = [$blog_id];
		}
		
		$data = [];
		
		foreach($blog_ids as $b) {

		
		
		switch_to_blog( $b );
		$options = get_fields('options-dealer');
            if (is_array($options)) {
                $dealerId = $options['dealerId'];   
				$dealerName = $options['name'];				
            }
		$query = new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => '-1',
				'post_status'    => 'publish',				
				'cache_results'  => false,
				'meta_query'    => array(
					'relation'      => 'AND',
					array(
						'key'       => 'cartype',
						'value'     => 'used',
						'compare'   => '=',
					)
				)
			)
		);
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$carImages  = get_field( 'images' );
				$carGallery = array();
				
				foreach ( $carImages as $key=>$value ) {
					$carGallery[] = [
						'url' => wp_get_attachment_image_url($value,'full'),
						'position' => $key
					];
				}

				$summerTyreLabels = get_field( 'summer-tyre-labels' );

				

				$showroomsIds = Showroom::getShowrooms($b);
				
				$showroomId   = $showroomsIds[0];

				if ( Showroom::isMultiShowroom() ) {
					$showroomId = get_field( 'showroom' );
				}

				
				$showroomLocation = get_the_title($showroomId);
			
				$dateCreated = get_post_meta( get_the_ID(), '_wp_old_date', true );

				$winterTyreLabels = get_field( 'winter-tyre-labels' );

				$formattedWinterTyreLabels = array();

				foreach ( $winterTyreLabels as $label ) {
					$formattedWinterTyreLabels[] = array(
						'url' => MultisiteFixer::buildUrl( $label['url'], null, true ),
					);
				}
				$length   = 24;
				$payment  = 20;
				$distance = 10;
				$carprice = get_field( 'regular-price' );

				if ( $payment !== 0 ) {
					$price = ( $carprice - ( ( $payment / 100 ) * $carprice ) ) / 0.7;

					$rate_najem = number_format( ( ( ( ( $price / $length ) ) * ( $distance / 1000 ) ) ) * 10, 2, '.', '' );
				}
				$color    = explode( ' ', get_field( 'color' ) );
				$color_wn = '';

				foreach ( $color as $key => $c ) {
					if ( $key > 0 ) {
						$color_wn .= $c . ' ';
					}
				}
				$accordion = get_field('accordion');
				$provider = get_field( 'showroomId', $showroomId );

				if ($simple_title) {
					$name = strtoupper(get_field( 'name', 'options-dealer' ));
					$showroomLocation = null;
				} else {
					$name = $showroomLocation;
				}
				$data[] = [
					'id' => get_the_ID().'_'.$b,
					'offerNumber' => get_field( 'offer-number' ),
					'carData' => [
						'model'	=> (get_field( 'model' ) ? get_field('model') : get_field('model_1')),
						'year'	=> get_field( 'production-year' ),
						'vin'	=> get_field( 'vin' ),
						'version'	=> get_field( 'version' ),
						'powerHp'	=> get_field( 'max-power' ),
						'fuelType'	=> get_field( 'fuel-type' ),
						'mileage'	=> get_field( 'car-distance' ),
						'gearboxVersion'	=> get_field( 'gearbox' ),
						'color'	=> $color_wn,
						'engine'	=> get_field( 'engine' ),
						'upholstery'	=> (get_field('inlay') ? get_field('inlay') : get_field('inlay_1')),
						'deliveryDate'	=> null,
						'acceleration0100' => get_field('acceleration'),
						'fuelConsumptionWltpTotal' => get_field('fuel-consumption'),
						'cargoCapacityLiters' => get_field('cargo-capacity')
					],
					'price' => [
						'grossPrice' => get_field( 'regular-price' ),
						'promoPrice' => get_field( 'discount-price' ),
						'profit' => null,
						'omnibus' => get_field('omnibus_price'),
						'installment' => null
					],
					'dealer' => [
						'name' => $name,
						'phone' => get_field( 'sales-phone' ),
						'location' => (get_field('location') ? get_field('location') : ($showroomLocation ?? null)),
						'dealerId' => $dealerId,
						'provider' => (str_replace('#','',$provider) ?? null), 
						'showRoom' => ($showroomLocation ?? null),
					],
					'images' => $carGallery,
					'tags' => null,
					'recommended' => null,
					'accordion' => $accordion
				];
				
			}
		} 
		
		restore_current_blog();
		}
		
		return $data;
	}
	private function getTyreLabelsAccordionElement($postId): array
	{
		$hashIds = new Hashids('encrypt car id');
		$carId = $hashIds->encode($postId, 58, 55);

		return array(
			'heading' => 'Etykiety energetyczne',
			'description' => '<a href="/etykiety-zimowe?car=' . $carId . '" target="_blank">Sprawdź etykiety energetyczne opon zimowych oferowane przez Volvo</a>',
		);
	}

	private function getFeaturedCars($id): array
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$featuredCars = new FeaturedCars($id);
		$featuredCarsBySimilarPrice = $featuredCars->get();
		restore_current_blog();

		return $featuredCarsBySimilarPrice;
	}

	private function mapToAccordion($array): ?array
	{
		if ($array) {
			foreach ($array as &$section) {
				$section['heading'] = $section['name'];
				unset($section['name']);

				$section['items'] = (is_array($section['items']) ? array_map('current', $section['items']) : []);
			}
			unset($section);

			return $array;
		}

		return null;
	}
}
