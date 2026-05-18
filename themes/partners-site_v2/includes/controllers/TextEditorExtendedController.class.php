<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;

class TextEditorExtendedController extends Controller {

	public function render(): string {

		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'textEditorExtended.png' );
			return '<img src="' . $img . '" >';
		}

		$content = get_field( 'content' );

		return $this->blockView(
			'components/organisms/text-editor-extended/text-editor-extended',
			array(
				'content' => $content,
			)
		);
	}
}
