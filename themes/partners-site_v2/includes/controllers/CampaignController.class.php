<?php

namespace Controllers;

use Classes\Controller;
use Classes\MultisiteFixer;
use \Classes\Cache;

class CampaignController extends Controller {
	private $cache;
	public function render(): string {
		switch_to_blog(1);
		global $post;
		$this->cache = new \Classes\Cache();

		// print_r($post->post_content);
		// //var_dump($post->post_name);
		if ( !$query = $this->cache->get(MultisiteFixer::getCurrentBlogId().'-post-global-'.$post->ID) ) { 
			
			$check_global_campain = $check_campaign = new \WP_Query(
				array(
					'post_type'      => 'campaign',
					'posts_per_page' => -1,
					'name' => $post->post_name,
					'post_status'    => array( 'publish' )
				)
			);
			$this->cache->set(MultisiteFixer::getCurrentBlogId().'-post-global-'.$post->ID,$check_global_campain,3600);
		}
		else {
			$check_global_campain = $this->cache->get(MultisiteFixer::getCurrentBlogId().'-post-global-'.$post->ID);
		}
		
		if (empty($check_global_campain->posts)) {
			switch_to_blog( MultisiteFixer::getCurrentBlogId() );
			global $post; 
		}
		$content          = do_blocks( $post->post_content );
		$legalInfoContent = get_field( 'legal-info-content', $post->ID );
		
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );

		if ( ! $query = wp_cache_get('post-'.$post->ID) ) { 
			$check_campaign = new \WP_Query(
				array(
					'post_type'      => 'campaign',
					'posts_per_page' => -1,
					'name' => $post->post_name,
					'post_status'    => array( 'publish' )
				)
			);
		//	wp_cache_set('post-global-'.$post->ID,$check_campaign,'',3600);
		}
		else {
			$check_campaign = wp_cache_get('post-'.$post->ID);
		}
		
		
		if (!empty($check_campaign->posts)) {
			foreach ( $check_campaign->posts as $local_camp ) { 
				$content = do_blocks( $local_camp->post_content );
			}
		}
		$campaignOverride = new \WP_Query(
			array(
				'post_type'      => 'campaign-override',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'campaign',
						'value'   => $post->ID,
						'compare' => '=',
					),
				),
			)
		);

		foreach ( $campaignOverride->posts as $override ) {
			$additionalContent .= do_blocks( $override->post_content );
		}

		$landing_page = get_field('one_page');
		$side_form = (get_field('side_form') == 'on' ? get_field('side_form') : false);
		return $this->view(
			'layouts/campaign/campaign',
			array(
				'sideform' => $side_form,
				'onepage' => $landing_page,
				'content'           => $content,
				'legalInfoContent'  => $legalInfoContent,
				'additionalContent' => $additionalContent,
			)
		);
	}
}
