<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;
use Classes\MultisiteFixer;

class ShortNotesController extends Controller {

	public function render() {
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = '/img/shortNotes.png';
			return '<img src="' . $img . '" >';
		}

		$items = array();
		$items = get_field( 'field_shortnotes' );

		return $this->blockView(
			'components/organisms/short-notes/short-notes',
			array(
				'items' => $items,
			)
		);
	}
}
