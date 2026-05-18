<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;

class PreviewComponentController extends Controller {

	public function render() {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'previewComponent.png' );
			return '<img src="' . $img . '" >';
		}

		$imageId = get_field( 'img' );

		$image = new ImageBuilder( $imageId );
		$image->addSize( array( 450, null ) );
		$image->addSize( array( 900, null ) );
		$image->addSize( array( 1350, null ) );

		$image->addSize( array( 721, null ) );
		$image->addSize( array( 1442, null ) );
		$image->addSize( array( 2163, null ) );

		$image->addSize( array( 944, null ) );
		$image->addSize( array( 1888, null ) );
		$image->addSize( array( 2832, null ) );
		$image->addMediaQuery( null, '100vw', true );
		$image->addMediaQuery( '(min-width: 992px)', '944px' );

		$content = get_field( 'content' );

		if ( $content && array_filter( $content ) ) {
			foreach ( $content as &$contentItem ) {
				if ( $contentItem['acf_fc_layout'] === 'contact-info' ) {
					$personId                     = $contentItem['contact-info'];
					$contentItem['contactPerson'] = array(
						'name'     => get_field( 'name', $personId ) . ' ' . get_field( 'surname', $personId ),
						'position' => get_field( 'position', $personId ),
						'phone'    => get_field( 'phone', $personId ),
						'email'    => get_field( 'email', $personId ),
					);
				}

				if ( $contentItem['acf_fc_layout'] === 'link' && isset( $contentItem['link'] ) ) {
					$contentItem['link'] = MultisiteFixer::buildLink( $contentItem['link'] );
					if (strpos($contentItem['link']['url'], '---') !== false) {

                        $rep = explode('---', $contentItem['link']['url']);

                        $contentItem['link']['url'] = '/dostepne-na-miejscu/#' . $rep[1];
                    }
				}
			}
		}

		$reverse = get_field( 'image-position' ) ?? false;

		return $this->blockView(
			'components/organisms/preview-component/preview-component',
			array(
				'reverse' => $reverse == 'right',
				'image'   => $image->get(),
				'heading' => get_field( 'heading' ),
				'content' => $content,
			)
		);
	}
}
