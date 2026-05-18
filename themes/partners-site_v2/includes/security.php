<?php
remove_action( 'wp_head', 'wp_generator' );

add_filter(
	'the_generator',
	function () {
		return '';
	}
);

add_filter( 'script_loader_src', 'removeScriptsVersion' );
add_filter( 'style_loader_src', 'removeScriptsVersion' );
function removeScriptsVersion( $src ) {
	global $wp_version;

	$version_str = '?ver=' . $wp_version;
	$offset      = strlen( $src ) - strlen( $version_str );

	if ( $offset >= 0 && strpos( $src, $version_str, $offset ) !== false ) {
		return substr( $src, 0, $offset );
	}
	return $src;
}
