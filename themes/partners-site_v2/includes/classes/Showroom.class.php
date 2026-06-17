<?php

namespace Classes;
use Classes\CarDictionary;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Classes\MultisiteFixer;

class Showroom {
	private static bool $initialized = false;

	public  $allshowrooms 		= [];
	private static $showrooms           = array();
	private static $services            = array();
	private static $showroomsAndSerices = array();

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'init' ) );
		add_action( 'admin_menu', array( $this, 'removeFromAdminMenu' ), 100 );
		add_action( 'current_screen', array( $this, 'limitPermissionsForPostType' ), 999 );
		add_action( 'employee_category_edit_form', array( $this, 'hideDefaultFieldsForCategory' ) );
		add_action( 'employee_category_add_form', array( $this, 'hideDefaultFieldsForCategory' ) );
	}

	public static function init(): void {
		if (self::$initialized) {
            return;
        }

		switch_to_blog( MultisiteFixer::getCurrentBlogId() );

		$showrooms = new \WP_Query(
			array(
				'post_type'      => 'showroom',
				'post_status' => 'publish',
				'posts_per_page' => -1,
			)
		);
		
		foreach ( $showrooms->posts as $showroom ) {
			$isShowroom = get_field( 'has-showroom', $showroom->ID );
			$isService  = get_field( 'has-service', $showroom->ID );

			self::$showroomsAndSerices[] = $showroom->ID;

			if ( $isShowroom ) {
				self::$showrooms[] = $showroom->ID;
			}

			if ( $isService ) {
				self::$services[] = $showroom->ID;
			}
		}

		restore_current_blog();
		
		self::$initialized = true;
	}
	public function getShowroomsGlobal($type = null) {
		self::init();
		
		$importTool = new \Classes\CarDictionary(new \GuzzleHttp\Client());        
        $blog_ids = $importTool->getBlogIds();
		$showroomsArr = [];
		unset($blog_ids[0]);
		foreach($blog_ids as $b) {
			
			
		
		switch_to_blog( $b['blog_id'] );

		$showrooms = new \WP_Query(
			array(
				'post_type'      => 'showroom',
				'post_status' => 'publish',
				'posts_per_page' => -1,
			)
		);

		foreach ( $showrooms->posts as $showroom ) {
			$isShowroom = get_field( 'has-showroom', $showroom->ID );
			$isService  = get_field( 'has-service', $showroom->ID );

			self::$showroomsAndSerices[] = $showroom->ID;
			if ($type == 'all') {
				$showroomsArr[$showroom->ID] = $showroom;
				$showroomsArr[$showroom->ID]->blog_id = $b['blog_id'];
			} else {
				$showroomsArr[$showroom->ID] = $showroom->post_title;
			}
			
			
		}

		restore_current_blog();
		}
		
		return $showroomsArr;
	}
	public static function isMultiShowroomAndService(): bool {
		self::init();

		$filter_unique = array_unique(self::$showroomsAndSerices);
		
		return count( $filter_unique ) > 1;
	}

	public static function isMultiShowroom(): bool {
		self::init();

		$filterOut = array_unique(self::$showrooms);

		return count( $filterOut ) > 1;
	}

	public static function isMultiService(): bool {
		self::init();

		$filterOut = array_unique(self::$services);
		return count( $filterOut ) > 1;
	}

	public static function hasAnyService(): bool {
		self::init();

		$filterOut = array_unique(self::$services);
		return count( $filterOut ) > 0;
	}

	public static function getShowroomsAndServices(): array {
		self::init();

		$filterOut = array_unique(self::$showroomsAndSerices);
		return $filterOut;
	}

	public static function getShowrooms(): array {
		self::init();

		return self::$showrooms;
	}

	public static function getServices(): array {
		self::init();

		return self::$services;
	}

	public function hideDefaultFieldsForCategory() {
		echo '<style> .term-description-wrap,.term-slug-wrap { display:none; } </style>';
	}

	public function removeFromAdminMenu(): void {
		if ( is_main_site() ) {
			remove_menu_page( 'edit.php?post_type=showroom' );
		}
	}

	public function limitPermissionsForPostType( $current_screen ): void {
		if ( is_main_site() && $this->isShowroomPostType( $current_screen ) ) {
			echo 'Brak uprawnień';
			/** @noinspection ForgottenDebugOutputInspection */
			wp_die();
		}
	}

	private function isShowroomPostType( $current_screen ): bool {
		return ( $current_screen->base === 'post' || $current_screen->base === 'edit' ) && $current_screen->post_type === 'showroom';
	}
}
