<?php

namespace Classes;

class Model {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'removeFromAdminMenu' ), 100 );
		add_filter( 'use_block_editor_for_post', array( $this, 'disableBlockEditor' ), 10, 2 );
		add_action( 'current_screen', array( $this, 'limitPermissionsForPostType' ), 999 );
		add_action( 'template_redirect', array( $this, 'redirect' ), 1 );
		add_filter( 'post_type_link', array( $this, 'adjustPermalinks' ), 1, 2 );
		add_action( 'model_category_edit_form', array( $this, 'hideDescriptionForCategory' ) );
		add_action( 'model_category_add_form', array( $this, 'hideDescriptionForCategory' ) );
		add_action( 'wp_before_admin_bar_render', array( $this, 'beforeAdminBarRender' ) );
		add_action( 'admin_head', array( $this, 'removePostLinkBlockEditorPanel' ) );
	}

	public function hideDescriptionForCategory() {
		echo '<style> .term-description-wrap { display:none; } </style>';
	}

	public function removeFromAdminMenu(): void {
		if ( is_main_site() ) {
			remove_menu_page( 'edit.php?post_type=model-override' );
		} else {
			remove_menu_page( 'edit.php?post_type=model' );
			remove_menu_page( 'options-models' );
		}
	}

	public function disableBlockEditor( $usBlockEditor, $post ): bool {
		if ( $post->post_type === 'model' && $post->post_parent == 0 ) {
			return false;
		}

		return $usBlockEditor;
	}

	public function limitPermissionsForPostType( $current_screen ): void {
		if (
			( is_main_site() && $this->isModelOverridePostType( $current_screen ) ) ||
			( ! is_main_site() && ( $this->isModelPostType( $current_screen ) || $this->isModelCategory( $current_screen ) ) )
		) {
			echo 'Brak uprawnień';
			/** @noinspection ForgottenDebugOutputInspection */
			wp_die();
		}
	}

	public function redirect(): void {
		$post = get_queried_object();

		if ( $post && $post->post_type === 'model' && is_single() && $post->post_parent !== 0 ) {
			// TODO: Add 301?
			wp_redirect( get_permalink( $post->post_parent ) );
		}
	}

	public function adjustPermalinks( $post_link, $post ) {
		if ( is_object( $post ) && $post->post_type === 'model' ) {
			if ( $post->post_parent !== 0 ) {
				$post = get_post( $post->post_parent );
			}
			$terms = wp_get_object_terms( $post->ID, 'model_category' );
			if ( $terms ) {
				return str_replace( '%model_category%', $terms[0]->slug, $post_link );
			}
		}
		return $post_link;
	}

	public function beforeAdminBarRender() {
		global $wp_admin_bar;

		if ( is_main_site() ) {
			$wp_admin_bar->remove_node( 'new-model-override' );
		} else {
			$wp_admin_bar->remove_node( 'new-model' );
		}
	}

	public function removePostLinkBlockEditorPanel() {
		global $current_screen;

		if ( $this->isModelOverridePostType( $current_screen ) ) {
			echo "
            <script>
            document.addEventListener('DOMContentLoaded', () => {
                wp.domReady( () => {
                    const { removeEditorPanel } = wp.data.dispatch('core/edit-post');
                    removeEditorPanel( 'post-link' );
                });
            });
            </script>";
		}
	}

	private function isModelPostType( $current_screen ): bool {
		return ( $current_screen->base === 'post' || $current_screen->base === 'edit' ) && $current_screen->post_type === 'model';
	}

	private function isModelCategory( $current_screen ): bool {
		return ( $current_screen->base === 'edit-tags' && $current_screen->post_type === 'model' ) || ( $current_screen->base === 'term' && $current_screen->taxonomy === 'model_category' );
	}

	private function isModelOverridePostType( $current_screen ): bool {
		return ( $current_screen->base === 'post' || $current_screen->base === 'edit' ) && $current_screen->post_type === 'model-override';
	}
}
