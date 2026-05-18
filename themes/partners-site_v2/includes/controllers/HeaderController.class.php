<?php

namespace Controllers;

use Classes\Controller;
use Classes\Menu;
use Classes\MultisiteFixer;

class HeaderController extends Controller {

	public function render() {
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$partnerName = get_field( 'name', 'options-dealer' );
		restore_current_blog();
		return $this->view(
			'components/organisms/header/header',
			array(
				'logo'             => array(
					'url'         => MultisiteFixer::getHomeUrl(),
					'svg'         => getSVG( 'volvo-logo' ),
					'partnerName' => $partnerName,
				),
				'socialMedia'      => $this->getSocialMedia(),
				'menuItems'        => $this->getMenuItems( 'header' ),
				'hasSideNav'       => true,
				'sideNavMenuItems' => $this->getMenuItems( 'side-nav' ),
			)
		);
	}

	private function getMenuItems( $slug ): ?array {
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$menu      = new Menu( $slug );
		$menuItems = $menu->getItems();
		restore_current_blog();

		if ( ! $menuItems ) {
			switch_to_blog( 1 );
			$menu      = new Menu( $slug );
			$menuItems = $menu->getItems();
			restore_current_blog();
		}

		return $menuItems;
	}

	private function getSocialMedia(): array {
		switch_to_blog( MultisiteFixer::getCurrentBlogId() );
		$socialMedia = get_field( 'social-media', 'options-dealer' );

		restore_current_blog();

		return $socialMedia ? array_filter( $socialMedia ) : array();
	}
}
