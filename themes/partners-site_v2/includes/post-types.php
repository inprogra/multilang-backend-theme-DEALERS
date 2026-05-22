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
			'label'        => __( 'Categories', 'partners-site_v2' ),
			'rewrite'      => array( 'slug' => 'modele' ),
			'hierarchical' => true,
			'show_in_rest' => false,    // hide taxonomy in gutenberg editor
		)
	);

	register_taxonomy(
		'employee_category',
		'employee',
		array(
			'label'              => __( 'Działy', 'partners-site_v2' ),
			'hierarchical'       => false,
			'show_in_rest'       => false,
			'show_ui'            => true,
			'show_in_quick_edit' => false,
			'meta_box_cb'        => false,
		)
	);

	$customPostTypes = array(
		'lead'              => array(
			'label'           => __( 'Leady', 'partners-site_v2' ),
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
			'label'        => __( 'Kampanie', 'partners-site_v2' ),
			'labels'       => array(
				'singular_name' => __( 'Kampania', 'partners-site_v2' ),
				'add_new'       => __( 'Dodaj kampanię', 'partners-site_v2' ),
				'add_new_item'  => __( 'Dodaj kampanię', 'partners-site_v2' ),
				'edit_item'     => __( 'Edytuj kampanię', 'partners-site_v2' ),
				'new_item'      => __( 'Nowa kampania', 'partners-site_v2' ),
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
			'label'        => __( 'Kampanie globalne', 'partners-site_v2' ),
			'labels'       => array(
				'singular_name' => __( 'Kampania globalna', 'partners-site_v2' ),
				'add_new'       => __( 'Dodaj dopisek do kampanii globalnej', 'partners-site_v2' ),
				'add_new_item'  => __( 'Dodaj dopisek', 'partners-site_v2' ),
				'edit_item'     => __( 'Edytuj dopisek kampanii globalnej', 'partners-site_v2' ),
				'new_item'      => __( 'Nowy dopisek do kampanii globalnej', 'partners-site_v2' ),
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
			'label'        => __( 'Modele', 'partners-site_v2' ),
			'labels'       => array(
				'singular_name' => __( 'Model', 'partners-site_v2' ),
				'add_new'       => __( 'Dodaj model', 'partners-site_v2' ),
				'add_new_item'  => __( 'Dodaj nowy', 'partners-site_v2' ),
				'edit_item'     => __( 'Edytuj model', 'partners-site_v2' ),
				'new_item'      => __( 'Nowy model', 'partners-site_v2' ),
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
			'label'        => __( 'Modele', 'partners-site_v2' ),
			'labels'       => array(
				'singular_name' => __( 'Model', 'partners-site_v2' ),
				'add_new'       => __( 'Dodaj model', 'partners-site_v2' ),
				'add_new_item'  => __( 'Dodaj nowy', 'partners-site_v2' ),
				'edit_item'     => __( 'Edytuj model', 'partners-site_v2' ),
				'new_item'      => __( 'Nowy model', 'partners-site_v2' ),
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
				'singular_name' => __( 'Pracownik', 'partners-site_v2' ),
				'add_new'       => __( 'Dodaj pracownika', 'partners-site_v2' ),
				'add_new_item'  => __( 'Dodaj nowego pracownika', 'partners-site_v2' ),
				'edit_item'     => __( 'Edytuj pracownika', 'partners-site_v2' ),
				'new_item'      => __( 'Nowy pracownik', 'partners-site_v2' ),
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
			'label'               => __( 'Salony', 'partners-site_v2' ),
			'labels'              => array(
				'singular_name' => __( 'Salon', 'partners-site_v2' ),
				'add_new'       => __( 'Dodaj salon', 'partners-site_v2' ),
				'add_new_item'  => __( 'Dodaj nowy salon', 'partners-site_v2' ),
				'edit_item'     => __( 'Edytuj salon', 'partners-site_v2' ),
				'new_item'      => __( 'Nowy salon', 'partners-site_v2' ),
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
				'singular_name' => __( 'Post', 'partners-site_v2' ),
				'add_new'       => __( 'Dodaj post', 'partners-site_v2' ),
				'add_new_item'  => __( 'Dodaj post', 'partners-site_v2' ),
				'edit_item'     => __( 'Edytuj post', 'partners-site_v2' ),
				'new_item'      => __( 'Nowy post', 'partners-site_v2' ),
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
