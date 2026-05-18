<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\MultisiteFixer;

class QuickInfoController extends Controller {

	public function render() {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'offerBoxes.png' );
			return '<img src="' . $img . '" >';
		}

		$items = array();

		// $items = get_field('field_shortnotes');

		$items = get_field( 'items' );

		// //var_dump(get_field('group_quickinfo'));
		// exit();
		// //var_dump($title);

		// //var_dump($items);
		// die('test');

		return $this->blockView(
			'components/organisms/quick-info/quick-info',
			array(
				'items' => $items,
			)
		);
	}
}
