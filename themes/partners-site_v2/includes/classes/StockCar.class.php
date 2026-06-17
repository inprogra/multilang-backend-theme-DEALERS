<?php

namespace Classes;

class StockCar {
	public const POST_TYPE = 'stock-car';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'removeFromAdminMenu' ), 100 );
		add_action( 'current_screen', array( $this, 'limitPermissionsForPostType' ), 999 );
		add_action( 'delete_post', array( $this, 'deleteTyreLabelsFolder' ) );
	}

	public static function registerPostType() {
		register_post_type(
			'stock-car',
			array(
				'label'    => __('Cars available on site', 'partners-site_v2'),
				'public'   => true,
				'rewrite'  => array(
					'slug' => 'dostepne-na-miejscu',
				),
				'supports' => array( 'title' ),
			)
		);
	}

	public function removeFromAdminMenu(): void {
		if ( is_main_site() ) {
			remove_menu_page( 'edit.php?post_type=stock-car' );
		}
	}

	public function limitPermissionsForPostType( $current_screen ): void {
		if ( is_main_site() && $this->isStockCarPostType( $current_screen ) ) {
			echo 'Brak uprawnień';
			/** @noinspection ForgottenDebugOutputInspection */
			wp_die();
		}
	}

	public function deleteTyreLabelsFolder( $postId ) {
		if ( get_post_type( $postId ) === 'stock-car' ) {
			$carSpecification     = new CarSpecification();
			$tyreLabelsFolderPath = TyreLabel::getLabelsFolderPath( MultisiteFixer::getCurrentBlogId(), $postId );
			$carSpecification->removeFolder( $tyreLabelsFolderPath );
		}
	}

	public function get_stock_cars($args = null)
	{
		$args['post_type'] = self::POST_TYPE;
		
		return get_posts($args);
	}

	private function isStockCarPostType( $current_screen ): bool {
		return ( $current_screen->base === 'post' || $current_screen->base === 'edit' ) && $current_screen->post_type === 'stock-car';
	}
}
