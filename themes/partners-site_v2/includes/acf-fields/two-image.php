<?php
if ( function_exists( 'acf_add_local_field_group' ) ) : 

	acf_add_local_field_group(
		array(
			'key'                   => 'twoImage',
			'title'                 => __('Two photos', 'partners-site_v2'),
			'fields'                => array(
                array(
                    'key'               => 'firstPicture',
                    'label'             => sprintf(__('Photo %s', 'partners-site_v2'), 1),
                    'name'              => 'firstPicture',
                    'type'              => 'image',
                    'instructions'      => '',
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
                    'min_width'         => 100,
                    'min_height'        => 100,
                    'min_size'          => '',
                    'max_width'         => '',
                    'max_height'        => '',
                    'max_size'          => '',
                    'mime_types'        => '',
                ),
                array(
                    'key'               => 'secondPicture',
                    'label'             => sprintf(__('Photo %s', 'partners-site_v2'), 2),
                    'name'              => 'secondPicture',
                    'type'              => 'image',
                    'instructions'      => '',
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
                    'min_width'         => 100,
                    'min_height'        => 100,
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
						'value'    => 'acf/two-image',
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
