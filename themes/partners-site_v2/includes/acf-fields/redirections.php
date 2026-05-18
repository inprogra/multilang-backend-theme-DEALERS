<?php
if ( function_exists( 'acf_add_local_field_group' ) ) :

	acf_add_local_field_group(
	
		array(
			'key'                   => 'group_60794d9a78fe6',
			'title'                 => 'Przekierowania',
			'fields'                => array(
					array(
						'key'               => 'field_redirections_csv',
						'label'             => 'Przekierowania w postaci CSV',
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
					'label'             => 'Przekierowania',
					'name'              => 'redirections',
					'type'              => 'repeater',
					'instructions'      => '<strong>UWAGA!</strong><br><br>
Przekierowanie 302: wykorzystywane do tymczasowego przekierowania adresu (krótki okres np. miesiąc) bądź testowania przekierowania przed zmianą na 301<br>
Przekierowanie 301: stałe zapisywane lokalnie na urządzeniach użytkowników. Nie powinny nigdy być zmieniane. Ewentualne zmiany lub usunięcie go zajmują bardzo dużo czasu<br><br>
<strong>Należy najpierw dodać przekierowanie typu 302, przetestestować je dokładnie i posiadając absolutną pewnność że linki przekierowania się nie zmienią oraz przekierowanie funkcjonuje poprawnie - można zmienić je na typ 301</strong>
<br/>
<br/>
<button class="removeRows" type="button">Usuń wybrane wiersze</button>
',
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
							'label'             => 'Typ',
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
							'label' => __( 'Url źródłowy', 'partners-site_v2' ),
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
							'label'             => 'Adres docelowy',
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
							'label' => 'Post id',
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
