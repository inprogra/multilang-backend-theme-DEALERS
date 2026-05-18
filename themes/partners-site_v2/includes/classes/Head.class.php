<?php

namespace Classes;

use Classes\MultisiteFixer;
use Classes\Cache;
use Classes\Showroom;
use Classes\WP_Pixel;
use Controllers\CookiesController;
use function Env\env;

class Head {

	private $items = array();

	public function __construct() {
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$googleMapsKey = get_field( 'google-maps-key', 'options-dealer' );
		$head_code     = get_field( 'field_additional_code', 'options-dealer' );

		// wp pixel
		$wp_pixel_key = get_field('wpPixelKey', 'options-dealer');
		$wp_pixel = get_field('wpPixel', 'options-dealer');
        $this->generateWPTrackingScript($wp_pixel_key, $wp_pixel);
		/////

		// DoubleClick 
		$this->addHtml(<<<HTML

		<script async src="https://www.googletagmanager.com/gtag/js?id=DC-15535071"></script>
		<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'DC-15535071');
		</script>
		HTML
		);
		/////


		if ( $head_code ) {
			$this->addHtml( $head_code );
		}
		$showrooms  = Showroom::getShowrooms();
		$showroomId = get_field( 'showroomId', $showrooms[0] );
		restore_current_blog();

		$this->addTag(
			'meta',
			array(
				'charset' => get_bloginfo( 'charset', 'display' ),
			)
		);

		$this->addTag(
			'meta',
			array(
				'name'    => 'viewport',
				'content' => 'width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0',
			)
		);

		$this->addWpHead();

		foreach ( array( '16x16', '32x32', '192x192' ) as $size ) {
			$this->addTag(
				'link',
				array(
					'rel'   => 'icon',
					'href'  => esc_url( Cache::getAsset( 'favicon-' . $size . '.v2.png' ) ),
					'sizes' => $size,
				)
			);
		}

		$this->addTag(
			'link',
			array(
				'rel'  => 'icon',
				'type' => 'image/svg+xml',
				'href' => esc_url( Cache::getAsset( 'favicon-16x16.v2.svg' ) ),
			)
		);

		$this->addTag(
			'link',
			array(
				'rel'   => 'apple-touch-icon',
				'sizes' => '180x180',
				'href'  => esc_url( Cache::getAsset( 'favicon-180x180.v2.png' ) ),
			)
		);

		$this->addTag(
			'link',
			array(
				'data-type' => 'lazy',
				'rel'  => 'stylesheet',
				'href' => get_template_directory_uri().'/assets/public/app.min.css',
			)
		);

		// $this->addTag(
		// 	'script',
		// 	array(
		// 		'data-type' => 'lazy',
		// 		'src' => get_template_directory_uri() .'/assets/public/app.min.js',
		// 	),
		// 	false
		// );

		if ( $googleMapsKey && $_SERVER['REQUEST_URI'] == '/kontakt/' ) {
			$this->addTag( 'script', array(), false, "var mapsApiKey = '" . $googleMapsKey . "'" );
		}

		$this->addHtml( Cache::getHeadPlaceHolder() );

		$this->addTag( 'script', array(), false, "var showRoom = '" . $showroomId . "'" );

		if ( is_user_logged_in() ) {
			$this->addTag('link', [
			'rel' => 'stylesheet',
			'href' => Cache::getAsset('cache.css'),
			]);

			$this->addTag('script', [
			'src' => Cache::getAsset('cache.js')
			], false);
		} 
	}


	//pixel
	public function generateWPTrackingScript($wp_pixel_key, $wp_pixel) {
		
		WP_Pixel::loadPixelData($wp_pixel_key, $wp_pixel);
	}

	


	private function addTag( $tag, $attributes = array(), $selfClosing = true, $content = '' ) {
		$this->items[] = array(
			'tag'         => $tag,
			'attributes'  => $attributes,
			'selfClosing' => $selfClosing,
			'content'     => $content,
		);
	}

	private function addHtml( $html ) {
		$this->items[] = $html;
	}

	private function addWpHead() {
		ob_start();
		wp_head();
		$wp_head = ob_get_clean();
		$this->addHtml( $wp_head ); 
	}
	
	public function print() {
		$output = '';
		$googleTagManager = CookiesController::getGtmScript();

		if ( strpos( $googleTagManager, '<script>' ) == false ) {
			$googleTagManager = '<script>' . $googleTagManager . '</script>';
		}
		
		// //var_dump($this->items);
		// exit();
		foreach ( $this->items as $key => $item ) {
			$status = true;
			// if (array_key_exists('src',$item['attributes']) && strpos($item['attributes']['src'],'app.js') !== false) {
			// $status = false;
			// }
			if ( $status ) {
				if ( is_array( $item ) ) {

					if ( $status ) {
					}
					$itemHtml = '<' . $item['tag'];

					if ( ! empty( $item['attributes'] ) ) {
						$itemHtml .= ' ';
					}

					foreach ( $item['attributes'] as $attribute => $value ) {
						$itemHtml .= $attribute . '="' . $value . '"';
						if ( $attribute !== array_key_last( $item['attributes'] ) ) {
							$itemHtml .= ' ';
						}
					}

					$itemHtml .= '>';
					if ( ! $item['selfClosing'] ) {
						$itemHtml .= $item['content'] . '</' . $item['tag'] . '>';
					}

					$output .= $itemHtml;

				} elseif ( is_string( $item ) ) {
					$output .= $item;
				}
				$output .= "\n";
			}
		}
		$output = str_replace( '<!--[[CACHE_PLACEHOLDER]]-->', $googleTagManager, $output );
		echo $output;
	}
}
