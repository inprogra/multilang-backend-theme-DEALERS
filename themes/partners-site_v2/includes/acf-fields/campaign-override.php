<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_605c9b6257833',
			'title'                 => __( 'Select a Global Campaign', 'partners-site_v2' ),
			'fields'                => array(
				array(
					'key'               => 'field_605c9b753660d',
					'label'             => __( 'Campaign', 'partners-site_v2' ),
					'name'              => 'campaign',
					'type'              => 'network_post_object',
					'instructions' 		=> __( 'The campaign under which the content of this page will be displayed', 'partners-site_v2' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'post_type'         => array(
						0 => 'campaign',
					),
					'taxonomy'          => '',
					'allow_null'        => 0,
					'multiple'          => 0,
					'return_format'     => 'id',
					'ui'                => 1,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'campaign-override',
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
