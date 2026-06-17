<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;

class TextEditorController extends Controller {

	public function render(): string {

		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'textEditor.png' );
			return '<img src="' . $img . '" >';
		}

		$content = get_field( 'content' );

		return $this->blockView(
			'components/organisms/text-editor/text-editor',
			array(
				'content' => $content,
			)
		);
	}
}
