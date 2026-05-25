<?php

use Classes\CarDictionary;

if (function_exists('acf_add_local_field_group')) :
    acf_add_local_field_group(
        array(
            'key'                   => 'group_offerBox',
            'title'                 => __('Offer Box', 'partners-site_v2'),
            'fields'                => array(
                array(
                    'key'               => 'widget_title',
                    'label' => __( 'Title', 'partners-site_v2' ),
                    'name'              => 'heading',
                    'type'              => 'text',
                    'instructions' => __( 'By default, the header text is gray. To make a specific phrase black, please place it between &lt;strong&gt;&lt;/strong&gt;,<br>e.g., &lt;strong&gt;TEXT&lt;/strong&gt;', 'partners-site_v2' ),
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
                    'maxlength'         => 166,
                ),
                array(
                    'key'               => 'field_offerBox',
                    'label'             => __('Box', 'partners-site_v2'),
                    'name'              => 'offer_boxes',
                    'type'              => 'repeater',
                    'instructions'      => '',
                    'required'          => 1,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'collapsed'         => 'widget_model',
                    'min'               => 0,
                    'max'               => 1,
                    'layout'            => 'block',
                    'button_label'      => '',
                    'sub_fields'        => array(
                        array(
                            'key' => 'widget_model',
                            'label' => __('Model', 'partners-site_v2'),
                            'name' => 'widget_model',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '100',
                                'class' => '',
                                'id' => '',
                            ),
                            'choices' => CarDictionary::getModels(),
                            'default_value' => false,
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 0,
                            'return_format' => 'value',
                            'ajax' => 0,
                            'placeholder' => '',
                            'admin-only' => false
                        ),
                    ),
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'block',
                        'operator' => '==',
                        'value'    => 'acf/offer-box',
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
