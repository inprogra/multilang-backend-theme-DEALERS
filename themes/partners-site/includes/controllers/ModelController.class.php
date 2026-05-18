<?php

namespace Controllers;

use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;


class ModelController extends Controller {

	public function render(): string {
		switch_to_blog( 1 );
		$post = get_queried_object();

		$modelShortName = get_field( 'short-name' );

		$model = array(
			'heading'   => 'Volvo ' . get_field( 'name' ),
			'shortName' => $modelShortName,
			'versions'  => array(),
			'content'   => the_content(),
		);
		

		$versions = new \WP_Query(
			array(
				'post_type'      => 'model',
				'posts_per_page' => -1,
				'post_parent'    => $post->ID,
			)
		);
		$Parsedown = new \Parsedown();
		foreach ( $versions->posts as $version ) {
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

			$galleryPictures = array();
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

			$technicalDataRaw = get_field( 'technical-data', $version->ID );
			$technicalData    = array();

			foreach ( $technicalDataRaw as $key => $value ) {
				$str                   = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $key ) ) );
				$str[0]                = strtolower( $str[0] );
				$technicalData[ $str ] = $value;
			}

			$twoColumnContentComponent        = get_field( 'two-column-content-component', $version->ID );
			$twoColumnContentComponentImageId = $twoColumnContentComponent['image'];

			$twoColumnContentComponentImage = new ImageBuilder( $twoColumnContentComponentImageId );
			$twoColumnContentComponentImage->addSize( array( 600, null ) );
			$twoColumnContentComponentImage->addSize( array( 1200, null ) );
			$twoColumnContentComponentImage->addSize( array( 1800, null ) );

			$twoColumnContentComponentImage->addSize( array( 450, null ) );
			$twoColumnContentComponentImage->addSize( array( 900, null ) );
			$twoColumnContentComponentImage->addSize( array( 1350, null ) );

			$twoColumnContentComponentImage->addSize( array( 721, null ) );
			$twoColumnContentComponentImage->addSize( array( 1442, null ) );
			$twoColumnContentComponentImage->addSize( array( 2163, null ) );

			$twoColumnContentComponentImage->addSize( array( 959, null ) );
			$twoColumnContentComponentImage->addSize( array( 1918, null ) );
			$twoColumnContentComponentImage->addSize( array( 2877, null ) );

			$twoColumnContentComponentImage->addMediaQuery( null, '100vw', true );
			$twoColumnContentComponentImage->addMediaQuery( '(min-width: 992px)', '600px' );

			switch_to_blog( MultisiteFixer::getCurrentBlogId() );
			$versionOverride       = new \WP_Query(
				array(
					'post_type'      => 'model-override',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'model',
							'value'   => $version->ID,
							'compare' => '=',
						),
					),
				)
			);
			$versionOverrideBlocks = '';
			foreach ( $versionOverride->posts as $override ) {
				$versionOverrideBlocks .= do_blocks( $override->post_content );
			}
			switch_to_blog( 1 );
			$Parsedown = new \Parsedown();
			$contentParsedown = $twoColumnContentComponent['description'];
			$contentParsedown = $Parsedown->text($contentParsedown);
			$contentParsedown = str_replace('<ul','<ul class="content__list list"',$contentParsedown);
			$contentParsedown = str_replace('<li','<li class="list__item"',$contentParsedown);
			$model['versions'][] = array(
				'name'                      => get_field( 'name', $version->ID ),
				'price'                     => get_field( 'price', $version->ID ),
				'hide_price'				=> get_field('hide_price', $version->ID),
				'description'               => $Parsedown->text(get_field( 'description', $version->ID )),
				'technicalData'             => $technicalData,
				'erange'                    => $technicalData['erange'],
				'fullTechnicalDataLink'     => MultisiteFixer::buildLink( get_field( 'full-technical-data-link', $version->ID ) ),
				'featuredImage'             => $featuredImage->get(),
				'gallery'                   => $galleryPictures,
				'twoColumnContentComponent' => array(
					'heading'       => $twoColumnContentComponent['heading'],
					'content'       => array(
						array(
							'acf_fc_layout' => 'description',
							'description'   => $contentParsedown,
						),
					),
					'link'          => MultisiteFixer::buildLink( $twoColumnContentComponent['link'] ),
					'testDriveLink' => MultisiteFixer::getHomeUrl() . '/jazda-testowa?s_model=' . $modelShortName,
					'video'         => $twoColumnContentComponent['video'],
					'image'         => $twoColumnContentComponentImage->get(),
				),
				'content'                   => do_blocks( $version->post_content ),
				'overrideContent'           => $versionOverrideBlocks,
			);
		}

		return $this->view( 'layouts/model-single/model-single', $model );
	}
}
