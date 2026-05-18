<?php

namespace Classes;

class GlobalOptions {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'removeFromAdminMenu' ), 100 );
	}

	public function removeFromAdminMenu(): void {
		if ( ! is_main_site() ) {
			remove_menu_page( 'options-global' );
		}
	}
}
