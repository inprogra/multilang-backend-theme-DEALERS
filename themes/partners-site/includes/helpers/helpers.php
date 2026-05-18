<?php

function getComponent( $component, $args = array() ) {
	locate_template( '/includes/views/components/' . $component . '.php', true, false, $args );
}

function getSVG( $file ) {
	$path = \Classes\Cache::getAsset( $file . '.svg', true );

	if ( file_exists( $path ) ) {
		return file_get_contents( $path );
	}

	return null;
}

function uniqueID() {
	return uniqid( mt_rand() . '_' . time(), true );
}

function polishSuffixes( $single, $few, $many, $value ): string {
	$value = abs( $value );

	if ( $value === 1 ) {
		return $single;
	}

	if ( ( $value >= 2 && $value <= 4 ) || ( ( $value % 100 > 20 ) && ( $value % 10 >= 2 ) && ( $value % 10 <= 4 ) ) || ( ( $value >= 20 ) && ( $value % 10 >= 2 ) && ( $value % 10 <= 4 ) ) ) {
		return $few;
	}

	return $many;
}
