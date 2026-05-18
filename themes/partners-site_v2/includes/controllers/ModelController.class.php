<?php

namespace Controllers;

use Classes\Controller;
use Classes\FeaturedCars;
use Classes\ImageBuilder;
use Classes\MultisiteFixer;
use Classes\PictureBuilder;
use \Spatie\Glide\GlideImage as GlideImage;
use \Classes\Cache;

class ModelController extends Controller {
	private $cache;
	public function __construct() {
		$this->cache = new \Classes\Cache();
	}
	public function render(): string {
		
		
		switch_to_blog( 1 );
		$post = get_queried_object();
		$pimg = new ImageBuilder(-1, false);
		$modelShortName = get_field( 'short-name' );
		$model = $this->cache->get(get_current_blog_id().'-'.str_replace(' ','_',$modelShortName));
		// $model = false;
		if (!$model) {
		$model = array(
			'heading'   => get_field( 'name' ),
			'shortName' => $modelShortName,
			'versions'  => array(),
			'content'   => the_content(),
			'slug_url' 	=> parse_url(get_the_permalink())['path']
		);
		
		//wp_cache_flush();
		$versions = new \WP_Query(
			array(
				'post_type'      => 'model',
				'posts_per_page' => -1,
				'cache'			 => false,
				'post_parent'    => $post->ID,
			)
		);
		$featuredCarsOptions = get_field('featured-cars', 'options-global');
		$Parsedown = new \Parsedown();
		
		foreach ( $versions->posts as $version ) {
			$colors = [];
			$gallery = get_field( 'gallery', $version->ID );
			$gallery_ids = $gallery;
			
			$colors = get_field('field_version_colors_content',$version->ID);
			
			if ($colors['version_interrior_color_tags'] && is_array($colors)) {
			foreach($colors['version_interrior_color_tags'] as $key=>$c) {
				$colors['version_interrior_color_tags'][$key]->icon = get_field('model_category_img',$c->taxonomy.'_'.$c->term_id);
			}
			}
			
			$temp_arr = [];
			$default_gallery = $colors['version_default_gallery'];
			if (!empty($default_gallery)) {
				
				foreach ($default_gallery as $v) {				
					array_push($temp_arr,$v->slug);
				}
				
			}
			if ($colors['cards']) {
			foreach($colors['cards'] as $key=>$s) {
				if ($colors['cards'][$key]['version_color_tags'][0]) {
				$colors['cards'][$key]['version_color_tags'][0]->icon = get_field('model_category_img',$colors['cards'][$key]['version_color_tags'][0]->taxonomy.'_'.$colors['cards'][$key]['version_color_tags'][0]->term_id);
				}
				$compare_default_gallery = [$s['version_color_tags'][0]->slug,$s['gallery_type']];
				if (array_diff($temp_arr,$compare_default_gallery) == null && $s['gallery_type'] !== 'auto') {
					$gallery = $s['version_gallery'];
				}
				
			}
		}
				
			$featuredImage = $gallery[0];
			
			$galleryPictures = [];
			$galleryThumbs = [];
			foreach ( $gallery_ids as $itemId ) {
				
				$img_id = $itemId;
				
				$url_check = wp_get_attachment_url($itemId);
				
				
				$blog_id = get_current_blog_id();
				$itemId = wp_get_attachment_url($itemId);
				if (strpos($itemId,'.mp4') !== false) {
					
				} else {
					
				}
				$file = parse_url($itemId);
				$file_url = '/var/www/volvocars-partner.pl/partners-site/web/'.$file['path'];
				$s = 1030;
				//$thumbnail = $this->cache->get($itemId.'-'.$s);
				$s = 500;
				
				$images = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 1080,
						'width' => 1920,
						'crop' =>  false,
						'image' => $itemId,
						'query' => 1680
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 700,
						'width' => 1440,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1000
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => false,
						'width' => 1000,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100
					]
				];
				$imagesThumbs = [
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 200,
						'width' => 500,
						'crop' =>  false,
						'image' => $itemId,
						'query' => 1680
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => 200,
						'width' => 300,
						'crop' => 'false',
						'image' => $itemId,
						'query' => 1000
					],
					[
						'blog_id' => $blog_id,
						'img_id' => $img_id,
						'height' => false,
						'width' => 300,
						'crop' => 'crop',
						'image' => $itemId,
						'query' => 100
					]
				];
				$images = $pimg->prepareImages($images);	
				$galleryPictures[] = $images;
			
				$images = $pimg->prepareImages($imagesThumbs);	
				$galleryThumbs[] = $images;
			}
			
			$technicalDataRaw = get_field( 'technical-data', $version->ID );
			$technicalData    = array();

			foreach ( $technicalDataRaw as $key => $value ) {
				$str                   = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $key ) ) );
				$str[0]                = strtolower( $str[0] );
				$technicalData[ $str ] = $value;
			}

			$twoColumnContentComponent        = get_field( 'two-column-content-component', $version->ID );
			$twoColumnContentComponentImageId = $twoColumnContentComponent['image'];
			$video = $twoColumnContentComponent['field_version_video'];
			
			if ($twoColumnContentComponentImageId) {
				$twoColumnImage = wp_get_attachment_image_url($twoColumnContentComponentImageId);
				// $client = new \GuzzleHttp\Client();
				// $data = $client->request('GET', 'https://image-render.cloud/api/resizeImages', ['query' => ['image' => $twoColumnImage,'sizes'=> '1200.700,1800,450,900,1350,721,1442,2163,959'], 'synchronous' => false]);
				 $twoColumnImageSizes = ['1200,700,1800,450,900,1350,721,1442,2163,959'];
			}
			
			
			switch_to_blog( MultisiteFixer::getCurrentBlogId() );
			$versionOverride       = new \WP_Query(
				array(
					'post_type'      => 'model-override',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'model',
							'value'   => $version->ID,
							'compare' => '=',
						),
					),
				)
			);
			$versionOverrideBlocks = '';
			foreach ( $versionOverride->posts as $override ) {
				$versionOverrideBlocks .= do_blocks( $override->post_content );
			}
			$stock = new FeaturedCars();
			$featuredCars = $stock->get();
			
			if (count($featuredCars) > 0) {
				$featuredCarsHeading = $featuredCarsOptions['all-cars-heading'];
			} else {
				$featuredCarsHeading = $featuredCarsOptions['not-found-heading'];
			}
			
			$model['featuredCars']= array(
				'heading' => $featuredCarsHeading,
				'cars' => $featuredCars,
			);
			switch_to_blog( 1 );
			

			
			$Parsedown = new \Parsedown();
			$contentParsedown = $twoColumnContentComponent['description'];
			$contentParsedown = $Parsedown->text($contentParsedown);
			$contentParsedown = str_replace('<ul','<ul class="content__list list"',$contentParsedown);
			$contentParsedown = str_replace('<li','<li class="list__item"',$contentParsedown);
			

			$model['versions'][] = array(
				'name'                      => get_field( 'name', $version->ID ),
				'price'                     => get_field( 'price', $version->ID ),
				'hide_price'				=> get_field('hide_price', $version->ID),
				'description'               => $Parsedown->text(get_field( 'description', $version->ID )),
				'desc_more'					=>  $contentParsedown,
				'title'						=> $twoColumnContentComponent['heading'],
				'technicalData'             => $technicalData,
				'erange'                    => $technicalData['erange'],
				'fullTechnicalDataLink'     => MultisiteFixer::buildLink( get_field( 'full-technical-data-link', $version->ID ) ),
				'featuredImage'             => $featuredImage,
				'gallery'                   => $galleryPictures,
				'gallery_thumbs'		    => $galleryThumbs,				
				'colors'					=> $colors,
				'twoColumnContentComponent' => array(
					'heading'       => $twoColumnContentComponent['heading'],
					'content'       => array(
						array(
							'acf_fc_layout' => 'description',
							'description'   => '',
						),
					),
					'link'          => MultisiteFixer::buildLink( $twoColumnContentComponent['link'] ),
					'testDriveLink' => MultisiteFixer::getHomeUrl() . '/jazda-testowa?s_model=' . $modelShortName,
					'video'         => $twoColumnContentComponent['video'],
					'custom_video'  => (is_array($video) ? $video[0] : false),
					'image'         => ($twoColumnImage ? 'https://image-render.cloud/api/renderImage?image='.$twoColumnImage.'&size=800' : false),
					'sizes'			=> $twoColumnImageSizes
				),
				'content'                   => do_blocks( $version->post_content, true ),
				'overrideContent'           => $versionOverrideBlocks,
				
			);
		}
		restore_current_blog();
		$this->cache->set(get_current_blog_id().'-'.$modelShortName, $model);
	}
		
		return $this->view( 'layouts/model-single/model-single', $model );
	}
}
