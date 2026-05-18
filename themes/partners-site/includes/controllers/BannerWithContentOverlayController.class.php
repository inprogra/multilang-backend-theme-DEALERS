<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;

class BannerWithContentOverlayController extends Controller {

	public function render(): string {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'bannerWithContentOverlay.png' );
			return '<img src="' . $img . '" >';
		}

		$imageID = get_field( 'img' );

		$image = new ImageBuilder( $imageID );
		$image->addSize( array( 1920, 556 ) );
		$image->addSize( array( 3840, 1112 ) );

		$image->addSize( array( 1600, 556 ) );

		$image->addSize( array( 1366, 540 ) );

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

		$link = get_field( 'link' );

		$hasButton = ! empty( $link );
		$button    = array();

		if ( $hasButton ) {
			$button = MultisiteFixer::buildLink( $link );
			
			if (strpos($button['url'],'#') !== false) {
				
				$rep = explode('#',$button['url']);
				
				$button['url'] = '/dostepne-na-miejscu/#'.$rep[1];
			}
		}

		return $this->blockView(
			'components/organisms/banner-with-content-overlay/banner-with-content-overlay',
			array(
				'image'       => $image->get(),
				'heading'     => get_field( 'heading' ),
				'description' => get_field( 'description' ),
				'format'      => get_field( 'format_banner' ),
				'hasButton'   => $hasButton,
				'button'      => $button,
			)
		);
	}
}
