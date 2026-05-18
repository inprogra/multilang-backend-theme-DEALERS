<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_605c7d6136c23',
			'title'                 => 'Zdjęcie banner',
			'fields'                => array(
				array(
					'key'               => 'field_605c7d71b7cae',
					'label' => __( 'Zdjęcie', 'partners-site_v2' ),
					'name'              => 'img',
					'type'              => 'image',
					'instructions' => __( '(min. szerokość: 1224px, Zalecana szerokość: 3672px)<br>(min. wysokość: 574px, Zalecana wysokość: 1344px)', 'partners-site_v2' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'library'           => 'all',
					'min_width'         => '',
					'min_height'        => '',
					'min_size'          => '',
					'max_width'         => '',
					'max_height'        => '',
					'max_size'          => '',
					'mime_types'        => '',
				),
				array(
					'key'               => 'field_crop',
					'label' => __( 'Przycinając zdjęcie zachowaj', 'partners-site_v2' ),
					'name'              => 'field_crop',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'choices'           => array(
						'crop-top' => 'Góra',
						'crop-center' => 'Środek',
						'crop-left' => 'Lewą stronę',
						'crop-right' => 'Prawą stronę',
						'crop-bottom' => 'Dół',

					), 
					'allow_null'        => 'true',
					'multiple'          => false,
					'wrapper'           => array(
						'width' => '50',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => 'standard',
					'placeholder'       => '',
				),
				array(
					'key'               => 'field_opis_wiekszy',
					'label' => __( 'Opis większy', 'partners-site_v2' ),
					'name'              => 'bigDescription',
					'type'              => 'text',
					'instructions' => __( 'Wpisz główny nagłówek bannera', 'partners-site_v2' ),
					'required'          => 0,
					'wrapper' => array(
						'width' => '100',
						'class' => '',
						'id'    => '',
					),
				),
				array(
					'key'               => 'field_opis_mniejszy',
					'label'             => 'Opis mniejszy',
					'name'              => 'smallDescription',
					'type'              => 'text',
					'instructions' => __( 'Wpisz podtytuł lub dodatkowy opis bannera', 'partners-site_v2' ),
					'required'          => 0,
					'wrapper' => array(
						'width' => '100',
						'class' => '',
						'id'    => '',
					)
				),
				array( 
					'key'               => 'field_dark_overlay',
					'label' => __( 'Delikatnie przyciemnić tło?', 'partners-site_v2' ),
					'name'              => 'darkOverlay',
					'type'              => 'true_false',
					'instructions' => __( 'Zaznacz, jeśli chcesz dodać delikatne przyciemnienie tła', 'partners-site_v2' ),
					'message' => __( 'Tak, przyciemnij tło', 'partners-site_v2' ),
					'default_value'     => 0,
					'ui'                => 0,
					'wrapper' => array(
						'width' => '100',
						'class' => '',
						'id'    => '',
					)
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/hero-image',
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
