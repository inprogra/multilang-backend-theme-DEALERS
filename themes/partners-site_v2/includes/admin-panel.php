<?php

use Classes\Cache;

add_action( 'admin_enqueue_scripts', 'loadAdminAssets' );
function loadAdminAssets() {
	wp_enqueue_style( 'custom_admin_stylesheet', Cache::getAsset( 'admin.css' ), false, '1.0.0' );
	wp_enqueue_script( 'custom_admin_script', Cache::getAsset( 'admin.js' ), false, '1.0.0' );
}

add_action( 'admin_menu', 'removeFromAdminMenu', 999 );
function removeFromAdminMenu(): void {
	remove_submenu_page( 'tools.php', 'ms-delete-site.php' );
}

add_action( 'current_screen', 'limitPermissionsForDeleteSitePage', 999 );
function limitPermissionsForDeleteSitePage( $currentScreen ) {
	if ( $currentScreen->base === 'ms-delete-site' ) {
		echo 'Brak uprawnień';
		/** @noinspection ForgottenDebugOutputInspection */
		wp_die();
	}
}
