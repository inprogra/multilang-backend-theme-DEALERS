<?php

namespace Classes;

use Classes\MultisiteFixer;

class YoastOverride {

	private $postOverride;
	private $title;
	private $metaDesc;
	private $canonical;
	private $siteName;

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'removeYoastBox' ), 100 );

		if ( MultisiteFixer::getCurrentBlogId() !== 1 ) {
			$this->siteName = get_bloginfo( 'blogname' );
			add_action( 'wp', array( $this, 'getPostOverride' ), 100 );

			add_filter( 'wpseo_title', array( $this, 'overrideTitle' ), 10, 2 );
			add_filter( 'wpseo_metadesc', array( $this, 'overrideMetaDesc' ) );
			add_filter( 'wpseo_canonical', array( $this, 'overrideCanonical' ) );
			add_filter( 'wpseo_opengraph_url', array( $this, 'fixOgUrl' ) );
			add_filter( 'pre_option_blogname', array( $this, 'fixBlogName' ), 10, 3 );

		}
			add_filter( 'wpseo_sitemap_exclude_post_type', array($this,'sitemap_exclude_post_type'), 10, 2 );
			add_filter( 'wpseo_sitemap_exclude_author', array($this,'sitemap_exclude_authors'), 10, 2 );
			add_filter( 'wpseo_canonical', array($this,'swpseo_canonical_domain_replace' ));
			add_filter( 'wpseo_sitemap_exclude_taxonomy', array($this,'sitemap_exclude_taxonomy'), 10, 2 );
	}
	public function sitemap_exclude_authors( $users ) {
		return array_filter( $users, function( $user ) {
			
	 
			 return false;
		 } );
	 }
	public function sitemap_exclude_post_type($value, $post_type) {	
			$post_types = ['showroom','employee','author','model-override','campaign-override'];
			if (in_array($post_type,$post_types)) {


				return true;
			}
	}
	public function sitemap_exclude_taxonomy( $excluded, $taxonomy ) {
		return true;
	}
	public function swpseo_canonical_domain_replace( $url ) {

		$domain              = $_SERVER['HTTP_HOST']; // this can be loaded from option table if you want admin to set it.
		$parsed              = parse_url( home_url() );
		$current_site_domain = $parsed['host'];
		return str_replace( $current_site_domain, $domain, $url );
	}
	public function getPostOverride() {
		global $post;

		if ( ! is_admin() && in_array( $post->post_type, array( 'campaign' ) ) ) {
			switch_to_blog( MultisiteFixer::getCurrentBlogId() );
			$postOverride = new \WP_Query(
				array(
					'post_type'      => $post->post_type . '-override',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => $post->post_type,
							'value'   => $post->ID,
							'compare' => '=',
						),
					),
				)
			);

			if ( $postOverride->found_posts ) {
				$yoastMeta          = YoastSEO()->meta->for_post( $this->postOverride->ID );
				$this->postOverride = $postOverride->posts[0];

				$this->title     = $yoastMeta->title;
				$this->metaDesc  = $yoastMeta->description;
				$this->canonical = $yoastMeta->canonical;
			}
			restore_current_blog();
		}
	}

	public function removeYoastBox() {
		remove_meta_box( 'wpseo_meta', 'lead', 'normal' );
		remove_meta_box( 'wpseo_meta', 'employee', 'normal' );
		remove_meta_box( 'wpseo_meta', 'showroom', 'normal' );
	}

	public function overrideTitle( $title, $presentation ) {
		if ( $this->postOverride ) {
			$title = $this->title;
		}

		return $title;
	}

	public function overrideMetaDesc( $metaDesc ) {
		if ( $this->postOverride ) {
			$metaDesc = $this->metaDesc;
		}
		return $metaDesc;
	}

	public function overrideCanonical( $canonical ) {
		if ( $this->postOverride ) {
			$canonical = $this->canonical;
		}

		return $canonical;
	}

	public function fixOgUrl( $openGraphUrl ) {
		return MultisiteFixer::buildUrl( $openGraphUrl );
	}

	public function fixBlogName( $output ) {
		if ( MultisiteFixer::getCurrentBlogId() !== 1 && get_current_blog_id() === 1 ) {
			switch_to_blog( MultisiteFixer::getCurrentBlogId() );
			$output = get_option( 'blogname' );
			restore_current_blog();
		}
		return $output;
	}
}
