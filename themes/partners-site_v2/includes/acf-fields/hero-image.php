<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_605c7d6136c23',
			'title'                 => __( 'Banner image', 'partners-site_v2' ),
			'fields'                => array(
				array(
					'key'               => 'field_605c7d71b7cae',
					'label' 			=> __( 'Photo', 'partners-site_v2' ),
					'name'              => 'img',
					'type'              => 'image',
					'instructions' 		=> __( '(min. width: 1224px, Recommended width: 3672px)<br>(min. height: 574px, Recommended height: 1344px)', 'partners-site_v2' ),
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
					'label' 			=> __( 'When cropping the image, keep', 'partners-site_v2' ),
					'name'              => 'field_crop',
					'type'              => 'radio',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'choices'           => array(
						'crop-top' => __( 'Top', 'partners-site_v2' ),
						'crop-center' => __( 'Center', 'partners-site_v2' ),
						'crop-left' => __( 'Left side', 'partners-site_v2' ),
						'crop-right' => __( 'Right side', 'partners-site_v2' ),
						'crop-bottom' => __( 'Bottom', 'partners-site_v2' ),

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
					'label' 			=> __( 'Larger description', 'partners-site_v2' ),
					'name'              => 'bigDescription',
					'type'              => 'text',
					'instructions' 		=> __( 'Enter the main banner headline', 'partners-site_v2' ),
					'required'          => 0,
					'wrapper' => array(
						'width' => '100',
						'class' => '',
						'id'    => '',
					),
				),
				array(
					'key'               => 'field_opis_mniejszy',
					'label'             => __( 'Shorter description', 'partners-site_v2' ),
					'name'              => 'smallDescription',
					'type'              => 'text',
					'instructions' 		=> __( 'Enter a subtitle or additional banner description', 'partners-site_v2' ),
					'required'          => 0,
					'wrapper' => array(
						'width' => '100',
						'class' => '',
						'id'    => '',
					)
				),
				array( 
					'key'               => 'field_dark_overlay',
					'label' 			=> __( 'Lightly darken the background?', 'partners-site_v2' ),
					'name'              => 'darkOverlay',
					'type'              => 'true_false',
					'instructions' 		=> __( 'Check this box if you want to add a slight background darkening', 'partners-site_v2' ),
					'message' 			=> __( 'Yes, darken the background', 'partners-site_v2' ),
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
