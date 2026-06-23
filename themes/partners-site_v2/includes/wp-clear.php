<?php

add_action( 'wp_enqueue_scripts', 'removeBlockStyles' );
function removeBlockStyles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
}

add_filter( 'intermediate_image_sizes_advanced', 'remove_default_image_sizes' );
function remove_default_image_sizes( $sizes ) {
	unset( $sizes['thumbnail'] );
	unset( $sizes['medium_large'] );
	unset( $sizes['large'] );

	return $sizes;
}

add_action( 'wp_dashboard_setup', 'removeDashboardMetaboxes' );
function removeDashboardMetaboxes() {
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'health_check_status', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
}

add_filter( 'use_block_editor_for_post', 'disableGutenbergHomepage', 10, 2 );
function disableGutenbergHomepage( $isEnabled, $post ): bool {
	if ( $post->post_type === 'page' && get_page_template_slug( $post->ID ) === 'wp-templates/template-homepage.php' ) {
		$isEnabled = false;
	}

	return $isEnabled;
}

add_action( 'admin_head', 'disableEditorHomepage' );
function disableEditorHomepage() {

	$screen = get_current_screen();
	if ( 'page' !== $screen->id || ! isset( $_GET['post'] ) ) {
		return;
	}

	if ( get_page_template_slug( $_GET['post'] ) === 'wp-templates/template-homepage.php' ) {
		remove_post_type_support( 'page', 'editor' );
	}
}
