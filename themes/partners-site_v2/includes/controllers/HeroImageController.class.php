<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\PictureBuilder;
use Classes\MultisiteFixer;

class HeroImageController extends Controller {

	public function render(): string {
		$block = $this->block; 
		

		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'heroImage.png' );
			return '<img src="' . $img . '" >';
		}

		$bigDescription   = get_field('bigDescription', $block['id']);
		$smallDescription = get_field('smallDescription', $block['id']);    
		$darkOverlay      = get_field('darkOverlay', $block['id']);

		$image = [];
		$itemId = get_field( 'img' );
		
		$img_id = $itemId;
		$itemId = wp_get_attachment_url($itemId);
		$itemId = $this->clearUrl($itemId);
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
				'height' => ($count_ratio > 1.7 ? 1024 : 1020),
				'width' => ($count_ratio > 1.7 ? 1920 : 1920),
				'crop'  => ($count_ratio > 1.7 ? 'max' : $crop_image),
				'image' => $itemId,
				'query' => 1500
			],
			[
				'blog_id' => get_current_blog_id(),
				'img_id' => $img_id,	
				'height' => false,
				'width' => 1500,
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
		
		return $this->blockView(
			'components/organisms/hero-image/hero-image',
			array(
				'image' => $images,
				'bigDescription'   => $bigDescription,
				'smallDescription'  => $smallDescription,
				'darkOverlay'      => $darkOverlay,
			)
		);
	}
	public function clearUrl($url)
	{
		$domain = get_blogaddress_by_id(MultisiteFixer::getCurrentBlogId());

		$url = str_replace('https://main.volvocars-partner.pl/', $domain, $url);

		return $url;
	}
}
