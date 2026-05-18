<?php

namespace Classes;

use Classes\MultisiteFixer;
use Classes\Cache;

class PictureBuilder {

	private $attachmentId;
	private $alt;
	private $sources;
	private $image;
	private static $hash;

	public function __construct( $attachmentId, $alt = null ) {
		$this->attachmentId = $attachmentId;
		$this->alt          = $alt;
		self::$hash         = Cache::getAttachmentHash( $attachmentId );

		if ( ! $alt ) {
			$this->alt = get_post_meta( $attachmentId, '_wp_attachment_image_alt', true );
		}
	}

	private static function generateImage( $attachmentId, $size, $crop = true, $alt = null, $networkHomeUrl = false ): array {
		$attachment = fly_get_attachment_image_src( $attachmentId, $size, $crop );
		if ( $attachment === false || empty( $attachment ) ) {
			return array();
		}

		if ( ! isset( $attachment['src'] ) ) {
			$attachmentHolder = $attachment;
			$attachment       = array(
				'src'    => $attachmentHolder[0],
				'width'  => $attachmentHolder[1],
				'height' => $attachmentHolder[2],
			);
		}

		$attachment['src'] = Cache::buildHashUrl( MultisiteFixer::buildUrl( $attachment['src'], null, $networkHomeUrl ), self::$hash );
		$attachment['alt'] = $alt;

		if ( ! $alt ) {
			$attachment['alt'] = get_post_meta( $attachmentId, '_wp_attachment_image_alt', true );
		}

		return $attachment;
	}

	public static function getImage( $attachmentId, $size, $crop = true, $networkHomeUrl = false ): array {
		self::$hash = Cache::getAttachmentHash( $attachmentId );
		return self::generateImage( $attachmentId, $size, $crop, null, $networkHomeUrl );
	}

	public function add( $media, $size, $crop = true ): void {
		$attachment = self::generateImage( $this->attachmentId, $size, $crop );

		if ( $media ) {
			$attachment['media'] = $media;
			$this->sources[]     = $attachment;
		} else {
			$this->image = $attachment;
		}
	}

	public function get(): array {
		return array(
			'sources' => $this->sources,
			'image'   => $this->image,
			'alt'     => $this->alt,
		);
	}
}
