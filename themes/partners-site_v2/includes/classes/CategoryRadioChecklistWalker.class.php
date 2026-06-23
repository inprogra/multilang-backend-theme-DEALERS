<?php
namespace Classes;

use Walker_Category_Checklist;

class CategoryRadioChecklistWalker extends Walker_Category_Checklist {

	function walk( $elements, $max_depth, ...$args ) {
		$output = parent::walk( $elements, $max_depth, ...$args );
		$output = str_replace(
			array( 'type="checkbox"', "type='checkbox'" ),
			array( 'type="radio"', "type='radio'" ),
			$output
		);
		return $output;
	}
}
