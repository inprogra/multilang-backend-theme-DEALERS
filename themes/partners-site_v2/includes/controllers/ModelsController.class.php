<?php

namespace Controllers;

use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;
use Classes\Cache;
class ModelsController extends Controller {

	public function render(): string {
		$cache = new \Classes\Cache();
		$blog_id = get_current_blog_id();
		$data = $cache->get('modele-'.$blog_id);
		
		//$data = false;
		if (!$data) {
			
		
		$items = array();

		switch_to_blog( 1 );

		$categories = get_terms(
			array(
				'taxonomy' => 'model_category',
			)
		);

		$typesField = get_field_object( 'field_604a1c9f94d09' );
		$types      = $typesField['choices'];

		$categoriesActiveMobile = get_field( 'show-mobile', 'options-models' );
		$categoryActiveDesktop  = get_field( 'show-desktop', 'options-models' );
		$pimg = new ImageBuilder(-1, false);
		foreach ( $categories as $category ) {
			$item = array(
				'heading'         => $category->name,
				'subheading'      => get_field( 'description', 'model_category_' . $category->term_id ),
				'isMobileActive'  => in_array( $category->term_id, $categoriesActiveMobile, true ),
				'isDesktopActive' => $category->term_id === $categoryActiveDesktop,
				'types'           => array(),
			);

			$models = new \WP_Query(
				array(
					'post_type'      => 'model',
					'post_parent'    => 0,
					'cache'			 => true,
					'posts_per_page' => -1,
					'tax_query'      => array(
						array(
							'taxonomy' => 'model_category',
							'terms'    => $category->term_id,
						),
					),
				)
			);

			foreach ( $types as $key => $type ) {
				$cars = array();
				
				foreach ( $models->posts as $model ) {
					$imagesDesktop = [];
					$imagesMobile = [];
					$modelId   = $model->ID;
					$modelType = get_field( 'type', $modelId );

					if ( $modelType === $key ) {
						$itemId     = get_field( 'thumbnail', $modelId );
						$img_id = $itemId;
						$blog_id = get_current_blog_id();
						$itemId = wp_get_attachment_url($itemId);
						$images = [
							[
								'blog_id' => $blog_id,
								'img_id' => $img_id,
								'height' => 180,
								'width' => 320,
								'crop' =>  false,
								'image' => $itemId,
								'query' => 1680
							],
							[
								'blog_id' => $blog_id,
								'img_id' => $img_id,
								'height' => 'false',
								'width' => 300,
								'crop' => 'false',
								'image' => $itemId,
								'query' => 1000
							],
							[
								'blog_id' => $blog_id,
								'img_id' => $img_id,
								'height' => 174,
								'width' => 406,
								'crop' => 'crop',
								'image' => $itemId,
								'query' => 100
							]
						];
						$imagesDesktop = $pimg->prepareImages($images);
						$imagesMobile = [];
					

						$cars[] = array(
							'name'         => get_field( 'name', $modelId ),
							'short_name'   => get_field( 'short_name_list' , $modelId),
							'hide_price'   => $this->getPriceStatus($modelId),
							'price'        => $this->getPrice( $modelId ,true),
							'imageMobile'  => $imagesMobile,
							'imageDesktop' => $imagesDesktop,
							'url'          => MultisiteFixer::buildUrl( get_the_permalink( $modelId ) ),
						);
					}
				}
				
				if ( array_filter( $cars ) ) {
					$item['carTypes'][] = array(
						'name' => $type,
						'cars' => $cars,
					);
				}
				// var_dump($item);
			}
			
			$items[] = $item;
		}
		
		$popup         = get_field( 'popup', 'options-models' );
		$popup['link'] = MultisiteFixer::buildLink( $popup['link'] );
		$data = [
			'carCategories' => $items,
			'hasPopup'      => array_filter( $popup ),
			'popup'         => $popup,
		];
			restore_current_blog();
			
			$cache->set('modele-'.$blog_id,$data,3600);
		}
		//$data = json_decode($data);
		
		return $this->view(
			'layouts/models/models',
			$data
		);
	}
	private function getPriceStatus($modelId) {
		$hide_price = false;

		$variations = new \WP_Query(
			array(
				'post_type'      => 'model',
				'posts_per_page' => 99,
				'post_parent'    => $modelId,
				'cache_results'  => true,
				'meta_key'       => 'price',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			)
		);

		if ( array_filter( $variations->posts ) ) {
			if ( array_filter( $variations->posts ) ) {
			foreach($variations->posts as $p) {
				$variation = $p;
				$variation = $variations->posts[0];

				$price_status = get_field( 'hide_price', $variation->ID );

				if ( $price_status ) {
					$hide_price = $price_status;
					break;
				}
			}
			

			

			
		}
			
		}

		return $hide_price;
	}

	private function getPrice( $modelId ,$custom_price = false) {
		$price = false;

		$variations = new \WP_Query(
			array(
				'post_type'      => 'model',
				'posts_per_page' => 1,
				'post_parent'    => $modelId,
				'cache_results'  => true,
				'meta_key'       => 'price',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			)
		);
		if ($custom_price) {
			$price = get_field('menu_price', $modelId);
			return $price;
		}
		
		if ( array_filter( $variations->posts ) ) {
			$variation = $variations->posts[0];

			$variationPrice = get_field( 'price', $variation->ID );

			if ( $variationPrice ) {
				$price = $variationPrice;
			}
		}

		return $price;
	}
}
