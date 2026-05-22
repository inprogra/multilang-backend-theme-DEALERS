<?php
if ( function_exists( 'acf_add_local_field_group' ) ) {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_blog',
			'title'                 => __( 'Blog Content Settings', 'partners-site_v2' ),
			'fields'                => array(
				array(
					'key'               => 'blog_desc',
					'label'             => __( 'Description', 'partners-site_v2' ),
					'name'              => 'blog_desc',
					'type'              => 'textarea',
					'instructions' 		=> sprintf(__( '(max. %s chars)', 'partners-site_v2' ), 50),
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
					'maxlength'         => 50,
				),
				array(
					'key'               => 'blog_image',
					'label' 			=> __( 'Photo', 'partners-site_v2' ),
					'name'              => 'blog_image',
					'type'              => 'image',
					'instructions'		=> __( '(min. width: 1920px, Recommended width: 3840px)<br>(min. height: 807px)<br>The main campaign image used in various sliders on the website.', 'partners-site_v2' ),
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'return_format'     => 'integer',
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
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'blog',
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
}
