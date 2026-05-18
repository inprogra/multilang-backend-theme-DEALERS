<?php

use Classes\CarDictionary;

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'group_cost_generator',
			'title'                 => 'Kalkulator kosztów',
			'fields'                => array(
				array(
					'key'               => 'cost_calculator',
					'label'             => 'Nagłówek',
					'name'              => 'cost_calculator',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => 'Kalkulator kosztów',
					'disabled'          => true,
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',

				),
				array(
					'key'               => 'cost-map-model',
					'label'             => 'Model',
					'name'              => 'cost-map-model',
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
					'key' => 'cost-map-version',
					'label' => 'Wersja wyposażenia',
					'name' => 'cost-map-version',
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
						'value'    => 'acf/cost-map',
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
