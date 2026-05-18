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
        'title' => 'Synchronizacja FindCar.pl',
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
                'label' => 'Synchronizuj z FindCar.pl',
                'name' => 'findcar_enabled',
                'type' => 'true_false',
                'instructions' => 'Zaznacz, aby wysłać to auto do FindCar.pl',
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
                'ui_on_text' => 'Włączona',
                'ui_off_text' => 'Wyłączona',
            ],
            [
                'key' => 'field_findcar_listing_id',
                'label' => 'ID oferty FindCar',
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
                'label' => 'FindCar Numer oferty',
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
                'label' => 'FindCar URL',
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
                'label' => 'Ostatnia synchronizacja',
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
                'label' => 'Błąd synchronizacji',
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
        'title' => 'FindCar.pl',
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
                'label' => 'Ustawienia',
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
                'label' => 'Włącz integrację',
                'name' => 'findcar_enabled',
                'type' => 'true_false',
                'instructions' => 'Włącz synchronizację samochodów z FindCar.pl',
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
                'ui_on_text' => 'Włączona',
                'ui_off_text' => 'Wyłączona',
            ],
            [
                'key' => 'field_findcar_dealer_auto_sync',
                'label' => 'Automatyczna synchronizacja',
                'name' => 'findcar_auto_sync',
                'type' => 'true_false',
                'instructions' => 'Automatycznie synchronizuj samochody po publikacji',
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
                'ui_on_text' => 'Włączona',
                'ui_off_text' => 'Wyłączona',
            ],
            [
                'key' => 'field_findcar_dealer_api_key',
                'label' => 'Klucz API',
                'name' => 'findcar_api_key',
                'type' => 'password',
                'instructions' => 'Klucz API FindCar.pl',
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
                'label' => 'Token autoryzacji lokalizacji',
                'name' => 'findcar_location_token',
                'type' => 'password',
                'instructions' => 'Token autoryzacji lokalizacji',
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
                'label' => 'ID lokalizacji',
                'name' => 'findcar_location_id',
                'type' => 'text',
                'instructions' => 'ID lokalizacji w FindCar.pl',
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
                'label' => 'ID inwentarza nowych',
                'name' => 'findcar_inventory_brand_new',
                'type' => 'text',
                'instructions' => 'ID inwentarza dla samochodów nowych',
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
                'label' => 'ID inwentarza używanych',
                'name' => 'findcar_inventory_pre_owned',
                'type' => 'text',
                'instructions' => 'ID inwentarza dla samochodów używanych',
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
                'label' => 'Status połączenia',
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
                'label' => 'Status',
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
                'label' => 'Testuj połączenie',
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
                'message' => '<button type="button" class="button" id="findcar-test-connection">Testuj połączenie</button>',
                'new_lines' => 'wpautop',
                'esc_html' => 0,
            ],
            [
                'key' => 'field_findcar_dealer_sync_button',
                'label' => 'Synchronizuj wszystkie',
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
                'message' => '<button type="button" class="button button-secondary" id="findcar-preview-sync">Podgląd</button> <button type="button" class="button button-primary" id="findcar-sync-all">Synchronizuj wszystkie samochody</button>',
                'new_lines' => 'wpautop',
                'esc_html' => 0,
            ],
        ],
    ]);
}