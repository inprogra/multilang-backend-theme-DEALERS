<?php

use Classes\CarDictionary;

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'html_code',
			'title'                 => __( 'HTML code', 'partners-site_v2' ),
			'fields'                => array(
				array(
					'key'               => 'html_code_render',
					'label'             => __( 'HTML code', 'partners-site_v2' ),
					'name'              => 'html_code_render',
					'type'              => 'textarea',
					'instructions' => __( 'All tags except for the script tag will be removed', 'partners-site_v2' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => null,
					'disabled'          => false,
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',

				),
				
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/html-code',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => 'Kalkulator kosztów',
			'show_in_rest'          => 0,
		)
	);

endif;
