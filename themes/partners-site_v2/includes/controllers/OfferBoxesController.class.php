<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\MultisiteFixer;

class OfferBoxesController extends Controller {

	public function render() {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'offerBoxes.png' );
			return '<img src="' . $img . '" >';
		}

		$layout = get_field( 'layout' );

		$items = array();

		$offerBoxesItems = get_field( 'items' );
		$pullUp          = get_field( 'pull_up' );
		$iconView          = get_field( 'iconView' );

		if (is_array($offerBoxesItems) && array_filter( $offerBoxesItems ) ) {
			foreach ( $offerBoxesItems as $box ) {
				$hasButton = ! empty( $box['link'] );
				$rewrite = false;
				
				if ($hasButton && strpos($box['link']['url'],'#') !== false) {
					$rewrite = true;
					$rep = explode('#',$box['link']['url']);
					
					$box['link']['url'] = '/dostepne-na-miejscu/#'.$rep[1];
					
				}
				$link = $hasButton ? MultisiteFixer::buildLink( $box['link'] ) : null;
				if ($rewrite) {
					$box['link']['text'] = $box['link']['title'];
					$link = $box['link'];
				}
				$items[] = array(
					'icon'        => $box['icon'],
					'heading'     => $box['heading'],
					'description' => $box['description'],
					'hasButton'   => $hasButton,
					'link'        => $link,
				);
			}
		}

		return $this->blockView(
			'components/organisms/offer-boxes/offer-boxes',
			array(
				'layout' => $layout,
				'pullUp' => $pullUp,
				'items'  => $items,
				'iconView'  => $iconView,
			)
		);
	}
}
