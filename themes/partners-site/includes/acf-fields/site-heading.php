<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_6034db6763083',
			'title'                 => 'SiteHeading',
			'fields'                => array(
				array(
					'key'               => 'field_6034db833e95b',
					'label'             => 'Nagłówek',
					'name'              => 'heading',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '70',
				),
				array(
					'key' => 'field_669a1d0b0f227',
					'label' => 'Typ Nagłówka',
					'name' => 'header_type',
					'aria-label' => '',
					'type' => 'select',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'1' => 'H1',
						'2' => 'H2',
						'3' => 'H3',
						'4' => 'H4',
						'5' => 'H5',
					),
					'default_value' => 1,
					'return_format' => 'value',
					'multiple' => 0,
					'allow_null' => 0,
					'ui' => 0,
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key'               => 'field_6034db8c3e95c',
					'label'             => 'Opis',
					'name'              => 'description',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '50',
				),
				array(
					'key'               => 'field_quick_header',
					'label'             => 'Rodzaj nagłówka',
					'name'              => 'field_quick_header',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'choices'           => array(
						'tak' => 'Szary nagłówek',
						'blog' => 'Nagłówek blogowy',

					),
					'allow_null'        => true,
					'multiple'          => false,
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/site-heading',
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
		)
	);
endif;
