<?php

namespace Classes;

class Campaign {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'removeFromAdminMenu' ), 100 );
		add_action('admin_enqueue_scripts', [$this, 'add_updated_slug_notice']);
		add_action( 'current_screen', array( $this, 'limitPermissionsForPostType' ), 999 );
		add_action( 'template_redirect', array( $this, 'redirect' ), 1 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'beforeAdminBarRender' ) );
		add_action( 'admin_head', array( $this, 'removePostLinkBlockEditorPanel' ) );
		add_action('wp_insert_post_data', [$this, 'post_insert_data'], 20);
	}

	public function removeFromAdminMenu(): void {
		if ( is_main_site() ) {
			remove_menu_page( 'edit.php?post_type=campaign-override' );
		}
	}

	public function limitPermissionsForPostType( $current_screen ): void {
		if ( is_main_site() && $this->isCampaignOverridePostType( $current_screen ) ) {
			esc_html_e('No permissions', 'partners-site_v2');
			/** @noinspection ForgottenDebugOutputInspection */
			wp_die();
		}
	}

	public function redirect(): void {
		$post = get_queried_object();
		
		if ( $post && $post->post_type === 'campaign-override' && is_single() ) {
			$campaignId = get_field( 'campaign' );
			
			if ( $campaignId ) {
				switch_to_blog( 1 );
				$campaignUrl = get_permalink( $campaignId );
				
				restore_current_blog();
				wp_redirect( MultisiteFixer::buildUrl( $campaignUrl ) );
			}
		} 
	} 

	public function beforeAdminBarRender() {
		global $wp_admin_bar;

		if ( is_main_site() ) {
			$wp_admin_bar->remove_node( 'new-campaign-override' );
		}
	}

	public function removePostLinkBlockEditorPanel() {
		global $current_screen;

		if ( $this->isCampaignOverridePostType( $current_screen ) ) {
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

	public function post_insert_data($data)
    {
        

		return $data;
	}

	public function add_updated_slug_notice($hook)
	{
		if ( $hook != 'post.php' && $hook !='post-new.php' ) {
			return;
		}

		wp_enqueue_script( 'tha_adminjobs', get_template_directory_uri() . '/includes/views/components/organisms/campaign/campaign.js', ['wp-api-fetch', 'jquery'], '1.0');
	}

	private function isCampaignOverridePostType( $current_screen ): bool {
		return ( $current_screen->base === 'post' || $current_screen->base === 'edit' ) && $current_screen->post_type === 'campaign-override';
	}
}
