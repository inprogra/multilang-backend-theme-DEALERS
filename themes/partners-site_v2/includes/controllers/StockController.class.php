<?php

namespace Controllers;

use Classes\Controller;
use Classes\FeaturedCars;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;
use Classes\Showroom;
use Classes\StockCarQueryBuilder;
use Classes\CarDictionary;
use \Classes\Cache;

class StockController extends Controller
{

	private $disable_dol;
	private $cache;
	public function __construct()
	{
		
		$this->cache = new \Classes\Cache();
	}
	public function render(): string
	{
		
		//echo '.';
		$this->cache = $cache = new \Classes\Cache();
		$this->disable_dol = $GLOBALS['disable_dol'];
		$filters = array();
		$phoneJs = $this->getPhoneJs();
		foreach ($_GET as $key => $value) {
			$filters[substr($key, 2)] = $value;
		}

		$query = $this->getCarsQuery($filters);
		$cars = $this->getCarsBy($query);
		$price_query = $this->getAllCars($filters);
		$price_cars = $this->getCarsBy($price_query);
		$priceMin = null;
		$priceMax = null;
		$client = new \GuzzleHttp\Client();



		foreach ($price_cars as $car) {
			if (!$priceMin) {
				$priceMin = (int) $car['regularPrice'];
			}
			if ((int) $car['regularPrice'] < $priceMin || (int) $car['discountPrice'] < $priceMin) {
				$priceMin = ((int) $car['discountPrice'] ? (int) $car['discountPrice'] : (int) $car['regularPrice']);
			}

			if ((int) $car['discountPrice'] && (int) $car['discountPrice'] < $priceMin) {
				$priceMin = (int) $car['discountPrice'];
			}

			if (!$priceMax) {
				$priceMax = (int) $car['regularPrice'];
			} elseif ((int) $car['regularPrice'] > $priceMax && (int) $car['discountPrice'] > $priceMax) {
				$priceMax = ((int) $car['discountPrice'] ? (int) $car['discountPrice'] : (int) $car['regularPrice']);
			}
		}
		//$min = $priceMin;
		$min = 0;
		$max = $priceMax;
		if (isset($filters['discount-price-min']) && (intval($filters['discount-price-min']) < $min || intval($filters['discount-price-min']) > $max)) {
			$filters['discount-price-min'] = $min;
		}
		if (isset($filters['discount-price-max']) && (intval($filters['discount-price-max']) < $min || intval($filters['discount-price-max']) > $max)) {
			$filters['discount-price-max'] = $max;
		}

		$featuredCars = $this->getFeaturedCars();

		switch_to_blog(1);
		$featuredCarsOptions = get_field('featured-cars', 'options-global');
		restore_current_blog();

		$carsCount = $query->found_posts;

		if ($carsCount > 0) {
			$featuredCarsHeading = $featuredCarsOptions['all-cars-heading'];
		} else {
			$featuredCarsHeading = $featuredCarsOptions['not-found-heading'];
		}

		$years = array();
		$car_models = array();
		$car_colors = array();
		$car_engine = array();
		$car_version = array();
		$car_inlay = array();
		$car_gearbox = array();
		$models = CarDictionary::getModels();
		$colors = CarDictionary::getColors();
		$engine = CarDictionary::getEngines();
		$version = CarDictionary::getVersions();
		$inlay = CarDictionary::getInlays();
		$gearbox = CarDictionary::getGearboxes();

		// //var_dump(count($price_cars));
		foreach ($price_cars as $car) {

			if (!array_key_exists($car['productionYear'], $years)) {
				$car_year = $car['productionYear'];
				$years[$car_year] = $car_year;
			}
			// //var_dump($car['model']);

			if (array_key_exists($car['model'], $models) && !in_array($car['model'], $car_models)) {
				$car_models[$car['model']] = $car['model'];
			}

			if (!array_key_exists($car['color'], $car_colors)) {
				$car_colors[substr($car['color'], 0, -1)] = substr($car['color'], 0, -1);
			}
			if (!array_key_exists($car['engine'], $car_engine)) {
				$car_engine[$car['engine']] = $car['engine'];
			}
			if (!array_key_exists($car['version'], $car_version)) {
				$car_version[$car['version']] = $car['version'];
			}
			if (!array_key_exists($car['inlay'], $car_inlay)) {
				$car_inlay[$car['inlay']] = $car['inlay'];
			}
			if (!array_key_exists($car['gearbox'], $car_gearbox)) {
				$car_gearbox[$car['gearbox']] = $car['gearbox'];
			}
		}

		foreach ($colors as $k => $c) {
			$color = explode(' ', $c);
			$el = array_shift($color);
			$color = implode(' ', $color);
			$colors[$k] = $color;
		}
		// //var_dump($colors);
		// //var_dump($car_colors);
		foreach ($colors as $k => $v) {
			if (!in_array($v, $car_colors)) {
				unset($colors[$k]);
			}
			// foreach($car_colors as $key=>$c) {
			// //var_dump($c);
			// //var_dump($v);
			// if ($v == $c) {

			// }
			// }

		}
		$car_colors = $colors;
		// //var_dump($car_colors);
		// $car_colors = $colors;

		krsort($car_models);
		ksort($car_engine);
		ksort($car_colors);

		ksort($car_version);
		ksort($car_inlay);
		krsort($years);
		$filters['showroom'] = $_GET['showroom'] ?? null;

		return $this->view(
			'layouts/stock/stock',
			array(
				'showroomFilters' => array(
					'filters' => $this->getShowroomFilters(),
					'selected' => $filters['showroom'] ?? null, 
				

				),
				'mainFilters' => array(
					'model' => array(
						'label' => __('Model', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect($car_models),
						'selected' => $filters['model'] ?? array(),
					),
					'color' => array(
						'label' => __('Color', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect($car_colors),
						'selected' => $filters['color'] ?? array(),
					),
					'engine' => array(
						'label' => __('Engine', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect($car_engine),
						'selected' => $filters['engine'] ?? array(),
					),
					'version' => array(
						'label' => __('Version', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect($car_version),
						'selected' => $filters['version'] ?? array(),
					),
				),
				'secondaryFilters' => array(
					'inlay' => array(
						'label' => __('Upholstery', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect($car_inlay),
						'selected' => $filters['inlay'] ?? array(),
					),
					'gearbox' => array(
						'label' => __('Transmission', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect($car_gearbox),
						'selected' => $filters['gearbox'] ?? array(),
					),
					'max-power' => array(
						'label' => __('Maximum power', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect(
							array(
								'0-170' => __('Up to 170 HP', 'partners-site_v2'),
								'170-200' => __('170–200 HP', 'partners-site_v2'),
								'201-250' => __('201–250 HP', 'partners-site_v2'),
								'251-300' => __('251–300 HP', 'partners-site_v2'),
								'301-10000' => __('Over 300 HP', 'partners-site_v2'),
							)
						),
						'selected' => $filters['max-power'] ?? array(),
					),
					'production-year' => array(
						'label' => __('Year of manufacture', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect(
							$years
						),
						'selected' => $filters['production-year'] ?? array(),
					),
					'radio-new-used' => array(
						'label' => 'car_type',
						'values' => $this->mapDictionaryToMultiselect(
							array(
								'all' => __('All', 'partners-site_v2'),
								'new' => __('New', 'partners-site_v2'),
								'used' => __('Used', 'partners-site_v2'),
							)
						),
						'selected' => $filters['cartype'] ?? array(),
					),
					'distance' => array(
						'label' => __('Mileage', 'partners-site_v2'),
						'values' => $this->mapDictionaryToMultiselect(
							array(
								'0-20000' => __('0-20,000 km', 'partners-site_v2'),
								'20001-100000' => __('20,000-100,000 km', 'partners-site_v2'),
								'100001-200000' => __('100,000–200,000 km', 'partners-site_v2'),
							)
						),
						'selected' => $filters['mileage'] ?? array(),
					),
				),
				'priceRangeFilter' => array(
					'name' => 'discount-price',
					'label' => __('Price', 'partners-site_v2'),
					'min' => $min,
					'max' => $max,
					'selected' => array(
						'min' => $filters['discount-price-min'] ?? $min,
						'max' => $filters['discount-price-max'] ?? $max,
					),
				),
				'phoneJs' => $phoneJs,
				'cars' => $cars,
				'carsCount' => $carsCount,
				'carsCountText' => polishSuffixes('auto', 'auta', 'aut', $carsCount),
				'pagination' => array(
					'currentPage' => 1,
					'maxPages' => $query->max_num_pages,
				),
				'featuredCars' => array(
					'heading' => $featuredCarsHeading,
					'cars' => $featuredCars,
				),
			)
		);
	}
	private function getPhoneJs()
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$phoneJs = get_field('globalJs', 'options-dealer');
		restore_current_blog();
		return $phoneJs;
	}
	public function filter($data): string
	{

		$query = $this->getCarsQuery($data);

		$cars = $this->getCarsBy($query);


		$featuredCars = $this->getFeaturedCars();

		switch_to_blog(1);
		$featuredCarsOptions = get_field('featured-cars', 'options-global');
		restore_current_blog();
		$phoneJs = $this->getPhoneJs();
		if (count($cars) > 0) {
			$featuredCarsHeading = $featuredCarsOptions['all-cars-heading'];
		} else {
			$featuredCarsHeading = $featuredCarsOptions['not-found-heading'];
		}

		return $this->view(
			'layouts/stock/stock-cars',
			array(
				'phoneJs' => $phoneJs,
				'cars' => $cars,
				'carsCount' => $query->found_posts,
				'carsCountText' => polishSuffixes('auto', 'auta', 'aut', $query->found_posts),
				'pagination' => array(
					'currentPage' => $data['page'],
					'maxPages' => $query->max_num_pages,
				),

			)
		);


	}

	private function getFeaturedCars()
	{
		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$featuredCars = new FeaturedCars();
		$featuredCarsByLowestPrice = $featuredCars->get();
		restore_current_blog();

		return $featuredCarsByLowestPrice;
	}

	private function getShowroomFilters()
	{
		if (!Showroom::isMultiShowroom()) {
			return false;
		}

		$filters = array();

		$showrooms = Showroom::getShowrooms();

		switch_to_blog(MultisiteFixer::getCurrentBlogId());

		foreach ($showrooms as $showroom) {
			$filters[$showroom] = get_field('name', $showroom);
		}

		restore_current_blog();

		return $filters;
	}

	private function mapDictionaryToMultiselect($values): array
	{
		$mappedValues = array();

		foreach ($values as $key => $value) {
			$mappedValues[] = array(
				'name' => $key,
				'value' => $key,
				'label' => $value,
			);
		}

		return $mappedValues;
	}
	private function getAllCars($filters): \WP_Query
	{
		$carQueryBuilder = new StockCarQueryBuilder();
		$page = intval($filters['page']) ?? 1;
		$validatedFilters = $carQueryBuilder->validateFilters($filters);

		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$query = new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'cache_results' => true,
				'meta_query' => $carQueryBuilder->build($validatedFilters),
			)
		);
		//switch_to_blog(1);
		return $query;
	}
	private function getCarsQuery($filters): \WP_Query
	{
		$upload_folder = explode('/', wp_get_upload_dir()['basedir']);
		array_pop($upload_folder);
		$upl = implode('/', $upload_folder) . '/cars/';
		$carQueryBuilder = new StockCarQueryBuilder();
		$page = intval($filters['page']) ?? 1;
		$validatedFilters = $carQueryBuilder->validateFilters($filters);

		switch_to_blog(MultisiteFixer::getCurrentBlogId());
		$blog_id = get_current_blog_id();
		$query = new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => '12',
				'post_status' => 'publish',
				'cache_results' => false,
				'paged' => $page,
				'meta_query' => $carQueryBuilder->build($validatedFilters),
			)
		);
		//switch_to_blog(1);
		return $query;
	}
	public function clearUrl($url)
	{
		$domain = get_blogaddress_by_id(MultisiteFixer::getCurrentBlogId());

		$url = str_replace('https://main.volvocars-partner.pl/', $domain, $url);

		return $url;
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
	private function getCarsBy($query): array
	{
		$pimg = new ImageBuilder(-1, false);
		$blog_id = get_current_blog_id();
		$client = new \GuzzleHttp\Client();
		$upload_folder = explode('/', wp_get_upload_dir()['basedir']);
		array_pop($upload_folder);
		$upl = implode('/', $upload_folder) . '/cars/';

		$cars = array();
		$offers = new CarDictionary();

		
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$imageFormat = false;
				if (get_field('regular-price')) {
					$gallery = (is_array(get_field('images')) ? array_slice(get_field('images'), 0, 7) : []);
					$imageFormat = (!empty(get_field('picture_format')) ? true : false);
					$galleryPictures = [];
					$galleryThumbs = [];
					$x = 0;
					foreach ($gallery as $itemId) {
						
						if ($x < 7) {
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
							
							
							
							// $divisor = gmp_intval( gmp_gcd( $width, $height ) );
							// $aspectRatio = $width / $divisor . ':' . $height / $divisor;
							
							if ($imageFormat) {
								$images = [
									[
										'blog_id' => $blog_id,
										'img_id' => $img_id,
										'format_height' => '1024',
										'format_width'  => '768',
										'height' => 1080,
										'width' => 1920,
										'crop' =>  false,
										'image' => $itemId,
										'query' => 1680,										
										'theight' => 220,
										'twidth' => null,
										'tcrop' =>  false,	
										'format' => $imageFormat,																	
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
										'theight' => 220,
										'twidth' => null,
										'tcrop' =>  false,	
										'format' => $imageFormat,	
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
										'theight' => 220,
										'twidth' => null,
										'tcrop' =>  false,	
										'format' => $imageFormat,	
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
										'theight' => 220,
										'twidth' => 200,
										'tcrop' =>  false,	
										'format' => $imageFormat,	
									],
									[
										'blog_id' => $blog_id,
										'img_id' => $img_id,
										'height' => 1024,
										'width' => 768,
										'crop' => 'false',
										'image' => $itemId,
										'query' => 1000,										
										'theight' => 220,
										'twidth' => 200,
										'tcrop' =>  false,	
										'format' => $imageFormat,	
									],
									[
										'blog_id' => $blog_id,
										'img_id' => $img_id,
										'height' => false,
										'width' => 500,
										'crop' => 'crop',
										'image' => $itemId,
										'query' => 100,										
										'theight' => 220,
										'twidth' => 200,
										'tcrop' =>  false,	
										'format' => $imageFormat,	
									]
								];
								$images = $pimg->prepareImages($images);	
								$galleryPictures[] = $images;
								$images = $pimg->prepareImages($imagesThumbs);	
								$galleryThumbs[] = $images;
								// $images = $pimg->prepareImages($images);	
								
							} else {

							
							if ($imageFormat || (int)$ratio > 16) {
								
								$images = [
									[
										'blog_id' => $blog_id,
										'img_id' => $img_id,
										'format_height' => '768',
										'format_width'  => '1024',
										'height' => 1080,
										'width' => 1920,
										'crop' =>  'crop',
										'image' => $itemId,
										'query' => 1680,										
										'theight' => 220,
										'twidth' => 200,
										'tcrop' =>  false,		
										'format' => $imageFormat,																
									],
									[
										'blog_id' => $blog_id,
										'img_id' => $img_id,
										'format_height' => '768',
										'format_width'  => '1024',
										'height' => 700,
										'width' => 1440,
										'crop' => 'false',
										'image' => $itemId,
										'query' => 1000,										
										'theight' => 300,
										'twidth' => 200,
										'tcrop' =>  false,		
										'format' => $imageFormat,
									],
									[
										'blog_id' => $blog_id,
										'img_id' => $img_id,
										'format_height' => '768',
										'format_width'  => '1024',
										'height' => false,
										'width' => 1000,
										'crop' => 'crop',
										'image' => $itemId,
										'query' => 100,										
										'theight' => 300,
										'twidth' => 200,
										'tcrop' =>  false,		
										'format' => $imageFormat,
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
										'format' => $imageFormat,
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
										'format' => $imageFormat,
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
										'format' => $imageFormat,
									]
								];
								$images = $pimg->prepareImages($images);	
								$galleryPictures[] = $images;
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
										'height' => 1100,
										'width' => 1920,
										'crop' =>  'crop',
										'image' => $itemId,
										'query' => 1680,										
										'theight' => 220,
										'twidth' => 200,
										'tcrop' =>  false,		
										'format' => $imageFormat,
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
										'theight' => 220,
										'twidth' => 200,
										'tcrop' =>  false,		
										'format' => $imageFormat,
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
										'theight' => 220,
										'twidth' => 200,
										'tcrop' =>  false,		
										'format' => $imageFormat,
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
										'format' => $imageFormat,
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
										'format' => $imageFormat,
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
										'format' => $imageFormat,	
									]
								];
								$images = $pimg->prepareImages($images);	
								$galleryPictures[] = $images;
								$images = $pimg->prepareImages($imagesThumbs);	
								$galleryThumbs[] = $images;
								$images = $pimg->prepareImages($images);	
												

							}
						}
					}
						$x++;
					}

					$length = 24;
					$payment = 20;
					$distance = 10;
					$carprice = get_field('regular-price');

					if ($payment !== 0) {
						$price = ($carprice - (($payment / 100) * $carprice)) / 0.7;

						$rate_najem = number_format((((($price / $length)) * ($distance / 1000))) * 10, 0, '', ' ');
					}
					$omnibus = false;

					$calc = null;
					$example_rate = null;
					$config_default = null;
					$eurocode = get_field('eurocode');
					if ($eurocode) {

						$leasing_variant = (get_field('lease_car') !== "0" ? get_field('lease_car') : 0);
						if ($leasing_variant == "none") {
							$leasing_variant = 0;
						}
						if ($leasing_variant > 0) {

							$lease = $offers->getLeaseOffer($leasing_variant);
							$lease = explode(' ', $lease);
							$lease_id = $lease[1];

							$lease = explode(']', $lease[0]);
							$lease = str_replace('[', '', $lease[0]);

							$hasDiscountPrice = get_field('has-discount-price');
							if ($hasDiscountPrice) {
								$default_price = number_format(((int) get_field('discount-price') / (1 + 23 / 100)), 0, '.', '');
							} else {
								$default_price = number_format(((int) get_field('regular-price') / (1 + 23 / 100)), 0, '.', '');
							}
							$default = 'leasing_' . $lease_id . '_' . $default_price . '_default.json';

							$config_default = $upl . '' . $default . '.json';

							if (file_exists($config_default)) {
								$calc = json_decode(file_get_contents($config_default));
							}
						}

						if ($calc) {
							$vat = 23;
							$example_rate = $calc->Output->TotalInstalment->ValueInPln;
							$vatToPay = ($example_rate / 100) * $vat;
							$example_rate = $example_rate + $vatToPay;
						}
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

					$lease_active = (get_field('lease_car') !== "none" && get_field('lease_car') !== '1' && get_field('lease_car') ? get_field('lease_car') : null);

					$cars[] = array(
						'id' => get_the_ID(),
						'color' => ($color_wn ? $color_wn : get_field('color_1')),
						'lease' => ($this->disable_dol ? null : $lease_active),
						'najem' => ($this->disable_dol ? null : get_field('najem_car')),
						'pno12' => get_field('pno'),
						'imagesFormat' => $imageFormat,
						'simulation' => number_format($example_rate, 0, '.', ''),
						'model' => (get_field('model_1') ? get_field('model_1') : get_field('model')),
						'offerNumber' => get_field('offer-number'),
						'dealer' => get_field('name', 'options-dealer'),
						'salesPhone' => get_field('sales-phone'),
						'regularPrice' => get_field('regular-price'),
						'regularPriceDefault' => get_field('regular-price'),
						'hasDiscountPrice' => (get_field('has-discount-price') && get_field('discount-price') !== '' ? get_field('has-discount-price') : false),
						'eurocode' => $eurocode,
						'discountPrice' => get_field('discount-price'),
						'productionYear' => get_field('production-year'),
						'engine' => (get_field('engine_1') ? get_field('engine_1') : get_field('engine')),
						'version' => (get_field('version_1') ? get_field('version_1') : get_field('version')),
						'gearbox' => get_field('gearbox'),
						'inlay' => (get_field('inlay_1') ? get_field('inlay_1') : get_field('inlay')),
						'maxPowerText' => get_field('max-power-text'),
						'fuelType' => get_field('fuel-type'),
						'acceleration' => get_field('acceleration'),
						'fuelConsumptionUnit' => get_field('fuel-consumption-unit'),
						'fuelConsumption' => get_field('fuel-consumption'),
						'maxSpeed' => get_field('max-speed'),
						'erange' => get_field('erange'),
						'omnibus_date' => get_post_timestamp(get_the_ID(), 'modified'),
						'omnibus_price' => get_field('omnibus_price'),
						'car_state' => get_field('cartype'),
						'in_archive' => get_field('archive'),
						'pickup_time' => get_field('pickup-time'),
						'cartype' => get_field('cartype'),
						'mileage' => get_field('mileage'),
						'distance' => get_field('car-distance'),
						'cargoCapacity' => get_field('cargo-capacity'),
						'seats' => get_field('seats'),
						'height' => get_field('height'),
						'length' => get_field('length'),
						'width' => get_field('width'),
						'groundClearance' => get_field('ground-clearance'),
						'erange' => get_field('erange'),
						'omnibus' => $omnibus,
						'gallery' => $galleryPictures,
						'galleryThumbs' => $galleryThumbs,
						'versionDescription' => CarDictionary::getVersionDescription(get_field('version')),
						'button' => array(
							'text' => 'Dowiedz się więcej',
							'permalink' => get_the_permalink(),
						),
					);
				}
			}
		}
		switch_to_blog(1);

		return $cars;
	}
}
