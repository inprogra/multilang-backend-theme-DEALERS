<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;

class BlogPostFooterController extends Controller {

	public function render() {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'previewComponent.png' );
			return '<img src="' . $img . '" >';
		}

		$author_data = [];

		$post_author_id = get_post_field('post_author', get_the_ID());
		if ($post_author_id) {
			$post_author = get_userdata($post_author_id);

			if ($post_author) {
				$author_data['name'] = $post_author->display_name;
				$author_data['email'] = $post_author->user_email;
				$author_data['phone'] = get_user_meta($post_author_id, 'phone_number', true);
				$author_data['position'] = get_user_meta($post_author_id, 'user_bio', true);
			}
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

				if ( $contentItem['acf_fc_layout'] === 'link' && isset( $contentItem['link'] ) && is_array($contentItem['link']) ) {
					$contentItem['link'] = MultisiteFixer::buildLink( $contentItem['link'] );
				}
			}
		}

		$reverse = get_field( 'image-position' ) ?? false;

		return $this->blockView(
			'components/organisms/blog-post-footer/blog-post-footer',
			array(
				'image'   => $image->get(),
				'heading' => get_field( 'heading' ),
				'content' => $content,
				'author' => $author_data
			)
		);
	}
}
