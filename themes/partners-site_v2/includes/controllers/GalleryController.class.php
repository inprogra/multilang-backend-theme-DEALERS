<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\PictureBuilder;

class GalleryController extends Controller {

	public function render(): string {
		$pimg = new ImageBuilder(-1, false);
		$blog_id = get_current_blog_id();
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'gallery.png' );
			return '<img src="' . $img . '" >';
		}

		$gallery      = get_field( 'gallery' );
		$galleryItems = array();

		foreach ( $gallery as $itemId ) {
			$img_id = $itemId;
			$itemId = wp_get_attachment_url($itemId);

			$images = [
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 1080,
					'width' => 1920,
					'crop' =>  'crop',
					'image' => $itemId,
					'query' => 1680,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,																		
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => 700,
					'width' => 1440,
					'crop' => 'false',
					'image' => $itemId,
					'query' => 1000,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				],
				[
					'blog_id' => $blog_id,
					'img_id' => $img_id,
					'height' => false,
					'width' => 1000,
					'crop' => 'crop',
					'image' => $itemId,
					'query' => 100,										
					'theight' => 300,
					'twidth' => 200,
					'tcrop' =>  false,		
				]
			];
			$images = $pimg->prepareImages($images);
			$galleryItems[] = array(
				'mobileImage'  => $images,
				'desktopImage' => $images,
				'full'         => '',
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
