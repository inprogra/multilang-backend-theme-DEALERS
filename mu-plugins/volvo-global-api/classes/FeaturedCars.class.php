<?php

namespace VGA\Classes;

class FeaturedCars
{

	private const CARS_TO_DISPLAY = 5;
	private $similarCarId = false;

	public function __construct($similarCarId = false)
	{
		if ($similarCarId) {
			$this->similarCarId = $similarCarId;
		}
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

	public function get(): array
	{
		$carsIds = $this->getIds();
		$cars = [];

		if (count($carsIds) < 2) {
			return array();
		}

		foreach ($carsIds as $id) {
			$cars[] = $this->getCarInfo($id);
		}

		return $cars;
	}

	public function clearUrl($url)
	{
		$domain = get_blogaddress_by_id(get_current_blog_id());

		$url = str_replace('https://main.volvocars-partner.pl/', $domain, $url);

		return $url;
	}

	private function getCarInfo($id): array
	{
		$images = get_field('images', $id);

		$blog_id = get_current_blog_id();
		
        if (!empty($images)) {
			$itemId = $images[0];

		    $imageFormat = false;

			$sizes = [288, 576, 864];
			$imagesArr = [];
			
			$img_id = $itemId;
			$images = volvo_global_prepare_image($img_id);
		}
	
		$imagesArr = $images;

		return array(
			'id' => get_the_ID(),
			'image' => $imagesArr ?? array(),
			'category' => get_field('category', $id),
			'model' => get_field('model', $id),
			'engine' => get_field('engine', $id),
			'price' => (get_field('has-discount-price', $id) && get_field('discount-price', $id) !== '' ? get_field('discount-price', $id) : get_field('regular-price', $id)),
			'url' => get_the_permalink($id),
		);
	}

	private function getIds(): array
	{
		$dealerChoices = $this->getDealerChoices();

		if (count($dealerChoices) === self::CARS_TO_DISPLAY) {
			return $dealerChoices;
		}

		$neededPosts = self::CARS_TO_DISPLAY - count($dealerChoices);

		$additionalPosts = array();

		if ($this->similarCarId) {
			$additionalPosts = $this->getSimilarPriceCars($neededPosts, $dealerChoices);
		} else {
			$additionalPosts = $this->getLowestPriceCars($neededPosts, $dealerChoices);
		}

		return array_merge($dealerChoices, $additionalPosts);
	}

	private function getDealerChoices(): array
	{
		$excludedIds = array();

		if ($this->similarCarId) {
			$excludedIds[] = $this->similarCarId;
		}

		$query = $this->buildDealerChoicesQuery(self::CARS_TO_DISPLAY, $excludedIds);

		if ($query->post_count <= 0) {
			return array();
		}

		$postsIDs = array();

		foreach ($query->posts as $post) {
			$postsIDs[] = $post->ID;
		}

		return $postsIDs;
	}

	private function getLowestPriceCars($neededPosts, $excludedPostsIds): array
	{
		$query = $this->buildLowestPriceQuery($neededPosts, $excludedPostsIds);

		if ($query->post_count <= 0) {
			return array();
		}

		$postsIDs = array();

		foreach ($query->posts as $post) {
			$postsIDs[] = $post->ID;
		}

		return $postsIDs;
	}

	private function getSimilarPriceCars($neededPosts, $excludedPostsIds): array
	{
		$price = get_field('discount-price', $this->similarCarId);

		$excludedPostsIds[] = $this->similarCarId;

		$query = $this->buildSimilarPriceQuery($excludedPostsIds);
		if ($query->post_count <= 0) {
			return array();
		}

		$posts = array();

		foreach ($query->posts as $post) {
			$posts[] = array(
				'id' => $post->ID,
				'price' => get_field('discount-price', $post->ID),
			);
		}

		if (is_int($price)) {
			usort($posts, $this->sortByClosestPrice('price', $price));
		}

		$posts = array_slice($posts, 0, $neededPosts);

		$postsIDs = array();

		foreach ($posts as $post) {
			$postsIDs[] = $post['id'];
		}

		return $postsIDs;
	}

	private function sortByClosestPrice($key, $price): \Closure
	{
		return static function ($a, $b) use ($key, $price) {
			return abs($price - $a[$key]) - abs($price - $b[$key]);
		};
	}

	private function buildDealerChoicesQuery($postCount, $excludedPostsIds = array()): \WP_Query
	{
		return new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => $postCount,
				'post_status' => 'publish',
				'post__not_in' => $excludedPostsIds,
				'orderby' => 'rand',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'is-featured',
						'value' => true,
					),
				),
			)
		);
	}

	private function buildLowestPriceQuery($postCount, $excludedPostsIds): \WP_Query
	{
		return new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => $postCount,
				'post_status' => 'publish',
				'post__not_in' => $excludedPostsIds,
				'meta_key' => 'discount-price',
				'orderby' => 'meta_value_num',
				'order' => 'ASC',
			)
		);
	}

	private function buildSimilarPriceQuery($excludedPostsIds): \WP_Query
	{
		return new \WP_Query(
			array(
				'post_type' => 'stock-car',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'post__not_in' => $excludedPostsIds,
			)
		);
	}
}
