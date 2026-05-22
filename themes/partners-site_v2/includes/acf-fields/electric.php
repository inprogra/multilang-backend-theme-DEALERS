<?php
use Classes\CarDictionary;
use Classes\Showroom;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
$showroom = new \Classes\Showroom();
$dictionary = new CarDictionary( new GuzzleHttp\Client() );

if ( function_exists( 'acf_add_local_field_group' ) ) {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_electric',
			'title'                 => __( 'Settings', 'partners-site_v2' ),
			'fields'                => array(
				array(
					'key'               => 'maps_disclaimer',
					'label'             => __( 'Information below the maps', 'partners-site_v2' ),
					'name'              => 'maps_disclaimer',
					'type'              => 'textarea',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => false,
					'allow_null'        => 0,
					'multiple'          => 0,
					'ui'                => 0,
					'return_format'     => 'value',
					'ajax'              => 0,
					'placeholder'       => '',
				),
				array(
					'key'               => 'field_electric_address',
					'label' 			=> __( 'Charger configuration', 'partners-site_v2' ),
					'name'              => 'chargers',
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
					'max'               => null,
					'layout'            => 'table',
					'button_label'      => '',
					'sub_fields'        => array(
						array(
							'key'               => 'super_charger',
							'label' 			=> __( 'Fast charger?', 'partners-site_v2' ),
							'name'              => 'super_charger',
							'type'              => 'checkbox',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'choices'           => array(
								'tak' => __( 'yes', 'partners-site_v2' ),
							),
							'allow_null'        => true,
							'multiple'          => false,
							'wrapper'           => array(
								'width' => '10',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => '',
							'placeholder'       => '',
						),
						array(
							'key'               => 'charger_address',
							'label' 			=> __( 'Charger address', 'partners-site_v2' ),
							'name'              => 'charger_address',
							'type'              => 'text',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => false,
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'charger_title',
							'label' 			=> __( 'Top description', 'partners-site_v2' ),
							'name'              => 'charger_title',
							'type'              => 'text',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => false,
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'charger_up',
							'label' 			=> __( 'Chargers', 'partners-site_v2' ),
							'name'              => 'charger_up',
							'type'              => 'wysiwyg',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => false,
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'charger_type',
							'label' 			=> __( 'Business card', 'partners-site_v2' ),
							'name'              => 'charger_type',
							'type'              => 'wysiwyg',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => false,
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						array(
							'key'               => 'charger_hours',
							'label'             => __( 'Address', 'partners-site_v2' ),
							'name'              => 'charger_hours',
							'type'              => 'wysiwyg',
							'instructions'      => '',
							'required'          => 0,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '',
								'class' => '',
								'id'    => '',
							),
							'default_value'     => false,
							'allow_null'        => 0,
							'multiple'          => 0,
							'ui'                => 0,
							'return_format'     => 'value',
							'ajax'              => 0,
							'placeholder'       => '',
						),
						
						// array(
						// 	'key'               => 'charger_address_place',
						// 	'label' => __( 'Rodzaje ładowarek ładowarki', 'partners-site_v2' ),
						// 	'name'              => 'charger_address_place',
						// 	'type' => 'select',
						// 	'instructions' => '',
						// 	'required' => 0,
						// 	'conditional_logic' => 0,
						// 	'wrapper' => array(
						// 		'width' => '50',
						// 		'class' => '',
						// 		'id' => '',
						// 	),
						// 	'choices' => $showroom->getShowroomsGlobal(),
						// 	'default_value' => false,
						// 	'allow_null' => 1,
						// 	'multiple' => 0,
						// 	'ui' => 0,
						// 	'return_format' => 'value',
						// 	'ajax' => 0,
						// 	'placeholder' => '',
						// 	'admin-only' => false
						// ),
						

					),
				),

			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'options-electric',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => 1,
			'description'           => '',
		)
	);




}
