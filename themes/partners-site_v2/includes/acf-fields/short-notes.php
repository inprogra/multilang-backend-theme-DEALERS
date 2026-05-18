<?php


if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_shortnotes',
			'title'                 => 'shortnotes',
			'fields'                => array(
				array(
					'key'               => 'field_shortnotes',
					'label' => __( 'Krótkie notatki', 'partners-site_v2' ),
					'name'              => 'items',
					'type'              => 'repeater',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'collapsed'         => '',
					'min'               => 2,
					'max'               => 4,
					'layout'            => 'block',
					'button_label'      => '',
					'sub_fields'        => array(
						array(
							'key'               => 'field_notetitle',
							'label' => __( 'Tytuł', 'partners-site_v2' ),
							'name'              => 'field_notetitle',
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
							'maxlength'         => '200',
							'rows'              => '',
							'new_lines'         => '',
						),
						array(
							'key'               => 'field_notesdesc',
							'label'             => 'Opis',
							'name'              => 'field_notesdesc',
							'type'              => 'textarea',
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
							'maxlength'         => '200',
							'rows'              => '',
							'new_lines'         => '',
						),

					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/short-notes',
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
