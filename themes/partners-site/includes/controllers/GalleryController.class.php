<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\PictureBuilder;

class GalleryController extends Controller {

	public function render(): string {

		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'gallery.png' );
			return '<img src="' . $img . '" >';
		}

		$gallery      = get_field( 'gallery' );
		$galleryItems = array();

		foreach ( $gallery as $itemId ) {
			$mobileImage = new ImageBuilder( $itemId );
			$mobileImage->addSize( array( 218, 164 ) );
			$mobileImage->addSize( array( 436, 328 ) );
			$mobileImage->addSize( array( 654, 492 ) );

			$mobileImage->addSize( array( 306, 164 ) );
			$mobileImage->addSize( array( 612, 328 ) );
			$mobileImage->addSize( array( 918, 492 ) );

			$mobileImage->addMediaQuery( null, '218px', true );
			$mobileImage->addMediaQuery( '(min-width: 720px)', '306px' );

			$desktopImage = new ImageBuilder( $itemId );
			$desktopImage->addSize( array( 322, 248 ) );
			$desktopImage->addSize( array( 644, 496 ) );
			$desktopImage->addSize( array( 966, 744 ) );

			$full = PictureBuilder::getImage( $itemId, 'full' );

			$galleryItems[] = array(
				'mobileImage'  => $mobileImage->get(),
				'desktopImage' => $desktopImage->get(),
				'full'         => $full,
				'domain'       => get_site_url(),

			);
		}

		return $this->blockView(
			'components/organisms/gallery/gallery',
			array(
				'gallery' => $galleryItems,
			)
		);
	}
}
