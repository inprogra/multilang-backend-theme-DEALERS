<?php

namespace Controllers;

use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;
use Classes\Lead;
use Classes\Showroom;
use Classes\Cache;

class TestDriveControllerNew extends Controller {
	private $cache;
	public function __construct() {
		$this->cache = new \Classes\Cache();
	}
    public function render(): string { 
        $items = [];
		
        //switch_to_blog( MultisiteFixer::getCurrentBlogId() );
        switch_to_blog(1);
        global $wp;
        $checkCar = explode('jazda-testowa-model-',$wp->request);
        $pimg = new ImageBuilder(-1, false);
		if (array_key_exists(1, $checkCar)) {
			
			$args = array(
				'name'           => $wp->request,
				'post_type'      => 'page',
				'post_status'	 => 'any',				
				'posts_per_page' => 1
			  );
			$test_car = get_posts($args);
			$car_title = strtoupper(str_replace(['model-','-electric'],['',''],$checkCar[1]));
			
			$versions = new \WP_Query(
				array(
					'post_type'      => 'model',
					'post_status'	 => 'any',
					'posts_per_page' => -1,
					'title' => $car_title,
					
				)
			);
			
			$children = get_children( array('post_parent' => $versions->posts[0]->ID) );
			$posts = array_merge($versions->posts, $children);
			
			$Parsedown = new \Parsedown();
			$galleryPictures = [];
			$x=0;
			foreach ( $posts as $version ) {
				if ($x < 1) {
				
				$gallery = get_field( 'gallery', $version->ID );	
					
				if ($gallery) {
					$gallery = array_splice($gallery,0,5);
				}
				
				foreach ( $gallery as $itemId ) {
					$img_id = $itemId;
					$blog_id = get_current_blog_id();
					$itemId = wp_get_attachment_url($itemId);
					$images = [
						[
							'blog_id' => $blog_id,
							'img_id' => $img_id,
							'height' => 600,
							'width' => 1024,
							'crop' =>  false,
							'image' => $itemId,
							'query' => 1680
						],
						[
							'blog_id' => $blog_id,
							'img_id' => $img_id,
							'height' => 'false',
							'width' => 700,
							'crop' => 'false',
							'image' => $itemId,
							'query' => 1200
						],
						[
							'blog_id' => $blog_id,
							'img_id' => $img_id,
							'height' => 'false',
							'width' => 500,
							'crop' => 'false',
							'image' => $itemId,
							'query' => 100
						]
					];
					
					$images = $pimg->prepareImages($images);
					$galleryPictures[] = $images;
					$s = 300;
					//$thumbnail = $this->cache->get($itemId.'-'.$s);
					$s = 1024;
					//$full = $this->cache->get($itemId.'-'.$s);
						
						
				
					
					
				}
				if (!empty($gallery)) {
					$x++;
				}
				
			}
				
			}
			
			$content = '';
			if (!empty($test_car)) {
				$title = $test_car[0]->post_title;
				$content = do_blocks( $test_car[0]->post_content );
			}
			
	
			restore_current_blog();

			$source = Lead::getPostSource();

			switch_to_blog( MultisiteFixer::getCurrentBlogId() );
			$partnerName = get_field( 'name', 'options-dealer' );
			$thanksCode = get_field('code_test_drive', 'options-dealer');
			$thanksCode = str_replace( '||time||', time(), $thanksCode );
		
			$showrooms = false;
			if (Showroom::isMultiShowroomAndService()) {
				$showrooms = [];
				$showroomsIds = Showroom::getShowroomsAndServices();
				foreach ($showroomsIds as $id) {
					$showrooms[$id] = get_field('name', $id);
				}
			}
			restore_current_blog();

			$today = new \DateTime();
			
			
			
			return $this->view('layouts/test-drive/test-drive-car', [
				'content_title' => $title,
				'content' => $content,
			//	'groups' => $modelsGroups,
				'source' => $source,
				'gallery' => $galleryPictures,
				'thanksCode' => ($thanksCode ? $thanksCode : ''),
				'destination' => 'new-cars',
				'showrooms' => $showrooms,
				'partnerName' => $partnerName,
				'thankyouImage' => Cache::getAsset('formThanksImage.png'),
				'todayDate' => $today->format('Y-m-d'),
			]);
		} else {
			
        $categories = get_terms([
            'taxonomy' => 'model_category',
            'hide_empty' => false,
        ]);

        $typesField = get_field_object('field_604a1c9f94d09');
        $types = $typesField['choices'] ?? [];

        foreach ($categories as $category) {
            $item = [
                'heading' => $category->name,
                'carTypes' => [],
            ]; 
            $modelsQuery = new \WP_Query([
                'post_type' => 'model',
                'post_parent' => 0,
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => 'model_category',
                        'terms' => $category->term_id,
                    ],
                ],
            ]);

            $carsByType = [];
			
            foreach ($modelsQuery->posts as $model) {
				$galleryPictures = [];
                $modelId = $model->ID;

                $testDriveEnabled = get_field('testDrive', $modelId);
                if (!$testDriveEnabled) {
                    continue;
                }

                $modelTypeKey = get_field('type', $modelId);
                if (!$modelTypeKey || !isset($types[$modelTypeKey])) {
                    continue;
                }

                $imageId = get_field('thumbnail', $modelId);
				$img_id = $imageId;
				$blog_id = get_current_blog_id();
				$itemId = wp_get_attachment_url($imageId);
				$s = 300;
					//$thumbnail = $this->cache->get($itemId.'-'.$s);
				$s = 1024;
					//$full = $this->cache->get($itemId.'-'.$s);
					$images = [
						[
							'blog_id' => $blog_id,
							'img_id' => $img_id,
							'height' => false,
							'width' => 500,
							'crop' =>  false,
							'image' => $itemId,
							'query' => 1680
						],
						[
							'blog_id' => $blog_id,
							'img_id' => $img_id,
							'height' => 'false',
							'width' => 700,
							'crop' => 'false',
							'image' => $itemId,
							'query' => 1200
						],
						[
							'blog_id' => $blog_id,
							'img_id' => $img_id,
							'height' => 'false',
							'width' => 500,
							'crop' => 'false',
							'image' => $itemId,
							'query' => 100
						]
					];
					
					$images = $pimg->prepareImages($images);
						
				
				
				
                

                $url = MultisiteFixer::buildUrl(get_the_permalink($modelId));
                $slug = basename(parse_url($url, PHP_URL_PATH));

                $carData = [
                    'name' => get_field('name', $modelId),
                    'short_name' => get_field('short_name_list', $modelId),
                    'imageDesktop' => $images,
                    'url' => $url,
                    'slug' => str_replace('-electric','',$slug),
                ];

                $carsByType[$modelTypeKey][] = $carData;
            }

            foreach ($types as $typeKey => $typeName) {
                if (!empty($carsByType[$typeKey])) {
                    $item['carTypes'][] = [
                        'name' => $typeName,
                        'cars' => $carsByType[$typeKey],
                    ];
                }
            }

            $items[] = $item;
        }

        restore_current_blog();
		
        return $this->view(
            'layouts/test-drive/test-drive',
            [
                'testDrive' => $items,
            ]
        );
    }
    }
}
