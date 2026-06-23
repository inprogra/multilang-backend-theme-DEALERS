<?php

add_filter( 'tiny_mce_before_init', 'modifyTinyMceFormatSelect' );
function modifyTinyMceFormatSelect( $init ) {
	$init['block_formats'] = 'Paragraph=p; Nagłówek-h2=h2; Nagłówek-h3=h3; Nagłówek-h4=h4; Nagłówek-h5=h5;';
	return $init;
}

add_filter( 'acf/fields/wysiwyg/toolbars', 'customTinyMceToolbars' );
function customTinyMceToolbars( $toolbars ) {
	$toolbars['blog']    = array();
	$toolbars['blog'][1] = array(
		'formatselect',
		'bold',
		'link',
		'unlink',
		'anchor',
		'charmap',
		'alignleft',
		'aligncenter',
		'alignright',
		'alignjustify',
		'undo',
		'redo',
		'removeformat',
	);

	// Has to be in lowercase
	$toolbars['blogextended']    = array();
	$toolbars['blogextended'][1] = array(
		'formatselect',
		'bold',
		'link',
		'unlink',
		'anchor',
		'charmap',
		'alignleft',
		'aligncenter',
		'alignright',
		'alignjustify',
		'undo',
		'redo',
		'removeformat',
		'bullist',
	);

	return $toolbars;
}

add_filter( 'wp_link_query', 'addGlobalLinksToLinkQuery', 999, 2 );
function addGlobalLinksToLinkQuery( $results, $query ) {
	if ( ! is_main_site() ) {
		switch_to_blog( 1 );
		$get_posts = new WP_Query();
		$posts     = $get_posts->query( $query );

		foreach ( $posts as $post ) {
			if ( 'post' === $post->post_type ) {
				$info = mysql2date( __( 'Y/m/d' ), $post->post_date );
			} else {
				$postType = get_post_type_object( $post->post_type );
				$info     = $postType->labels->singular_name . ' (Globalny)';
			}

			$results[] = array(
				'ID'        => $post->ID,
				'title'     => trim( esc_html( strip_tags( get_the_title( $post ) ) ) ),
				'permalink' => \Classes\MultisiteFixer::buildUrl( get_permalink( $post->ID ) ),
				'info'      => $info,
			);
		}
		restore_current_blog();
	}

	return $results;
}
