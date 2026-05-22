<?php
if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
	
		array(
			'key'                   => 'group_60794d9a78fe6',
			'title'                 => __('Redirects', 'partners-site_v2'),
			'fields'                => array(
					array(
						'key'               => 'field_redirections_csv',
						'label'             => __('Redirects in CSV format', 'partners-site_v2'),
						'name'              => 'field_redirections_csv',
						'type'              => 'file',
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
						'maxlength'         => '',
					),
				array(
					'key'               => 'field_60794dacf7a95',
					'label'             => __('Redirects', 'partners-site_v2'),
					'name'              => 'redirections',
					'type'              => 'repeater',
					'instructions'      => __( '<strong>WARNING!</strong><br><br>302 Redirect: used for temporary redirects (short-term, e.g., one month) or for testing redirects before switching to a 301 redirect<br>301 Redirect: permanent, stored locally on users\' devices. They should never be changed. Any changes or removal take a very long time<br><br><strong>First, add a 302 redirect, test it thoroughly, and once you are absolutely certain that the redirect links will not change and the redirect is functioning correctly, you can change it to a 301 redirect</strong><br/ ><br/><button class=\“removeRows\” type="button">Remove selected rows</button>', 'partners-site_v2' ),
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
					'layout'            => 'table',
					'button_label'      => '',
					'sub_fields'        => array(
						array(
							'key'               => 'field_60794e2af7a96',
							'label'             => __('Type', 'partners-site_v2'),
							'name'              => 'code',
							'type'              => 'select',
							'instructions'      => '',
							'required'          => 1,
							'conditional_logic' => 0,
							'wrapper'           => array(
								'width' => '5',
								'class' => '',
								'id'    => '',
							),
							'choices'           => array(
								302 => '302',
								301 => '301',
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
							'key'               => 'field_60794e64f7a97',
							'label' => __('Source URL', 'partners-site_v2'),
							'name'              => 'source',
							'type'              => 'url',
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
						),
						array(
							'key'               => 'field_60794f3af7a99',
							'label'             => __('Destination URL', 'partners-site_v2'),
							'name'              => 'target',
							'type'              => 'url',
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
						),

						array(
							'key' => 'field_66742938f4473',
							'label' => __('Post ID', 'partners-site_v2'),
							'name' => 'post_id',
							'type' => 'number',
							'instructions' => '',
							'readonly' => 1,
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '8',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
						),

					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'options-redirects',
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
