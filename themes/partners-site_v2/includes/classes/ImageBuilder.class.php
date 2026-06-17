<?php

namespace Classes;

use Classes\Cache;
use Classes\MultisiteFixer;

class ImageBuilder {

	private $attachmentId;
	private $hash;
	private $image;
	private $cache;
	public function __construct( $attachmentId, $alt = null ) {
		$this->cache = new \Classes\Cache();
		if ($attachmentId !== -1) {
		$this->attachmentId = $attachmentId;
		$this->hash         = Cache::getAttachmentHash( $attachmentId );

		if ( ! $alt ) {
			$alt = get_post_meta( $attachmentId, '_wp_attachment_image_alt', true );
		}

		$this->image = array(
			'alt'               => $alt,
			'sizes'             => array(),
			'mediaQueries'      => array(),
			'defaultMediaQuery' => array(),
		);
		}
	}
	public function gcd($a, $b)
	{	
		if ($b == NULL || $b == 0) {
			return false;
		}
		
		return ($a % $b) ? $this->gcd($b, $a % $b) : $b;
	}
	public function ratio($x, $y)
	{
		$gcd = $this->gcd($x, $y);
		
		if ($gcd) {
			return ($x / $gcd) . ':' . ($y / $gcd);
		} 
	}
	public function clearUrl($url)
	{
		$domain = get_blogaddress_by_id(MultisiteFixer::getCurrentBlogId());

		$url = str_replace('https://main.volvocars-partner.pl/', $domain, $url);

		return $url;
	}	
	public function prepareImages(array $data) {
		
		foreach($data as $key => $value) {
			// 1-6498-810-1200-max
		
				$cache_check = $this->cache->getDatabaseKey( $value['blog_id'].'-'.$value['img_id'].'-'.$value['width'].'-'.$value['height'].'-'.$value['crop']);
				// $cache_check = false;
				if (!$cache_check) {
				$data[$key]['src'] = 'https://image-render.cloud/api/renderImage?image='.$value['image'].'&size='.$value['width'].'&height='.($value['height'] ? $value['height'] : false).'&fit='.($value['crop'] ? $value['crop'] : false).'&flip='.($value['crop'] ? 1 : false).'&blog_id='.$value['blog_id'].'&img_id='.$value['img_id'];
				} else {
					$data[$key]['src'] = $cache_check;
				}
				$data[$key]['thumb'] = 'https://image-render.cloud/api/renderImage?image='.$value['image'].'&size='.$value['twidth'].'&height='.($value['theight'] ? $value['theight'] : false).'&fit='.($value['tcrop'] ? $value['tcrop'] : false).'&flip='.($value['tcrop'] ? 1 : false).'&blog_id='.$value['blog_id'].'&img_id='.$value['img_id'];
		}
		return $data;
	}
	public function get() {
		return $this->image;
	}



	public function addSize( $size, $crop = true, $networkHomeUrl = false ) {
		
		$size = $this->generate( $this->attachmentId, $size, $crop, $networkHomeUrl );
	
		if (!$size) {
			
			$size = $this->generate( $this->attachmentId, $size, $crop, true );
			
			// //var_dump($size);
		}
		$this->image['sizes'][] = $size;
	}

	public function addMediaQuery( $mediaQuery, $size, $default = false ) {
		if ( $default ) {
			$this->image['defaultMediaQuery'] = array(
				'size' => $size,
			);
		} else {
			$this->image['mediaQueries'][] = array(
				'mediaQuery' => $mediaQuery,
				'size'       => $size,
			);
		}
	}

	private function generate( $attachmentId, $size, $crop = true, $networkHomeUrl = false ): array {
		$generatedImage = fly_get_attachment_image_src( $attachmentId, $size, $crop );
		
		if ($networkHomeUrl) {
			switch_to_blog( 1 );
			$generatedImage = fly_get_attachment_image_src( $attachmentId, $size, $crop );
			restore_current_blog();
		}
		


		if ( $generatedImage === false || empty( $generatedImage ) ) {
			return array();
		}
	
		$generatedImage['src'] = Cache::buildHashUrl( MultisiteFixer::buildUrl( $generatedImage['src'], null, $networkHomeUrl ), $this->hash );
	
		return $generatedImage;
	}
}
