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
			'label'              => __('Departments', 'partners-site_v2'),
			'hierarchical'       => false,
			'show_in_rest'       => false,
			'show_ui'            => true,
			'show_in_quick_edit' => false,
			'meta_box_cb'        => false,
		)
	);

	$customPostTypes = array(
		'lead'              => array(
			'label'           => __('Leads', 'partners-site_v2'),
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
			'label'        => __('Campaigns', 'partners-site_v2'),
			'labels'       => array(
				'singular_name' => __('Campaign', 'partners-site_v2'),
				'add_new'       => __('Add campaign', 'partners-site_v2'),
				'add_new_item'  => __('Add campaign', 'partners-site_v2'),
				'edit_item'     => __('Edit campaign', 'partners-site_v2'),
				'new_item'      => __('New campaign', 'partners-site_v2'),
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
			'label'        => __('Global campaigns', 'partners-site_v2'),
			'labels'       => array(
				'singular_name' => __('Global campaign', 'partners-site_v2'),
				'add_new'       => __('Add a note to a global campaign', 'partners-site_v2'),
				'add_new_item'  => __('Add note', 'partners-site_v2'),
				'edit_item'     => __('Edit global campaign note', 'partners-site_v2'),
				'new_item'      => __('New note for a global campaign', 'partners-site_v2'),
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
			'label'        => __('Models', 'partners-site_v2'),
			'labels'       => array(
				'singular_name' => __('Model', 'partners-site_v2'),
				'add_new'       => __('Add model', 'partners-site_v2'),
				'add_new_item'  => __('Add new', 'partners-site_v2'),
				'edit_item'     => __('Edit model', 'partners-site_v2'),
				'new_item'      => __('New model', 'partners-site_v2'),
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
			'label'        => __('Models', 'partners-site_v2'),
			'labels'       => array(
				'singular_name' => __('Model', 'partners-site_v2'),
				'add_new'       => __('Add model', 'partners-site_v2'),
				'add_new_item'  => __('Add new', 'partners-site_v2'),
				'edit_item'     => __('Edit model', 'partners-site_v2'),
				'new_item'      => __('New model', 'partners-site_v2'),
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
			'label'        => __('Employees', 'partners-site_v2'),
			'labels'       => array(
				'singular_name' => __('Employee', 'partners-site_v2'),
				'add_new'       => __('Add employee', 'partners-site_v2'),
				'add_new_item'  => __('Add new employee', 'partners-site_v2'),
				'edit_item'     => __('Edit employee', 'partners-site_v2'),
				'new_item'      => __('New employee', 'partners-site_v2'),
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
			'label'               => __('Showrooms', 'partners-site_v2'),
			'labels'              => array(
				'singular_name' => __('Showroom', 'partners-site_v2'),
				'add_new'       => __('Add Showroom', 'partners-site_v2'),
				'add_new_item'  => __('Add new Showroom', 'partners-site_v2'),
				'edit_item'     => __('Edit Showroom', 'partners-site_v2'),
				'new_item'      => __('New Showroom', 'partners-site_v2'),
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
			'label'        => __('Blog', 'partners-site_v2'),
			'labels'       => array(
				'singular_name' => __('Post', 'partners-site_v2'),
				'add_new'       => __('Add post', 'partners-site_v2'),
				'add_new_item'  => __('Add post', 'partners-site_v2'),
				'edit_item'     => __('Edit post', 'partners-site_v2'),
				'new_item'      => __('New post', 'partners-site_v2'),
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
					'<h3>' . esc_html__('Welcome to the Global Campaigns screen!', 'partners-site_v2') . '</h3>' .
					'<p>' . esc_html__('Here, you can add your own content to existing global campaigns displayed on your website.', 'partners-site_v2') . '</p>' .
					'<p>' . esc_html__('Create a page below and add content that will appear under the campaign you select.', 'partners-site_v2') . '</p>' .
					'<p>' . esc_html__('Don’t forget to select a new campaign in <a href="/wp/wp-admin/admin.php?page=options-homepage">Home Page Options</a>', 'partners-site_v2') . '</a></p>' .
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
