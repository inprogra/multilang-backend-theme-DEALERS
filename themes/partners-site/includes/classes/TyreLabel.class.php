<?php

namespace Classes;

use Intervention\Image\ImageManagerStatic;

class TyreLabel {

	private $type;
	private $standardTyreLabelWidth = 390;
	private $sliderTyreLabelWidth   = 808;
	private $sliderTyreLabelHeight  = 453;

	public function __construct( $type ) {
		$this->type = $type;
	}

	public function save( $file, $destinationFileName, $postId ): string {
		$destination = self::getLabelsFolderPath( MultisiteFixer::getCurrentBlogId(), $postId ) . '/' . $this->type . '/' . $destinationFileName;
		$this->saveFile( $file, $destination );
		if ( $this->type === 'summer' ) {
			$this->saveSliderDimensionsTyreImage( $destination );
		} else {
			$this->saveStandardDimensionsTyreImage( $destination );
		}

		return self::getLabelsFolderUrl( MultisiteFixer::getCurrentBlogId(), $postId ) . '/' . $this->type . '/' . $destinationFileName;
	}

	public static function getLabelsFolderPath( $siteId, $postId ): string {
		return ABSPATH . '../' . 'tyre-labels/' . $siteId . '/' . $postId;
	}

	public static function getLabelsFolderUrl( $siteId, $postId ): string {
		return MultisiteFixer::getHomeUrl() . '/tyre-labels/' . $siteId . '/' . $postId;
	}


	private function saveStandardDimensionsTyreImage( $file ): void {
		$img = ImageManagerStatic::make( $file );
		$img->resize(
			$this->standardTyreLabelWidth,
			null,
			function ( $constraint ) {
				$constraint->aspectRatio();
				$constraint->upsize();
			}
		);
		$img->save( $file );
	}

	private function saveSliderDimensionsTyreImage( $file ): void {
		$img = ImageManagerStatic::make( $file );
		$img->resize(
			808,
			453,
			function ( $constraint ) {
				$constraint->aspectRatio();
				$constraint->upsize();
			}
		);
		$canvas = ImageManagerStatic::canvas( $this->sliderTyreLabelWidth, $this->sliderTyreLabelHeight, '#fff' );
		$canvas->insert( $img, 'center' );
		$canvas->save( $file );
	}

	private function saveFile( $file, $destination ): void {
		$curlConnection  = curl_init( $file );
		$destinationFile = fopen( $destination, 'wb' );
		curl_setopt( $curlConnection, CURLOPT_FILE, $destinationFile );
		curl_setopt( $curlConnection, CURLOPT_HEADER, 0 );
		curl_exec( $curlConnection );
		curl_close( $curlConnection );
		fclose( $destinationFile );
	}
}
