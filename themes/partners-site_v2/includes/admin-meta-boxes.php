<?php

use Classes\CategoryRadioChecklistWalker;

add_filter(
	'wp_terms_checklist_args',
	function ( $args ) {
		if ( ! empty( $args['taxonomy'] ) && $args['taxonomy'] === 'model_category' ) {
			if ( empty( $args['walker'] ) || is_a( $args['walker'], 'Walker' ) ) {
				$args['walker'] = new CategoryRadioChecklistWalker();
			}
		}
		return $args;
	}
);
