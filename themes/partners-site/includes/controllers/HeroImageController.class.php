<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\PictureBuilder;

class HeroImageController extends Controller {

	public function render(): string {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'heroImage.png' );
			return '<img src="' . $img . '" >';
		}

		$imageID = get_field( 'img' );

		$image = new ImageBuilder( $imageID );
		$image->addSize( array( 1224, 448 ) );
		$image->addSize( array( 2448, 896 ) );
		$image->addSize( array( 3672, 1344 ) );

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
		$image->addMediaQuery( '(min-width: 992px)', '1224px' );

		return $this->blockView(
			'components/organisms/hero-image/hero-image',
			array(
				'image' => $image->get(),
			)
		);
	}
}
