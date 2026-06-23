<?php

add_filter( 'tiny_mce_before_init', 'changeTinyMceSettings' );
function changeTinyMceSettings( $init ) {
	$init['paste_as_text'] = true;
	return $init;
}
