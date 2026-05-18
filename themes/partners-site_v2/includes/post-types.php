<?php

use Classes\StockCar;
use Classes\CarSpecification;

add_action( 'init', 'registerThemePostTypes' );
function registerThemePostTypes() {
	StockCar::registerPostType();
	CarSpecification::registerPostType();

	register_taxonomy(
		'model_category',
		'model',
		array(
			'label'        => 'Kategorie',
			'rewrite'      => array( 'slug' => 'modele' ),
			'hierarchical' => true,
			'show_in_rest' => false,    // hide taxonomy in gutenberg editor
		)
	);

	register_taxonomy(
		'employee_category',
		'employee',
		array(
			'label'              => 'Działy',
			'hierarchical'       => false,
			'show_in_rest'       => false,
			'show_ui'            => true,
			'show_in_quick_edit' => false,
			'meta_box_cb'        => false,
		)
	);

	$customPostTypes = array(
		'lead'              => array(
			'label'           => 'Leady',
			'public'          => false,
			'rewrite'         => false,
			'show_ui'         => true,
			'capability_type' => 'lead',
			'capabilities'    => array(
				'read_post'           => 'lead_read',
				'create_posts'        => 'lead_create',
				'delete_posts'        => 'leads_delete',
				'delete_others_posts' => 'leads_others_delete',
				'delete_post'         => 'lead_delete',
				'publish_posts'       => 'lead_publish',
			),
			'has_archive'     => false,
			'hierarchical'    => false,
			'supports'        => array( 'revisions' ),
		),
		'campaign'          => array(
			'label'        => 'Kampanie',
			'labels'       => array(
				'singular_name' => 'Kampania',
				'add_new'       => 'Dodaj kampanię',
				'add_new_item'  => 'Dodaj kampanię',
				'edit_item'     => 'Edytuj kampanię',
				'new_item'      => 'Nowa kampania',
			),
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'show_in_menu' => true,
			'hierarchical' => false,
			'supports'     => array( 'title', 'editor', 'revisions' ),
			'rewrite'      => array(
				'slug'       => 'kampanie',
				'with_front' => false,
			),
		),
		'campaign-override' => array(
			'label'        => 'Kampanie globalne',
			'labels'       => array(
				'singular_name' => 'Kampania globalna',
				'add_new'       => 'Dodaj dopisek do kampanii globalnej',
				'add_new_item'  => 'Dodaj dopisek',
				'edit_item'     => 'Edytuj dopisek kampanii globalnej',
				'new_item'      => 'Nowy dopisek do kampanii globalnej',
			),
			'has_archive'  => false,
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'supports'     => array( 'title', 'editor', 'revisions' ),
		),
		'model'             => array(
			'label'        => 'Modele',
			'labels'       => array(
				'singular_name' => 'Model',
				'add_new'       => 'Dodaj model',
				'add_new_item'  => 'Dodaj nowy',
				'edit_item'     => 'Edytuj model',
				'new_item'      => 'Nowy model',
			),
			'has_archive'  => false,
			'rewrite'      => array(
				'slug'       => 'modele/%model_category%',
				'with_front' => false,
			),
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'taxonomies'   => array( 'model_category' ),
			'supports'     => array( 'page-attributes', 'title', 'editor', 'revisions' ),
		),
		'model-override'    => array(
			'label'        => 'Modele',
			'labels'       => array(
				'singular_name' => 'Model',
				'add_new'       => 'Dodaj model',
				'add_new_item'  => 'Dodaj nowy',
				'edit_item'     => 'Edytuj model',
				'new_item'      => 'Nowy model',
			),
			'has_archive'  => false,
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'supports'     => array( 'title', 'editor', 'revisions' ),
		),
		'employee'          => array(
			'label'        => 'Pracownicy',
			'labels'       => array(
				'singular_name' => 'Pracownik',
				'add_new'       => 'Dodaj pracownika',
				'add_new_item'  => 'Dodaj nowego pracownika',
				'edit_item'     => 'Edytuj pracownika',
				'new_item'      => 'Nowy pracownik',
			),
			'has_archive'  => false,
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'supports'     => array( 'title', 'editor', 'revisions' ),
		),
		'showroom'          => array(
			'label'               => 'Salony',
			'labels'              => array(
				'singular_name' => 'Salon',
				'add_new'       => 'Dodaj salon',
				'add_new_item'  => 'Dodaj nowy salon',
				'edit_item'     => 'Edytuj salon',
				'new_item'      => 'Nowy salon',
			),
			'has_archive'         => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'revisions' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts'        => 'showroom_create',
				'delete_posts'        => 'showrooms_delete',
				'delete_others_posts' => 'showrooms_others_delete',
				'delete_post'         => 'showroom_delete',
				'publish_posts'       => 'showroom_publish',
			),
			'map_meta_cap'        => true,
		),
		'blog'          => array(
			'label'        => 'Blog',
			'labels'       => array(
				'singular_name' => 'Post',
				'add_new'       => 'Dodaj post',
				'add_new_item'  => 'Dodaj post',
				'edit_item'     => 'Edytuj post',
				'new_item'      => 'Nowy post',
			),
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'show_in_menu' => true,
			'hierarchical' => false,
			'supports'     => array( 'title', 'editor', 'revisions', 'author', 'excerpt', 'thumbnail' ),
			'rewrite'      => array(
				'slug'       => 'blog'
			),
			'taxonomies' => ['post_tag'],
			'publicly_queryable' => true
		),
	);

	foreach ( $customPostTypes as $name => $args ) {
		if ($name == 'blog' && (get_current_blog_id() == 13)) {
			
		}	else {
			register_post_type( $name, $args );
		}
	}
}

add_action( 'init', 'addCapabilitiesForAdmin' );
function addCapabilitiesForAdmin() {
	if ( get_option( 'is_admin_role_updated' ) != 2 ) {
		$admin = get_role( 'administrator' );

		$admin->add_cap( 'lead' );
		$admin->add_cap( 'lead_read' );
		$admin->add_cap( 'lead_create' );
		$admin->add_cap( 'leads_delete' );
		$admin->add_cap( 'leads_others_delete' );
		$admin->add_cap( 'lead_delete' );
		$admin->add_cap( 'lead_publish' );

		$admin->add_cap( 'showroom_create' );
		$admin->add_cap( 'showrooms_delete' );
		$admin->add_cap( 'showrooms_others_delete' );
		$admin->add_cap( 'showroom_delete' );
		$admin->add_cap( 'showroom_publish' );

		update_option( 'is_admin_role_updated', 2 );
	}
}

add_action( 'admin_notices', 'general_admin_notice' );
function general_admin_notice() {
	$screen = get_current_screen();
	if ( false ) {
		switch ( $screen->id ) {
			case 'upload':   // Media
				break;
			case 'edit-page':   // Strony
				break;
			case 'edit-stock-car':   // Samochody dostępne na miejscu
				break;
			case 'edit-campaign':   // Kampanie
				break;
			case 'edit-campaign-override':   // Nadpisywanie kampanii
				echo '<div class="notice notice-info">' .
					'<h3>Witamy na ekranie Kampanii globalnych!</h3>' .
					'<p>W tym miejscu moves dodać własne treści do istniejących już kampanii globalnych wyświetlanych na Twojej stronie.</p>' .
					'<p>Stwórz poniżej stronę i dodaj w niej treści które wyświetlą się pod kampanią która wybierzesz.</p>' .
					'<p>Nie zapomnij wybrać nowej kampanii w <a href="/wp/wp-admin/admin.php?page=options-homepage">Opcjach Strony Głównej</a></p>' .
					'</div>';
				break;
			case 'edit-model-override':   // Modele
				break;
			case 'edit-employee':   // Pracownicy
				break;
			case 'toplevel_page_options-homepage':   // Opcje strony głównej
				break;
			case 'toplevel_page_options-service':   // Opcje strony serwisowej
				break;
			case 'toplevel_page_options-dealer':   // Opcje dealera
				break;
		}
	}
}
