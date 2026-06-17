<?php

namespace Controllers;

use Classes\Cache;
use Classes\Controller;

class TableController extends Controller {
	public function getTableData() {
		$table_data = get_field('table_preview');
		
		return $table_data;
		
	}
	public function render(): string {
		
		$backendPreview = get_field( 'backendPreview' );
		if ( $backendPreview ) {
			$img = Cache::getAsset( 'tableEditor.png' );
			return '<img src="' . $img . '" >';
		}
		
		$table_data = $this->getTableData();
		
		if ($table_data) {
			$content = $table_data;
			
		}
		
		// var_dump(get_fields());
		$color = get_field('table_color');
		$blocked_column = get_field('static_header');
		$header_size = ($table_data['header'] ? count($table_data['header']) : null);
		
		return $this->blockView(
			'components/organisms/table-editor/table-editor',
			array(
				'color' => $color,
				'blocked_column' => $blocked_column,
				'content' => $content,
				'size'    => $header_size,
			)
		);
	}
}
