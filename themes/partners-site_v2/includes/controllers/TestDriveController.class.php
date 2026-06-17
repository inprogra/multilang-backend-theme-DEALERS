<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\Lead;
use Classes\MultisiteFixer;
use Classes\Showroom;
use Classes\PictureBuilder;

class TestDriveController extends Controller {

	public function render($type = 'main',$car = null): string {
		
		switch_to_blog( 1 );
		global $wp;
		
		$checkCar = explode('jazda-testowa-model-',$wp->request);
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
			foreach ( $posts as $version ) {
				$gallery = get_field( 'gallery', $version->ID );
				
				$featuredImageId = $gallery[0];
				$featuredImage   = new ImageBuilder( $featuredImageId );
				$featuredImage->addSize( array( 293, null ) );
				$featuredImage->addSize( array( 586, null ) );
				$featuredImage->addSize( array( 879, null ) );
	
				$featuredImage->addSize( array( 380, null ) );
				$featuredImage->addSize( array( 760, null ) );
				$featuredImage->addSize( array( 1140, null ) );
	
				$featuredImage->addSize( array( 514, null ) );
				$featuredImage->addSize( array( 1028, null ) );
				$featuredImage->addSize( array( 1542, null ) );
	
				$featuredImage->addSize( array( 670, null ) );
				$featuredImage->addSize( array( 1340, null ) );
				$featuredImage->addSize( array( 2010, null ) );
	
				$featuredImage->addMediaQuery( null, '100vw', true );
				$featuredImage->addMediaQuery( '(min-width: 992px)', '293px' );
				
				
				foreach ( $gallery as $itemId ) {
					$image = new ImageBuilder( $itemId );
					$image->addSize( array( 600, 336 ) );
					$image->addSize( array( 1200, 672 ) );
					$image->addSize( array( 1800, 1008 ) );					
	
					$image->addSize( array( 450, null ) );
					$image->addSize( array( 900, null ) );
					$image->addSize( array( 1350, null ) );
	
					$image->addSize( array( 721, null ) );
					$image->addSize( array( 1442, null ) );
					$image->addSize( array( 2163, null ) );
	
					$image->addSize( array( 959, null ) );
					$image->addSize( array( 1918, null ) );
					$image->addSize( array( 2877, null ) );
	
					$image->addMediaQuery( null, '100vw', true );
					$image->addMediaQuery( '(min-width: 992px)', '944px' );
				
	
					$full = PictureBuilder::getImage( $itemId, 'full' );
	
					$thumbnail = new ImageBuilder( $itemId );
	
					$thumbnail->addSize( array( 189, 105 ) );
					$thumbnail->addSize( array( 378, 210 ) );
					$thumbnail->addSize( array( 567, 315 ) );
	
					$thumbnail->addMediaQuery( null, '190px', true );
	
					$galleryPictures[] = array(
						'image'     => $image->get(),
						'full'      => $full,
						'thumbnail' => $thumbnail->get(),
						'domain'    => get_site_url(),
					);
					
				}
			}
			
			$content = '';
			if (!empty($test_car)) {
				$title = $test_car[0]->post_title;
				$content = do_blocks( $test_car[0]->post_content );
			}
			
			$modelsGroupsField = get_field( 'models_groups', 'options-test-drive' );
			$modelsGroups      = array();
			
			foreach ( $modelsGroupsField as $modelsGroup ) {
				$group = array();
				foreach ( $modelsGroup['models'] as $model ) {
					$image = new ImageBuilder( $model['image'] );
					
					$image->addSize( array( 288, null ) );
					$image->addSize( array( 288, null ) );
					$image->addSize( array( 288, null ) );

					$group[] = array(
						'slug'  => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $model['model']))),
						'name'  => $model['model'],
						'image' => $image->get(),
					);
				}
				$modelsGroups[] = $group;
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
				'groups' => $modelsGroups,
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

		
		
			$modelsGroupsField = get_field( 'models_groups', 'options-test-drive' );
			$modelsGroups      = array();
			
			foreach ( $modelsGroupsField as $modelsGroup ) {
				$group = array();
				foreach ( $modelsGroup['models'] as $model ) {
					$image = new ImageBuilder( $model['image'] );
					
					$image->addSize( array( 288, null ) );
					$image->addSize( array( 288, null ) );
					$image->addSize( array( 288, null ) );

					$group[] = array(
						'slug'  => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $model['model']))),
						'name'  => $model['model'],
						'image' => $image->get(),
					);
				}
				$modelsGroups[] = $group;
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

			return $this->view('layouts/test-drive/test-drive', [
				'groups' => $modelsGroups,
				'source' => $source,
				'thanksCode' => ($thanksCode ? $thanksCode : ''),
				'destination' => 'new-cars',
				'showrooms' => $showrooms,
				'partnerName' => $partnerName,
				'thankyouImage' => Cache::getAsset('formThanksImage.png'),
				'todayDate' => $today->format('Y-m-d'),
			]);
		}
	}
}
