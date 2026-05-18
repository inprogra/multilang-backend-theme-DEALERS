<?php

namespace Classes;

use Classes\Cache;
use Classes\MultisiteFixer;

class ImageBuilder {

	private $attachmentId;
	private $hash;
	private $image;

	public function __construct( $attachmentId, $alt = null ) {
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
