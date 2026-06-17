<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('acf/include_fields', 'findcar_register_acf_fields');
add_action('acf/init', 'findcar_register_acf_fields', 5);

function findcar_register_acf_fields()
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_findcar_car_settings',
        'title' => __('Synchronization FindCar.pl', 'volvo-feed-templates'),
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'stock-car',
                ],
            ],
        ],
        'menu_order' => 50,
        'position' => 'side',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'fields' => [
            [
                'key' => 'field_findcar_enabled',
                'label' => __('Sync with FindCar.pl', 'volvo-feed-templates'),
                'name' => 'findcar_enabled',
                'type' => 'true_false',
                'instructions' => __('Check this box to send this car to FindCar.pl', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => __('Enabled', 'volvo-feed-templates'),
                'ui_off_text' => __('Disabled', 'volvo-feed-templates'),
            ],
            [
                'key' => 'field_findcar_listing_id',
                'label' => __('Offer ID FindCar', 'volvo-feed-templates'),
                'name' => 'findcar_listing_id',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'readonly' => 1,
                'disabled' => 1,
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_listing_number',
                'label' => __('Offer number FindCar', 'volvo-feed-templates'),
                'name' => 'findcar_listing_number',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'readonly' => 1,
                'disabled' => 1,
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_listing_url',
                'label' => __('FindCar URL', 'volvo-feed-templates'),
                'name' => 'findcar_listing_url',
                'type' => 'url',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'readonly' => 1,
                'disabled' => 1,
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_last_sync',
                'label' => __('Last sync', 'volvo-feed-templates'),
                'name' => 'findcar_last_sync',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'readonly' => 1,
                'disabled' => 1,
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_sync_error',
                'label' => __('Synchronization error', 'volvo-feed-templates'),
                'name' => 'findcar_sync_error',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'readonly' => 1,
                'disabled' => 1,
                'placeholder' => '',
            ],
        ],
    ]);

    acf_add_local_field_group([
        'key' => 'group_findcar_dealer_options',
        'title' => __('FindCar.pl', 'volvo-feed-templates'),
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'options-dealer',
                ],
            ],
        ],
        'menu_order' => 50,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'fields' => [
            [
                'key' => 'field_findcar_dealer_tab',
                'label' => __('Settings', 'volvo-feed-templates'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'placement' => 'top',
                'endpoint' => 0,
            ],
            [
                'key' => 'field_findcar_dealer_enabled',
                'label' => __('Enable integration', 'volvo-feed-templates'),
                'name' => 'findcar_enabled',
                'type' => 'true_false',
                'instructions' => __('Enable car synchronization with FindCar.pl', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => __('Enabled', 'volvo-feed-templates'),
                'ui_off_text' => __('Disabled', 'volvo-feed-templates'),
            ],
            [
                'key' => 'field_findcar_dealer_auto_sync',
                'label' => __('Automatic synchronization', 'volvo-feed-templates'),
                'name' => 'findcar_auto_sync',
                'type' => 'true_false',
                'instructions' => __('Automatically sync cars after publication', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_findcar_dealer_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => __('Enabled', 'volvo-feed-templates'),
                'ui_off_text' => __('Disabled', 'volvo-feed-templates'),
            ],
            [
                'key' => 'field_findcar_dealer_api_key',
                'label' => __('API key', 'volvo-feed-templates'),
                'name' => 'findcar_api_key',
                'type' => 'password',
                'instructions' => __('FindCar.pl API key', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_findcar_dealer_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ],
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_dealer_location_token',
                'label' => __('Location authorization token', 'volvo-feed-templates'),
                'name' => 'findcar_location_token',
                'type' => 'password',
                'instructions' => __('Location authorization token', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_findcar_dealer_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ],
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_dealer_location_id',
                'label' => __('Location ID', 'volvo-feed-templates'),
                'name' => 'findcar_location_id',
                'type' => 'text',
                'instructions' => __('Location ID in FindCar.pl', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_findcar_dealer_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ],
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_inventory_brand_new',
                'label' => __('New inventory IDs', 'volvo-feed-templates'),
                'name' => 'findcar_inventory_brand_new',
                'type' => 'text',
                'instructions' => __('Vehicle identification number for new cars', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ],
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_inventory_pre_owned',
                'label' => __('Used inventory ID', 'volvo-feed-templates'),
                'name' => 'findcar_inventory_pre_owned',
                'type' => 'text',
                'instructions' => __('Vehicle identification number for used cars', 'volvo-feed-templates'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ],
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_dealer_status_tab',
                'label' => __('Connection status', 'volvo-feed-templates'),
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_findcar_dealer_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'placement' => 'top',
                'endpoint' => 0,
            ],
            [
                'key' => 'field_findcar_dealer_connection_status',
                'label' => __('Status', 'volvo-feed-templates'),
                'name' => 'findcar_connection_status',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'readonly' => 1,
                'disabled' => 1,
                'placeholder' => '',
            ],
            [
                'key' => 'field_findcar_dealer_test_button',
                'label' => __('Test connection', 'volvo-feed-templates'),
                'name' => 'findcar_test_button',
                'type' => 'message',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_findcar_dealer_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '<button type="button" class="button" id="findcar-test-connection">' . __('Test connection', 'volvo-feed-templates') . '</button>',
                'new_lines' => 'wpautop',
                'esc_html' => 0,
            ],
            [
                'key' => 'field_findcar_dealer_sync_button',
                'label' => __('Synchronize all', 'volvo-feed-templates'),
                'name' => 'findcar_sync_button',
                'type' => 'message',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_findcar_dealer_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '<button type="button" class="button button-secondary" id="findcar-preview-sync">' . __('Preview', 'volvo-feed-templates') . '</button> <button type="button" class="button button-primary" id="findcar-sync-all">' . __('Synchronize all cars', 'volvo-feed-templates') . '</button>',
                'new_lines' => 'wpautop',
                'esc_html' => 0,
            ],
        ],
    ]);
}