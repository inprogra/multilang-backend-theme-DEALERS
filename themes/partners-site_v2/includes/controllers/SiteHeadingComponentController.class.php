<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;

class SiteHeadingComponentController extends Controller {

	public function render() {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'siteHeading.png' );
			return '<img src="' . $img . '" >';
		}
		$version = get_field( 'field_quick_header' );
		
		if ( $version && $version == 'tak' ) {
			$template = 'grey-heading';
		} else if ($version && $version == 'blog' ) {
			$template = 'site-heading-blog';
		} else {
			$template = 'site-heading';
		}
		$current_blog_id = get_current_blog_id();
		return $this->blockView(
			'components/atoms/site-heading/' . $template,
			array(
				'heading'     => get_field( 'heading' ),
				'header_type' => get_field('header_type'),
				'description' => get_field( 'description' ),
				'date' => get_the_date('d.m.Y'),
				'tags'	=> $this->get_post_tags(),
				'current_blog_id' => $current_blog_id
			)
		);
	}

	private function get_post_tags()
	{
		$post_id = get_the_ID();
		$tags = wp_get_post_terms($post_id, 'post_tag');

		if ($tags && !is_wp_error($tags)) {
			return $tags;
		}

		return [];
	}
}
