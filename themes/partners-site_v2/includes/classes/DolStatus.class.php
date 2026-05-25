<?php

namespace Classes;

use Classes\StockCar;

class DolStatus
{
    public const COLUMN_NAME = 'dol-status';
    public const FIELD_NAME = 'dol_sync';
    public const COLUMN_LABEL = 'Dol Status';
    public const DOL_STATUSES = [
        0 => 'Status not checked',
        1 => 'Data available in DOL',
        2 => 'Data not available in DOL',
        3 => 'DOL Synchronized',
        4 => 'Synchronization failed',
    ];
    public const DOL_STATUSES_ICONS = [
        0 => '<svg fill="#deddda" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 97.524 97.524" xml:space="preserve" stroke="#deddda"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M48.763,30.491c-16.272,0-29.514,13.24-29.514,29.515c0,16.27,13.241,29.512,29.514,29.512s29.511-13.242,29.511-29.512 C78.274,43.732,65.036,30.491,48.763,30.491z M48.763,82.972c-12.666,0-22.969-10.301-22.969-22.966 c0-12.67,10.303-22.972,22.969-22.972c12.663,0,22.967,10.302,22.967,22.972C71.73,72.671,61.428,82.972,48.763,82.972z"></path> <path d="M52.629,67.768l-0.718-0.486c-0.661-0.451-1.555-0.283-2.012,0.367c-0.149,0.215-0.298,0.416-0.447,0.609l3.761-13.363 c0.135-0.479,0.016-0.996-0.316-1.367s-0.834-0.545-1.323-0.465l-7.121,1.178c-0.704,0.117-1.22,0.726-1.22,1.438v0.794 c0,0.429,0.188,0.834,0.512,1.109c0.325,0.274,0.756,0.401,1.177,0.33c0.648-0.104,1.055-0.157,1.31-0.185 c-0.073,0.356-0.219,0.963-0.507,1.977l-2.69,9.249c-0.388,1.342-0.569,2.274-0.569,2.94c0,0.918,0.347,1.728,1.003,2.336 c0.627,0.582,1.448,0.89,2.375,0.89c1.183,0,2.379-0.466,3.563-1.386c1.381-1.084,2.6-2.412,3.62-3.953 C53.467,69.112,53.289,68.216,52.629,67.768z"></path> <path d="M49.051,45.899c-0.673,0.673-1.029,1.529-1.029,2.478c0,0.947,0.36,1.809,1.042,2.49c0.681,0.682,1.542,1.042,2.49,1.042 c0.953,0,1.813-0.364,2.487-1.054c0.666-0.681,1.019-1.537,1.019-2.479c0-0.942-0.348-1.795-1.004-2.467 C52.729,44.554,50.402,44.55,49.051,45.899z"></path> <path d="M78.012,22.936c-0.701-0.059-1.342-0.417-1.76-0.981c-3.974-5.374-10.353-8.689-17.14-8.689 c-0.429,0-0.858,0.013-1.288,0.039c-0.58,0.034-1.15-0.139-1.616-0.488c-4.131-3.119-9.137-4.81-14.379-4.81 c-9.389,0-17.724,5.442-21.614,13.685c-0.352,0.745-1.055,1.262-1.871,1.376C7.992,24.511,0,33.421,0,44.165 c0,9.762,6.608,17.99,15.581,20.5c-0.213-1.523-0.331-3.077-0.331-4.658c0-1.461,0.104-2.896,0.286-4.309 c-4.228-2.123-7.142-6.488-7.142-11.533c0-7.119,5.791-12.91,12.91-12.91c0.36,0,0.713,0.028,1.066,0.058 c2.051,0.157,3.909-1.173,4.408-3.162c1.737-6.918,7.925-11.751,15.051-11.751c4.07,0,7.923,1.572,10.847,4.427 c1,0.976,2.42,1.39,3.789,1.104c0.87-0.181,1.76-0.272,2.647-0.272c4.983,0,9.437,2.802,11.616,7.312 c0.767,1.586,2.443,2.531,4.195,2.351c0.438-0.044,0.873-0.065,1.295-0.065c7.119,0,12.912,5.791,12.912,12.91 c0,5.044-2.916,9.409-7.143,11.533c0.184,1.412,0.287,2.848,0.287,4.309c0,1.581-0.118,3.135-0.331,4.658 c8.972-2.51,15.58-10.739,15.58-20.5C97.523,33.022,88.924,23.848,78.012,22.936z"></path> </g> </g> </g></svg>',
        1 => '<svg fill="#74c253" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 97.524 97.524" xml:space="preserve" stroke="#74c253"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M48.763,30.491c-16.272,0-29.514,13.24-29.514,29.515c0,16.27,13.241,29.512,29.514,29.512s29.511-13.242,29.511-29.512 C78.274,43.732,65.036,30.491,48.763,30.491z M48.763,82.972c-12.666,0-22.969-10.301-22.969-22.966 c0-12.67,10.303-22.972,22.969-22.972c12.663,0,22.967,10.302,22.967,22.972C71.73,72.671,61.428,82.972,48.763,82.972z"></path> <path d="M52.629,67.768l-0.718-0.486c-0.661-0.451-1.555-0.283-2.012,0.367c-0.149,0.215-0.298,0.416-0.447,0.609l3.761-13.363 c0.135-0.479,0.016-0.996-0.316-1.367s-0.834-0.545-1.323-0.465l-7.121,1.178c-0.704,0.117-1.22,0.726-1.22,1.438v0.794 c0,0.429,0.188,0.834,0.512,1.109c0.325,0.274,0.756,0.401,1.177,0.33c0.648-0.104,1.055-0.157,1.31-0.185 c-0.073,0.356-0.219,0.963-0.507,1.977l-2.69,9.249c-0.388,1.342-0.569,2.274-0.569,2.94c0,0.918,0.347,1.728,1.003,2.336 c0.627,0.582,1.448,0.89,2.375,0.89c1.183,0,2.379-0.466,3.563-1.386c1.381-1.084,2.6-2.412,3.62-3.953 C53.467,69.112,53.289,68.216,52.629,67.768z"></path> <path d="M49.051,45.899c-0.673,0.673-1.029,1.529-1.029,2.478c0,0.947,0.36,1.809,1.042,2.49c0.681,0.682,1.542,1.042,2.49,1.042 c0.953,0,1.813-0.364,2.487-1.054c0.666-0.681,1.019-1.537,1.019-2.479c0-0.942-0.348-1.795-1.004-2.467 C52.729,44.554,50.402,44.55,49.051,45.899z"></path> <path d="M78.012,22.936c-0.701-0.059-1.342-0.417-1.76-0.981c-3.974-5.374-10.353-8.689-17.14-8.689 c-0.429,0-0.858,0.013-1.288,0.039c-0.58,0.034-1.15-0.139-1.616-0.488c-4.131-3.119-9.137-4.81-14.379-4.81 c-9.389,0-17.724,5.442-21.614,13.685c-0.352,0.745-1.055,1.262-1.871,1.376C7.992,24.511,0,33.421,0,44.165 c0,9.762,6.608,17.99,15.581,20.5c-0.213-1.523-0.331-3.077-0.331-4.658c0-1.461,0.104-2.896,0.286-4.309 c-4.228-2.123-7.142-6.488-7.142-11.533c0-7.119,5.791-12.91,12.91-12.91c0.36,0,0.713,0.028,1.066,0.058 c2.051,0.157,3.909-1.173,4.408-3.162c1.737-6.918,7.925-11.751,15.051-11.751c4.07,0,7.923,1.572,10.847,4.427 c1,0.976,2.42,1.39,3.789,1.104c0.87-0.181,1.76-0.272,2.647-0.272c4.983,0,9.437,2.802,11.616,7.312 c0.767,1.586,2.443,2.531,4.195,2.351c0.438-0.044,0.873-0.065,1.295-0.065c7.119,0,12.912,5.791,12.912,12.91 c0,5.044-2.916,9.409-7.143,11.533c0.184,1.412,0.287,2.848,0.287,4.309c0,1.581-0.118,3.135-0.331,4.658 c8.972-2.51,15.58-10.739,15.58-20.5C97.523,33.022,88.924,23.848,78.012,22.936z"></path> </g> </g> </g></svg>',
        2 => '<svg fill="#BF2012" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 97.524 97.524" xml:space="preserve" stroke="#BF2012"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M48.763,30.491c-16.272,0-29.514,13.24-29.514,29.515c0,16.27,13.241,29.512,29.514,29.512s29.511-13.242,29.511-29.512 C78.274,43.732,65.036,30.491,48.763,30.491z M48.763,82.972c-12.666,0-22.969-10.301-22.969-22.966 c0-12.67,10.303-22.972,22.969-22.972c12.663,0,22.967,10.302,22.967,22.972C71.73,72.671,61.428,82.972,48.763,82.972z"></path> <path d="M52.629,67.768l-0.718-0.486c-0.661-0.451-1.555-0.283-2.012,0.367c-0.149,0.215-0.298,0.416-0.447,0.609l3.761-13.363 c0.135-0.479,0.016-0.996-0.316-1.367s-0.834-0.545-1.323-0.465l-7.121,1.178c-0.704,0.117-1.22,0.726-1.22,1.438v0.794 c0,0.429,0.188,0.834,0.512,1.109c0.325,0.274,0.756,0.401,1.177,0.33c0.648-0.104,1.055-0.157,1.31-0.185 c-0.073,0.356-0.219,0.963-0.507,1.977l-2.69,9.249c-0.388,1.342-0.569,2.274-0.569,2.94c0,0.918,0.347,1.728,1.003,2.336 c0.627,0.582,1.448,0.89,2.375,0.89c1.183,0,2.379-0.466,3.563-1.386c1.381-1.084,2.6-2.412,3.62-3.953 C53.467,69.112,53.289,68.216,52.629,67.768z"></path> <path d="M49.051,45.899c-0.673,0.673-1.029,1.529-1.029,2.478c0,0.947,0.36,1.809,1.042,2.49c0.681,0.682,1.542,1.042,2.49,1.042 c0.953,0,1.813-0.364,2.487-1.054c0.666-0.681,1.019-1.537,1.019-2.479c0-0.942-0.348-1.795-1.004-2.467 C52.729,44.554,50.402,44.55,49.051,45.899z"></path> <path d="M78.012,22.936c-0.701-0.059-1.342-0.417-1.76-0.981c-3.974-5.374-10.353-8.689-17.14-8.689 c-0.429,0-0.858,0.013-1.288,0.039c-0.58,0.034-1.15-0.139-1.616-0.488c-4.131-3.119-9.137-4.81-14.379-4.81 c-9.389,0-17.724,5.442-21.614,13.685c-0.352,0.745-1.055,1.262-1.871,1.376C7.992,24.511,0,33.421,0,44.165 c0,9.762,6.608,17.99,15.581,20.5c-0.213-1.523-0.331-3.077-0.331-4.658c0-1.461,0.104-2.896,0.286-4.309 c-4.228-2.123-7.142-6.488-7.142-11.533c0-7.119,5.791-12.91,12.91-12.91c0.36,0,0.713,0.028,1.066,0.058 c2.051,0.157,3.909-1.173,4.408-3.162c1.737-6.918,7.925-11.751,15.051-11.751c4.07,0,7.923,1.572,10.847,4.427 c1,0.976,2.42,1.39,3.789,1.104c0.87-0.181,1.76-0.272,2.647-0.272c4.983,0,9.437,2.802,11.616,7.312 c0.767,1.586,2.443,2.531,4.195,2.351c0.438-0.044,0.873-0.065,1.295-0.065c7.119,0,12.912,5.791,12.912,12.91 c0,5.044-2.916,9.409-7.143,11.533c0.184,1.412,0.287,2.848,0.287,4.309c0,1.581-0.118,3.135-0.331,4.658 c8.972-2.51,15.58-10.739,15.58-20.5C97.523,33.022,88.924,23.848,78.012,22.936z"></path> </g> </g> </g></svg>',
        3 => '<svg fill="#74c253" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 98.588 98.588" xml:space="preserve" stroke="#74c253"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M47.02,73.915h-3.857V48.238c0-1.934-1.567-3.5-3.497-3.5h-9.544c-1.932,0-3.498,1.566-3.498,3.5v25.677h-3.858 c-0.885,0-1.691,0.515-2.062,1.315c-0.374,0.808-0.243,1.752,0.329,2.431l10.328,12.146c0.882,1.036,2.174,1.629,3.533,1.629 c1.359,0,2.65-0.594,3.532-1.629l10.327-12.146c0.572-0.679,0.704-1.625,0.33-2.431C48.712,74.43,47.907,73.915,47.02,73.915z"></path> <path d="M67.225,46.369c-0.881-1.036-2.172-1.631-3.531-1.631s-2.651,0.595-3.532,1.631L49.833,58.512 c-0.571,0.678-0.702,1.624-0.329,2.43c0.371,0.804,1.177,1.317,2.063,1.317h3.856v25.677c0,1.937,1.566,3.503,3.498,3.503h9.543 c1.932,0,3.498-1.566,3.498-3.503V62.258h3.856c0.885,0,1.692-0.515,2.062-1.317c0.375-0.806,0.243-1.751-0.328-2.43 L67.225,46.369z"></path> <path d="M78.852,22.241c-0.7-0.058-1.344-0.417-1.762-0.982c-4.017-5.438-10.469-8.792-17.332-8.792 c-0.438,0-0.875,0.013-1.312,0.04c-0.582,0.035-1.154-0.138-1.618-0.489c-4.177-3.157-9.241-4.868-14.544-4.868 c-9.496,0-17.924,5.507-21.854,13.847c-0.352,0.746-1.057,1.263-1.873,1.376C8.087,23.828,0,32.839,0,43.703 c0,11.876,9.662,21.539,21.537,21.539h0.939V48.238c0-4.216,3.43-7.647,7.646-7.647h9.544c4.215,0,7.645,3.431,7.645,7.647v6.837 l9.693-11.395c1.67-1.964,4.109-3.091,6.69-3.091c2.579,0,5.019,1.126,6.688,3.089l10.33,12.145 c1.618,1.918,1.979,4.606,0.932,6.862c-0.438,0.95-1.106,1.749-1.911,2.368c10.61-1.327,18.854-10.387,18.854-21.352 C98.588,32.436,89.887,23.159,78.852,22.241z"></path> </g> </g> </g></svg>',
        4 => '<svg fill="#BF2012" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 98.588 98.588" xml:space="preserve" stroke="#BF2012"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M47.02,73.915h-3.857V48.238c0-1.934-1.567-3.5-3.497-3.5h-9.544c-1.932,0-3.498,1.566-3.498,3.5v25.677h-3.858 c-0.885,0-1.691,0.515-2.062,1.315c-0.374,0.808-0.243,1.752,0.329,2.431l10.328,12.146c0.882,1.036,2.174,1.629,3.533,1.629 c1.359,0,2.65-0.594,3.532-1.629l10.327-12.146c0.572-0.679,0.704-1.625,0.33-2.431C48.712,74.43,47.907,73.915,47.02,73.915z"></path> <path d="M67.225,46.369c-0.881-1.036-2.172-1.631-3.531-1.631s-2.651,0.595-3.532,1.631L49.833,58.512 c-0.571,0.678-0.702,1.624-0.329,2.43c0.371,0.804,1.177,1.317,2.063,1.317h3.856v25.677c0,1.937,1.566,3.503,3.498,3.503h9.543 c1.932,0,3.498-1.566,3.498-3.503V62.258h3.856c0.885,0,1.692-0.515,2.062-1.317c0.375-0.806,0.243-1.751-0.328-2.43 L67.225,46.369z"></path> <path d="M78.852,22.241c-0.7-0.058-1.344-0.417-1.762-0.982c-4.017-5.438-10.469-8.792-17.332-8.792 c-0.438,0-0.875,0.013-1.312,0.04c-0.582,0.035-1.154-0.138-1.618-0.489c-4.177-3.157-9.241-4.868-14.544-4.868 c-9.496,0-17.924,5.507-21.854,13.847c-0.352,0.746-1.057,1.263-1.873,1.376C8.087,23.828,0,32.839,0,43.703 c0,11.876,9.662,21.539,21.537,21.539h0.939V48.238c0-4.216,3.43-7.647,7.646-7.647h9.544c4.215,0,7.645,3.431,7.645,7.647v6.837 l9.693-11.395c1.67-1.964,4.109-3.091,6.69-3.091c2.579,0,5.019,1.126,6.688,3.089l10.33,12.145 c1.618,1.918,1.979,4.606,0.932,6.862c-0.438,0.95-1.106,1.749-1.911,2.368c10.61-1.327,18.854-10.387,18.854-21.352 C98.588,32.436,89.887,23.159,78.852,22.241z"></path> </g> </g> </g></svg>',
    ];

    public function __construct()
    {
       add_action('admin_head', [__CLASS__, 'limit_column_width']);
       add_filter('manage_posts_columns', [__CLASS__, 'add_to_listing'], 10, 2);
       add_action('manage_stock-car_posts_custom_column', [__CLASS__, 'add_column_content'], 10, 2);
    }

    public static function add_to_listing($defaults, $post_type)
    {
        if ($post_type === StockCar::POST_TYPE) {
            $defaults['vin'] = 'VIN/CON';
            $defaults['findcar_status'] = 'Findcar';
            $defaults['otomoto_status'] = 'Otomoto';
        }


        return $defaults;
    }

    public static function add_column_content($column_name, $post_id)
    {
        if ($column_name === 'vin') {
            $vin = get_field('vin',$post_id);
            if ($vin) {
                echo $vin;
            } else {
                echo get_field('con',$post_id);
            }
            
        }
        if ($column_name === 'findcar_status') {
            $findcar_enabled = get_field('findcar_enabled', $post_id);
            $findcar_sync_error = get_field('findcar_sync_error', $post_id);
            $findcar_listing_id = get_field('findcar_listing_id', $post_id);
            $findcar_status = get_post_meta($post_id, 'findcar_status', true);
            $findcar_listing_status = get_post_meta($post_id, 'findcar_listing_status', true);

            $is_missing_fields = !empty($findcar_sync_error) && strpos($findcar_sync_error, __('Required fields missing', 'partners-site_v2') . ':') !== false;
            $is_api_error = !empty($findcar_sync_error) && !$is_missing_fields;
            $is_synced = !empty($findcar_listing_id) && empty($findcar_sync_error);
            $is_active = $findcar_status === 'active' && $findcar_listing_status === 'active';

            if (!$findcar_enabled && !$is_synced) {
                $color = '#9e9e9e';
                $title = 'Findcar: ' . __('Not synchronized', 'partners-site_v2');
                $status_class = 'findcar-grey';
                $label = __('Not synchronized', 'partners-site_v2');
            } elseif ($is_missing_fields) {
                $missing_list = str_replace(__('Required fields missing', 'partners-site_v2') . ': ', '', $findcar_sync_error);
                $color = '#ff9800';
                $title = 'Findcar: ' . __('Data missing', 'partners-site_v2') . ' - ' . esc_attr($missing_list);
                $status_class = 'findcar-orange';
                $label = __('Missing', 'partners-site_v2') . ': ' . esc_html($missing_list);
            } elseif ($is_api_error) {
                $color = '#f44336';
                $title = 'Findcar: ' . __('Error', 'partners-site_v2') . ' - ' . esc_attr($findcar_sync_error);
                $status_class = 'findcar-red';
                $label = __('Error', 'partners-site_v2') . ': ' . esc_html($findcar_sync_error);
            } elseif ($is_active) {
                $color = '#4caf50';
                $title = 'Findcar: ' . __('Active', 'partners-site_v2');
                $status_class = 'findcar-green';
                $label = __('Active', 'partners-site_v2');
            } elseif ($is_synced) {
                $color = '#4caf50';
                $title = 'Findcar: ' . __('Synchronized', 'partners-site_v2');
                $status_class = 'findcar-green';
                $label = __('Synchronized', 'partners-site_v2');
            } else {
                $color = '#f44336';
                $title = 'Findcar: ' . __('Inactive', 'partners-site_v2');
                $status_class = 'findcar-red';
                $label = __('Inactive', 'partners-site_v2');
            }

            echo <<<HTML
            <div class="findcar_status-wrap">
                <div class="findcar_status-icon $status_class" title="$title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" version="1.1"><g id="surface1">
                    <path style=" " fill="$color" d="M 16 3 C 8.832031 3 3 8.832031 3 16 C 3 23.167969 8.832031 29 16 29 C 23.167969 29 29 23.167969 29 16 C 29 8.832031 23.167969 3 16 3 Z M 16 5 C 22.085938 5 27 9.914063 27 16 C 27 22.085938 22.085938 27 16 27 C 9.914063 27 5 22.085938 5 16 C 5 9.914063 9.914063 5 16 5 Z M 22.28125 11.28125 L 15 18.5625 L 10.71875 14.28125 L 9.28125 15.71875 L 14.28125 20.71875 L 15 21.40625 L 15.71875 20.71875 L 23.71875 12.71875 Z "/>
                    </g></svg>
                </div>
                <span class="findcar_status-label">$label</span>
            </div>
            HTML;
        }
        if ($column_name === 'otomoto_status') {
            $otomoto_enabled = false;
            if (function_exists('get_field')) {
                $settings = get_field('otomoto_settings', 'options-dealer');
                $otomoto_enabled = $settings && isset($settings['otomoto_enabled']) && $settings['otomoto_enabled'];
            }
            
            if ($otomoto_enabled) {
                $color = 'green';
                $title = esc_attr('Otomoto: ' . __('On', 'partners-site_v2'));
            } else {
                $color = 'red';
                $title = esc_attr('Otomoto: ' . __('Off', 'partners-site_v2'));
            }
            
            echo <<<HTML
            <div class="otomoto_status" style="width:35px;" title="$title">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" version="1.1"><g id="surface1">
            <path style=" " fill="$color" d="M 16 3 C 8.832031 3 3 8.832031 3 16 C 3 23.167969 8.832031 29 16 29 C 23.167969 29 29 23.167969 29 16 C 29 8.832031 23.167969 3 16 3 Z M 16 5 C 22.085938 5 27 9.914063 27 16 C 27 22.085938 22.085938 27 16 27 C 9.914063 27 5 22.085938 5 16 C 5 9.914063 9.914063 5 16 5 Z M 22.28125 11.28125 L 15 18.5625 L 10.71875 14.28125 L 9.28125 15.71875 L 14.28125 20.71875 L 15 21.40625 L 15.71875 20.71875 L 23.71875 12.71875 Z "/>
            </g></svg>
            </div>
            HTML;
        }
    }

    public static function limit_column_width()
    {
        echo <<<HTML
            <style>
                #vin {
                    width:150px;
                }
                #findcar_status,
                #otomoto_status {
                    width: 180px;
                    text-align:left;
                }
                .findcar_status-wrap {
                    display:flex;
                    align-items:center;
                    gap:8px;
                    width:100%;
                }
                .findcar_status-icon {
                    flex-shrink:0;
                    width:20px;
                    height:20px;
                }
                .findcar_status-icon svg {
                    width:20px;
                    height:20px;
                    display:block;
                }
                .findcar_status-label {
                    font-size:11px;
                    line-height:1.3;
                    color:#555;
                    white-space:normal;
                    word-break:break-word;
                    max-width:140px;
                }
                .otomoto_status {
                    width:100%;
                    text-align:center;
                }
                td.findcar_status > div,
                td.otomoto_status > div {
                    margin: 0px auto;
                }
                .otomoto_status svg {
                    width:20px;
                }
                .findcar_status-icon {
                    flex-shrink:0;
                    width:20px;
                    height:20px;
                }
                .findcar_status-icon svg {
                    width:20px;
                    height:20px;
                    display:block;
                }
                .findcar_status-label {
                    font-size:11px;
                    line-height:1.3;
                    color:#555;
                    white-space:normal;
                    word-break:break-word;
                    max-width:140px;
                }
                td.findcar_status > div,
                td.otomoto_status > div {
                    margin: 0px auto;
                }
                .otomoto_status svg {
                    width:20px;
                }
            </style>
        HTML;
    }

    public static function data_in_dol(int $post_id): bool
    {
        return  array_search(get_field(self::FIELD_NAME, $post_id), [1, 3, 4]);
    }
}
