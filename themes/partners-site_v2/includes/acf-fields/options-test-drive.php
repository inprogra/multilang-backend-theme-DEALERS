<?php

use Classes\CarDictionary;

if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
		array(
			'key'                   => 'group_60d5ce6f2e2a6',
			'title'                 => __('Test drive options', 'partners-site_v2'),
			'fields'                => array(
				array(
					'key'               => 'field_60d5ce87a76ee',
					'label'             => __('Model groups', 'partners-site_v2'),
					'name'              => 'models_groups',
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
					'min'               => 0,
					'max'               => 0,
					'layout'            => 'row',
					'button_label'      => '',
					'sub_fields'        => array(
						array(
							'key'               => 'field_60d5ceaaa76ef',
							'label'             => __('Models', 'partners-site_v2'),
							'name'              => 'models',
							'type'              => 'repeater',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'collapsed'         => 'field_60d5cedba76f0',
							'min'               => 0,
							'max'               => 0,
							'layout'            => 'table',
							'button_label'      => '',
							'sub_fields'        => array(
								array(
									'key'               => 'field_602b96dc02487',
									'label'             => __( 'Model', 'partners-site_v2' ),
									'name'              => 'model',
									'type'              => 'select',
									'instructions'      => '',
									'required'          => 1,
									'conditional_logic' => 0,
									'wrapper'           => array(
										'width' => '',
										'class' => '',
										'id'    => '',
									),
									'choices'           => CarDictionary::getModels(),
									'default_value'     => false,
									'allow_null'        => 0,
									'multiple'          => 0,
									'ui'                => 0,
									'return_format'     => 'value',
									'ajax'              => 0,
									'placeholder'       => '',
								),
								array(
									'key'               => 'field_60d5cf35a76f1',
									'label' => __( 'Photo', 'partners-site_v2' ),
									'name'              => 'image',
									'type'              => 'image',
									'instructions'      => '',
									'required'          => 1,
									'conditional_logic' => 0,
									'wrapper'           => array(
										'width' => '',
										'class' => '',
										'id'    => '',
									),
									'return_format'     => 'id',
									'preview_size'      => 'thumbnail',
									'library'           => 'all',
									'min_width'         => '',
									'min_height'        => '',
									'min_size'          => '',
									'max_width'         => '',
									'max_height'        => '',
									'max_size'          => '',
									'mime_types'        => '',
								),
							),
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'options-test-drive',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'autoload' => true,
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
	);

endif;
