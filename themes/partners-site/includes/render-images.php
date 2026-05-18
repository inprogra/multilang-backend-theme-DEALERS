<?php

add_action( 'save_post', 'renderPostsImages' );
function renderPostsImages( $post_id ) {
	$url = \Classes\MultisiteFixer::buildUrl( get_the_permalink( $post_id ), true );
	wp_remote_get( $url );
}

add_action( 'acf/save_post', 'renderHomepageImages', 20 );
function renderHomepageImages() {
	$screen = get_current_screen();
	if ( strpos( $screen->id, 'options-homepage' ) == true ) {
		$url = \Classes\MultisiteFixer::getHomeUrl( true );
		wp_remote_get( $url );
	}
}
