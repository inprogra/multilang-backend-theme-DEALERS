<?php

namespace Classes;

class Employee {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'removeFromAdminMenu' ), 999 );
		add_action( 'current_screen', array( $this, 'limitPermissionsForPostType' ), 999 );
	}

	public function removeFromAdminMenu(): void {
		if ( is_main_site() ) {
			remove_submenu_page( 'edit.php?post_type=employee', 'edit.php?post_type=employee' );
			remove_submenu_page( 'edit.php?post_type=employee', 'post-new.php?post_type=employee' );
		} else {
			remove_submenu_page( 'edit.php?post_type=employee', 'edit-tags.php?taxonomy=employee_category&amp;post_type=employee' );
		}
	}

	public function limitPermissionsForPostType( $current_screen ): void {
		if (
			( is_main_site() && $this->isEmployeePostType( $current_screen ) ) ||
			( ! is_main_site() && $this->isEmployeeCategory( $current_screen ) )
		) {
			echo 'Brak uprawnień';
			/** @noinspection ForgottenDebugOutputInspection */
			wp_die();
		}
	}

	private function isEmployeePostType( $current_screen ): bool {
		return ( $current_screen->base === 'post' || $current_screen->base === 'edit' ) && $current_screen->post_type === 'employee';
	}

	private function isEmployeeCategory( $current_screen ): bool {
		return ( $current_screen->base === 'edit-tags' && $current_screen->post_type === 'employee' ) || ( $current_screen->base === 'term' && $current_screen->taxonomy === 'employee_category' );
	}
}
