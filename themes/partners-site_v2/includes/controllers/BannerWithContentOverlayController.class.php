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

		$image = [];
		$itemId = get_field( 'img' );
		
		$img_id = $itemId;
		$itemId = wp_get_attachment_url($itemId);
		
		$img_width = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($itemId)['path'])[0];
		$img_height = (int) getimagesize('/var/www/volvocars-partner.pl/partners-site/web' . parse_url($itemId)['path'])[1];
		$crop_image = get_field('field_crop');
	
		$crop_image = ($crop_image ? $crop_image : 'crop-center');
		$pimg = new ImageBuilder(-1,false);
		
		$ratio = $pimg->ratio($img_width,$img_height);
		$count_ratio = ($img_height > 0 ? $img_width/$img_height : false);
	
		$sizes = [
			[
				'blog_id' => get_current_blog_id(),
				'img_id' => $img_id,
				'height' => ($count_ratio > 1.7 ? 1000 : 1200),
				'width' => ($count_ratio > 1.7 ? 2500 : 2500),
				'crop'  => ($count_ratio > 1.7 ? 'crop' : $crop_image),
				'image' => $itemId,
				'query' => 1500
			],
			[
				'blog_id' => get_current_blog_id(),
				'img_id' => $img_id,	
				'height' => false,
				'width' => 1700,
				'crop'  => false,
				'image' => $itemId,
				'query' => 1200
			],
			[	'blog_id' => get_current_blog_id(),
				'img_id' => $img_id,
				'height' => false,
				'width' => 1200,
				'crop'  => false,
				'image' => $itemId,
				'query' => 900
			],
			[
				'blog_id' => get_current_blog_id(),
				'img_id' => $img_id,
				'height' => false,
				'width' => 900,
				'crop'  => false,
				'image' => $itemId,
				'query' => 700
			],
			[
				'blog_id' => get_current_blog_id(),
				'img_id' => $img_id,
				'height' => 767,
				'width' => 767,
				'crop'  => 'crop',
				'image' => $itemId,
				'query' => 100
			]
			
		];
		
		$images = $pimg->prepareImages($sizes);
		//$images = [];
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
				'image'       => $images,
				'heading'     => get_field( 'heading' ),
				'description' => get_field( 'description' ),
				'format'      => get_field( 'format_banner' ),
				'hasButton'   => $hasButton,
				'button'      => $button,
			)
		);
	}
}
