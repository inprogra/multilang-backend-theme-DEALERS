<?php

add_action(
	'admin_init',
	function () {
		$currentOptions = get_option( 'scporder_options' );
		$options        = array(
			'objects'            => array( 'stock-car', 'campaign', 'model', 'employee', 'showroom' ),
			'tags'               => array( 'model_category', 'employee_category' ),
			'show_advanced_view' => '',
		);

		if ( $currentOptions !== $options ) {
			update_option( 'scporder_options', $options );
		}
	},
	9
);
