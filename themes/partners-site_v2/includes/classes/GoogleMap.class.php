<?php

namespace Classes;

class GoogleMap {

	public function __construct() {
		add_action( 'acf/init', array( $this, 'registerBackendKey' ) );
		add_filter( 'acf/fields/google_map/api', array( $this, 'changeAPIKey' ) );
	}

	public function changeAPIKey(): array {
		switch_to_blog( \Classes\MultisiteFixer::getCurrentBlogId() );
		$apiKey = get_field( 'google-maps-key', 'options-dealer' );
		restore_current_blog();

		$api['key'] = $apiKey;

		return $api;
	}

	public function registerBackendKey(): void {
		switch_to_blog( \Classes\MultisiteFixer::getCurrentBlogId() );
		$apiKey = get_field( 'google-maps-key', 'options-dealer' );
		acf_update_setting( 'google_api_key', $apiKey );
		restore_current_blog();
	}
}
