<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;

class AnchorController extends Controller {

	public function render(): string {

		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'tableEditor.png' );
			return '';
		}
		$anchor = get_field( 'anchor' );

		return $this->blockView(
			'components/organisms/anchor/anchor',
			array(
				'anchor' => $anchor,
			)
		);
	}
}
// $mappedValues = array();

// foreach ($values as $key => $value) {
// $mappedValues[] = array(
// 'name' => $key,
// 'value' => $key,
// 'label' => $value
// );
// }
