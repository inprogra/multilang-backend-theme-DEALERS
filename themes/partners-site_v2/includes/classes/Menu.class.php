<?php

namespace Classes;

class Menu {

	private $menu;

	public function __construct( $slug ) {
		$locations = get_nav_menu_locations();

		$this->menu = wp_get_nav_menu_object( $locations[ $slug ] );
	}

	public function getItems(): ?array {
		if ( ! $this->menu ) {
			return null;
		}

		$menuItems = wp_get_nav_menu_items( $this->menu->term_id, array( 'update_post_term_cache' => false ) );

		$items = array();

		foreach ( $menuItems as $item ) {
			$menuLink = MultisiteFixer::buildLink(
				array(
					'title'  => $item->title,
					'url'    => $item->url,
					'target' => $item->target,
				)
			);

			$items[] = array(
				'id'               => $item->ID,
				'title'            => $menuLink['text'],
				'url'              => $menuLink['url'],
				'target'           => $menuLink['target'] ?? false,
				'nofollow'         => $menuLink['nofollow'] ?? false,
				'menu_item_parent' => $item->menu_item_parent,
			);
		}

		return $items;
	}
}
