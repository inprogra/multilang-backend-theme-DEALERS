<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;

class PreviewComponentController extends Controller {

	public function render() {
		$blog_id = get_current_blog_id(); 
		$pimg = new ImageBuilder(-1, false);
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'previewComponent.png' );
			return '<img src="' . $img . '" >';
		}

		$imageId = get_field( 'img' );
		$img_id = $imageId;   
		$itemId = wp_get_attachment_url($imageId);
		$images = [
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 1024,				
				'width' => 1562,
				'crop' =>  'fit',
				'image' => $itemId,
				'query' => 1680,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,																		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 900,				
				'width' => 1300, 
				'crop' =>  'fit',
				'image' => $itemId,
				'query' => 1440,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,																		
			],
			[
				'blog_id' => $blog_id,
				'img_id' => $img_id,
				'height' => 800,
				'width' => 1100, 
				'crop' => 'crop',
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
				'width' => 1300,
				'crop' => 'crop',
				'image' => $itemId,
				'query' => 100,										
				'theight' => 300,
				'twidth' => 200,
				'tcrop' =>  false,		
			]
		];
	
		$images = $pimg->prepareImages($images);
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

				if ( $contentItem['acf_fc_layout'] === 'link' && isset( $contentItem['link'] ) && !empty( $contentItem['link'] ) ) {
					$contentItem['link'] = MultisiteFixer::buildLink( $contentItem['link'] );
			
					if ( strpos($contentItem['link']['url'], '---') !== false ) {
						// exit('chwileczke');
						$rep = explode('---', $contentItem['link']['url']);
						$contentItem['link']['url'] = '/dostepne-na-miejscu/#' . $rep[1];
					}
				} 
				
				
			}  
		}
	
		$reverse = get_field( 'image-position' ) ?? false;
		$allowed_tags = ['h1','h2','h3','h4','h5','h6'];
		$heading_tag = get_field('heading_tag');

		if (!in_array($heading_tag, $allowed_tags)) {
			$heading_tag = 'h2';
		}

		return $this->blockView(
			'components/organisms/preview-component/preview-component',
			array(
				'reverse' => $reverse == 'right',
				'image'   => $images,
				'heading_tag'  => $heading_tag,
				'heading' => get_field( 'heading' ),
				'content' => $content,
			)
		);
	}
}
