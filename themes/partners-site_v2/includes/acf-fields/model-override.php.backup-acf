<?php

if ( function_exists( 'acf_add_local_field_group' ) ) :
	acf_add_local_field_group(
		array(
			'key'                   => 'group_604f1fe1dadef',
			'title'                 => 'Model-Override',
			'fields'                => array(
				array(
					'key'               => 'field_604f1fef287ca',
					'label'             => 'Model',
					'name'              => 'model',
					'type'              => 'network_post_object',
					'instructions'      => 'Model pod którym wyświetli się treść tej strony',
					'required'          => 1,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'post_type'         => array(
						0 => 'model',
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
						'value'    => 'model-override',
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

add_filter( 'acf/fields/network_post_object/query/key=field_604f1fef287ca', 'modifyModelOverrideItemsQuery', 10, 3 );
function modifyModelOverrideItemsQuery( $args, $field, $post_id ): array {
	$args['post_parent__not_in'] = array( 0 );
	return $args;
}

add_filter( 'acf/fields/network_post_object/result/key=field_604f1fef287ca', 'modifyModelOverrideItemsResult', 10, 4 );
function modifyModelOverrideItemsResult( $text, $post ): string {
	if ( $post->post_parent !== 0 ) {
		$parent_post       = get_post( $post->post_parent );
		$parent_post_title = $parent_post->post_title;
		return $parent_post_title . ' ' . $text;
	}
	return $text;
}

add_filter( 'acf/fields/network_post_object/get_posts_args/key=field_604f1fef287ca', 'modifyModelOverrideGetPosts', 10, 4 );
function modifyModelOverrideGetPosts( $args ): array {
	$args['post_parent__not_in'] = array( 0 );

	return $args;
}
