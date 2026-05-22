<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_605cafb0e79c3',
			'title'                 => __( 'Gallery', 'partners-site_v2' ),
			'fields'                => array(
				array(
					'key'               => 'field_605cafba13a7f',
					'label'             => __( 'Gallery', 'partners-site_v2' ),
					'name'              => 'gallery',
					'type'              => 'gallery',
					'instructions'		=> __( '(max. 3 photos)<br>When opened, the gallery displays the photos in their original size, with the option to zoom in.', 'partners-site_v2' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'return_format'     => 'id',
					'preview_size'      => 'medium',
					'insert'            => 'append',
					'library'           => 'all',
					'min'               => '',
					'max'               => '3',
					'min_width'         => '',
					'min_height'        => '',
					'min_size'          => '',
					'max_width'         => '',
					'max_height'        => '',
					'max_size'          => '',
					'mime_types'        => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/gallery',
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
