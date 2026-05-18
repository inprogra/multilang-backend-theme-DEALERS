<?php

namespace Controllers;

use Classes\CarDictionary;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;

class HomepageController extends Controller {

	public function render(): string {
		switch_to_blog( 1 );
		$mobileImage = get_field( 'mobile_homepage_image', 'options-homepage' );
		$bottomImageId = get_field( 'bottom-image', 'options-homepage' );
		$bottomImage   = null;

		if ( $bottomImageId ) {
			$bottomImage = new ImageBuilder( $bottomImageId );
			// $bottomImage->addSize( array( 3840, 1614 ) );

			$bottomImage->addSize( array( 1920, 540 ) );

			$bottomImage->addSize( array( 1366, 574 ) );

			$bottomImage->addSize( array( 450, null ) );
			$bottomImage->addSize( array( 900, null ) );
			$bottomImage->addSize( array( 1350, null ) );

			$bottomImage->addSize( array( 721, null ) );
			$bottomImage->addSize( array( 1442, null ) );
			$bottomImage->addSize( array( 2163, null ) );

			$bottomImage->addSize( array( 959, null ) );
			$bottomImage->addSize( array( 1918, null ) );
			$bottomImage->addSize( array( 2877, null ) );

			$bottomImage->addMediaQuery( null, '100vw', true );

			$bottomImage = $bottomImage->get();

		}
		if ( $mobileImage ) {
			$mobileImage = new ImageBuilder( $mobileImage );
		
			$mobileImage->addSize( array( 1280, null ) );
			$mobileImage->addSize( array( 640, null ) );
			$mobileImage->addSize( array( 320, null ) );
			
			


			$mobileImage->addMediaQuery( null, '100vw', true );

			$mobileImage = $mobileImage->get();
			
		}
		if ( $mobileImage ) {
			$mobileImage = new ImageBuilder( $mobileImage );
		
			$mobileImage->addSize( array( 1280, null ) );
			$mobileImage->addSize( array( 640, null ) );
			$mobileImage->addSize( array( 640, null ) );
			
			


			$mobileImage->addMediaQuery( null, '100vw', true );

			$mobileImage = $mobileImage->get();
			
		}

		restore_current_blog();
		$globalHtml = get_field( 'field_global_html');
		
		return $this->view(
			'layouts/homepage/homepage',
			array(
				'heroSlider'      => $this->getHeroSlider(),
				'offers'          => $this->getOffers(),
				'stockCarsSlider' => $this->getStockCarsSlider(),
				'bottomImage'     => $bottomImage,
				'globalHtml'	  => $globalHtml,
				'mobileImage' => $mobileImage
			)
		);


	}

	private function getHeroSlider(): array {
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$heroSlider = array(
			'slides' => array(),
		);

		$sliderOptions = get_field( 'slider', 'options-homepage' );

		$slides = array();

		if ( ! empty( $sliderOptions ) ) {
			foreach ( $sliderOptions['slides'] as $item ) {
				$slidePost = false;
				if ( $item['type'] === 'local' && $item['local-campaign'] ) {
					$slidePost = get_post( $item['local-campaign'] );
				} elseif ( $item['type'] === 'global' && $item['global-campaign'] ) {
					switch_to_blog( 1 );
					$slidePost          = get_post( $item['global-campaign'] );
					$slidePost->site_ID = 1;
					restore_current_blog();
				}
				if ( $slidePost && $slidePost->post_status === 'publish' ) {
					$slides[] = $slidePost;
				}
			}
		}

		if ( empty( $slides ) || count( $slides ) < 3 ) {
			$slidesIds = array();

			if ( ! empty( $slides ) ) {
				foreach ( $slides as $slide ) {
					$slidesIds[] = $slide->ID;
				}
			}

			$slidesCount = count( $slides ) ?? 0;

			$latestCampaigns = new \WP_Query(
				array(
					'network'        => true,
					'sites__in'      => array( 1 ),
					'post_type'      => 'campaign',
					'post_status'    => 'publish',
					'posts_per_page' => 3 - $slidesCount,
					'post__not_in'   => $slidesIds,
				)
			);

			$slides = array_merge( $slides, $latestCampaigns->posts );
		}

		foreach ( $slides as $slide ) {
			if ( $slide->site_ID !== get_current_blog_id() ) {
				switch_to_blog( $slide->site_ID );
			}

			$title   = get_field( 'title', $slide->ID );
			$imageID = get_field( 'image', $slide->ID );

			$image = new ImageBuilder( $imageID );
			$image->addSize( array( 3840, 1614 ) );

			$image->addSize( array( 1600, 672 ) );

			$image->addSize( array( 1366, 574 ) );

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

			$thumbnail = new ImageBuilder( $imageID );
			$thumbnail->addSize( array( 104, 56 ) );
			$thumbnail->addSize( array( 208, 112 ) );
			$thumbnail->addSize( array( 312, 168 ) );
			$thumbnail->addMediaQuery( null, '104px', true );

			$linkField = get_field( 'link', $slide->ID );

			if ( ! $linkField || ! array_filter( $linkField ) ) {
				$linkField = array(
					'url' => get_the_permalink( $slide->ID ),
				);
			}

			if ( ! $linkField['title'] ) {
				$linkField['title'] = 'Dowiedz się więcej';
			}

			$heroSlider['slides'][] = array(
				'title'     => $title,
				'subtitle'  => get_field( 'subtitle', $slide->ID ),
				'link'      => MultisiteFixer::buildLink( $linkField ),
				'image'     => $image->get(),
				'thumbnail' => $thumbnail->get(),
			);

			if ( ms_is_switched() ) {
				restore_current_blog();
			}
		}
		return $heroSlider;
	}

	private function getOffers(): array {
		switch_to_blog( 1 );

		$offersOptions           = get_field( 'offers', 'options-homepage' );
		$options                 = getBasicOptions( 1 );
		$previewComponentOptions = $offersOptions['preview-component'];

		$image = new ImageBuilder( $previewComponentOptions['image'] );
		$image->addSize( array( 450, null ) );
		$image->addSize( array( 900, null ) );
		$image->addSize( array( 1350, null ) );

		$image->addSize( array( 721, null ) );
		$image->addSize( array( 1442, null ) );
		$image->addSize( array( 2163, null ) );

		$image->addSize( array( 944, null ) );
		$image->addSize( array( 1888, null ) );
		$image->addSize( array( 2832, null ) );
		$image->addMediaQuery( null, '100vw', true );
		$image->addMediaQuery( '(min-width: 992px)', '944px' );

		$offerBoxesOptions = $offersOptions['offer-boxes'];
		$offerBoxes        = array(
			'items' => array(),
		);
		foreach ( $offerBoxesOptions['items'] as $box ) {
			$hasButton = ! empty( $box['link'] );

			$offerBoxes['items'][] = array(
				'icon'        => $box['icon'],
				'heading'     => $box['heading'],
				'description' => $box['description'],
				'hasButton'   => $hasButton,
				'link'        => $hasButton ? MultisiteFixer::buildLink( $box['link'] ) : null,
			);
		}

		restore_current_blog();

		$content = array();

		if ( isset( $previewComponentOptions['description'] ) ) {
			$content[] = array(
				'acf_fc_layout' => 'description',
				'description'   => $previewComponentOptions['description'],
			);
		}

		if ( isset( $previewComponentOptions['link'] ) ) {
			$content[] = array(
				'acf_fc_layout' => 'link',
				'link'          => MultisiteFixer::buildLink( $previewComponentOptions['link'] ),
			);
		}

		return array(
			'heading'              => $offersOptions['heading'] ?? '',
			'showPreviewComponent' => $offersOptions['enable-preview-component'],
			'previewComponent'     => array(
				'reverse' => true,
				'image'   => $image->get(),
				'heading' => $previewComponentOptions['heading'] ?? null,
				'content' => $content,
			),
			'offerBoxes'           => $offerBoxes ?? null,
		);
	}

	private function getStockCarsSlider(): array {
		switch_to_blog( 1 );

		$stockOptions = get_field( 'stock-cars', 'options-homepage' );

		$excerpt = array();
		if ( array_filter( $stockOptions ) ) {
			$excerpt = array(
				'heading'     => $stockOptions['excerpt']['heading'],
				'description' => $stockOptions['excerpt']['description'],
				'link'        => MultisiteFixer::buildLink( $stockOptions['excerpt']['link'] ),
			);
		}

		restore_current_blog();

		return array(
			'heading' => $stockOptions['heading'],
			'cars'    => $this->getStockCars(),
			'excerpt' => $excerpt,
		);
	}

	private function getStockCars(): array {

		$categories = array_values( CarDictionary::getModelCategories() );

		$query = new \WP_Query(
			array(
				'post_type'      => 'stock-car',
				'posts_per_page' => '3',
				'cache_results'  => true,
				'meta_query'     => array(
					array(
						'key'     => 'category',
						'value'   => $categories,
						'compare' => 'IN',
					),
				),
			)
		);

		return $this->getCarsBy( $query );
	}

	private function getCarsBy( $query ): array {
		$cars = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$images = get_field( 'images' );
				$image  = null;
				if ( ! empty( $images ) ) {
					$image = $images[0];
					$image = new ImageBuilder( $image );
					$image->addSize( array( 288, 162 ) );
					$image->addSize( array( 576, 324 ) );
					$image->addSize( array( 864, 486 ) );
					$image->addMediaQuery( null, '288px', true );
					$getImage = $image->get();
				}

				$cars[] = array(
					'id'       => get_the_ID(),
					'image'    => $getImage ?? array(),
					'category' => get_field( 'category' ),
					'model'    => get_field( 'model' ),
					'engine'   => get_field( 'engine' ),
					'price'    => (get_field( 'discount-price' ) > 1000000 ? substr(get_field( 'discount-price' ), 0, -2) : get_field( 'discount-price' )),
					'url'      => get_the_permalink(),
				);
			}
		}

		return $cars;
	}
}
