<?php

add_filter( 'register_post_type_args', 'removePost', 0, 2 );
function removePost( $args, $postType ) {
	if ( $postType === 'post' ) {
		$args['public']              = false;
		$args['show_ui']             = false;
		$args['show_in_menu']        = false;
		$args['show_in_admin_bar']   = false;
		$args['show_in_nav_menus']   = false;
		$args['can_export']          = false;
		$args['has_archive']         = false;
		$args['exclude_from_search'] = true;
		$args['publicly_queryable']  = false;
		$args['show_in_rest']        = false;
	}

	return $args;
}



add_action( 'current_screen', 'removePostAdd', 999 );
function removePostAdd( $current_screen ): void {
	if ( $current_screen->base === 'post' && $current_screen->post_type === 'post' ) {
		echo 'Brak uprawnień';
		/** @noinspection ForgottenDebugOutputInspection */
		wp_die();
	}
}

add_action(
	'wp_before_admin_bar_render',
	function () {
		global $wp_admin_bar;
		$blogs = get_sites();

		foreach ( $blogs as $blog ) {
			$wp_admin_bar->remove_node( 'blog-' . $blog->blog_id . '-n' );
		}

		$wp_admin_bar->remove_node( 'new-post' );
	}
);
