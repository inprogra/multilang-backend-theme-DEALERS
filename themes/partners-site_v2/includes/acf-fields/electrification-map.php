<?php

use Classes\CarDictionary;

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'group_map_generator',
			'title'                 => __( 'Coverage map', 'partners-site_v2' ),
			'fields'                => array(
				array(
					'key'               => 'google_map',
					'label' 			=> __( 'Coverage map', 'partners-site_v2' ),
					'name'              => 'google_map',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => __( 'Google Map', 'partners-site_v2' ),
					'disabled'          => true,
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
				),
				array(
					'key' => 'electrification-map-show',
					'label' => __( 'Hide map?', 'partners-site_v2' ),
					'name' => 'electrification-map-show',
					'type' => 'true_false',
					'instructions' => __( 'Only one instance of the map can be displayed on each page of the new model', 'partners-site_v2' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
				),
				array(
					'key'               => 'electrification-map-model',
					'label'             => __( 'Model', 'partners-site_v2' ),
					'name'              => 'electrification-map-model',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => CarDictionary::getModels('elektryczne'),
					'default_value'     => false,
					'allow_null'        => 1,
					'multiple'          => 0,
					'ui'                => 0,
					'return_format'     => 'value',
					'ajax'              => 0,
					'placeholder'       => '',
				),
				array(
					'key' => 'electrification-map-version',
					'label' => __( 'Equipment Version', 'partners-site_v2' ),
					'name' => 'electrification-map-version',
					'aria-label' => '',
					'type' => 'select',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => CarDictionary::getVersions(),
					'default_value' => false,
					'return_format' => 'value',
					'multiple' => 0,
					'allow_null' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/electrification-map',
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
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);

endif;
