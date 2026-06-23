<?php

namespace Controllers;

use Classes\Controller;

class EventController extends Controller


{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueMediaUploader']);

        add_action('wp_ajax_eventy_get', [$this, 'ajaxGetEvents']);
        add_action('wp_ajax_eventy_post', [$this, 'ajaxPostEvent']);
        add_action('wp_ajax_eventy_put', [$this, 'ajaxPutEvent']);
        add_action('wp_ajax_eventy_remove', [$this, 'ajaxDeleteEvent']);
        add_action('wp_ajax_participants_remove', [$this, 'ajaxDeleteParticipant']);
        add_action('admin_post_export_events_csv', [$this, 'exportEventsCsv']);
    }
    

    public function registerMenu()
    {
        add_menu_page(
            __( 'Events', 'partners-site_v2'),
            __( 'Events', 'partners-site_v2'),
            'publish_pages',
            'eventy', 
            [$this, 'renderPage']
        );
        add_submenu_page(
            'eventy', 
            __( 'List of participants', 'partners-site_v2'), 
            __( 'List of participants', 'partners-site_v2'),
            'publish_pages',
            'eventy-lista-uczestnikow',
            [$this, 'renderParticipantsPage'] 
        );
        
    }

    public function enqueueMediaUploader($hook)
{
 
    wp_localize_script('jquery', 'eventyParams', [
        'redirectUrl' => esc_url(admin_url('admin.php?page=eventy')),
        'blogId' => get_current_blog_id(), 
    ]);
    
    if ($hook !== 'toplevel_page_eventy') return;

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    wp_add_inline_script('jquery', "
    jQuery(document).ready(function($) {
        var mediaUploader;

        $('.button-danger').on('click', function(e) {
            e.preventDefault();

            var eventId = $(this).data('event-id');
            var confirmDelete = confirm(" . json_encode(__('Are you sure you want to delete this event?', 'partners-site_v2')) . ");
            if (!confirmDelete) return;

            console.log({
                action: 'eventy_remove',  
                _id: eventId,  
            });

            $.ajax({
                url: ajaxurl,
                method: 'POST', 
                data: {
                    action: 'eventy_remove', 
                    _id: eventId,  
                },
                contentType: 'application/x-www-form-urlencoded',  
                success: function(response) {
                    if (response.success) {
                        alert(" . json_encode(__('Event deleted!', 'partners-site_v2')) . ");
                        location.reload(); 
                    } else {
                        alert(" . json_encode(__('Error', 'partners-site_v2')) . " + ': ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(" . json_encode(__('AJAX error: ', 'partners-site_v2')) . ", xhr, status, error);  
                    alert(" . json_encode(__('An error occurred while deleting the event.', 'partners-site_v2')) . ");
                }
            });
        });

        $(document).on('click', '.select-image-button', function(e) {
            e.preventDefault();
            var button = $(this);
            var inputId = button.data('input-id');  
            var input = $('#' + inputId);            
            var imagePreview = input.siblings('img'); 

            var mediaUploader = wp.media({
                title: " . json_encode(__('Select an image', 'partners-site_v2')) . ",
                button: { text: " . json_encode(__('Upload an image', 'partners-site_v2')) . " },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                var imageUrl = attachment.url;

                input.val(imageUrl);                 
                input.removeClass('validation-error'); 
                input.next('.validation-message').remove(); 

                if (imagePreview.length) {
                    imagePreview
                        .attr('src', imageUrl)
                        .css({
                            'max-width': '150px',
                            'max-height': '150px',
                            'object-fit': 'cover',
                            'margin-top': '10px'
                        });
                }
            });

            mediaUploader.open();
        });

        $('<style>')
            .prop('type', 'text/css')
            .html('.limited-width { max-width: 400px; width: 100%; } .validation-error { border-color: red; } .validation-message { color:red; margin-top:5px; font-size:13px; }')
            .appendTo('head');

        $('form#event-edit-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var valid = true;

            form.find('input, textarea').removeClass('validation-error');
            form.find('.validation-message').remove(); 

            var nameField = form.find('input[name=\"name\"]');
            if (!nameField.val().trim()) {
                nameField.addClass('validation-error');
                var msg_name = " . json_encode(__('The "Name" field is required.', 'partners-site_v2')) . ";
                $('<p class=\"validation-message\">' + msg_name + '</p>').insertAfter(nameField);
                valid = false;
            }

            var imageField = form.find('input[name=\"image\"]');
            if (!imageField.val().trim()) {
                imageField.addClass('validation-error');
                var msg_image = " . json_encode(__('The "Image" field is required.', 'partners-site_v2')) . ";
                $('<p class=\"validation-message\">' + msg_image + '</p>').insertAfter(imageField);
                valid = false;
            }

            if (!valid) return;

            var eventId = form.find('input[name=\"event_id\"]').val();
            var payload = {};

            if (eventId) {
                payload._id = eventId;
            }

            form.find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (!name) return;

                if (name === 'image') {
                    payload[name] = { path: $(this).val() };
                } else if (name === 'status' || name === 'private') {
                    payload[name] = $(this).is(':checked');
                } else {
                    payload[name] = $(this).val();
                } 
            });
            
            console.log('Wysyłana ścieżka obrazka:', payload.image.path); 
            // edycja wydarzeń
            var blogId = eventyParams.blogId;

            $.ajax({
                url: 'https://events.dealervolvo.pl/instance' + blogId + '/api/collections/save/pricing?token=4ca43516c3548033e78fa126f2ae9b', 
                type: 'POST',
                data: JSON.stringify({ data: [ payload ] }),
                contentType: 'application/json',
                success: function(response) {
                    var msg_success = " . json_encode(__('Changes saved successfully!', 'partners-site_v2')) . ";
                    $('<div class=\"notice notice-success\"><p>' + msg_success + '</p></div>').insertBefore('.wrap h1');
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: $('.notice').offset().top
                        }, 200);}, 500);
                    setTimeout(function() {
                        window.location.href = eventyParams.redirectUrl;
                    }, 2000);
                },
                error: function(xhr) {
                    console.log('Błąd zapisu:', xhr);
                    console.log('Odpowiedź serwera:', xhr.responseText);

                    var msg = xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)
                        ? (xhr.responseJSON.message || xhr.responseJSON.error)
                        : xhr.status;
                    var msg_prefix = " . json_encode(__('Error while saving:', 'partners-site_v2')) . ";
                    $('<div class=\"notice notice-error\"><p>' + msg_prefix + ' ' + msg + '</p></div>').insertBefore('.wrap h1');
                }
            });
        });

    });
    ");
}


    // spis eventow
    public function fetchExternalEvents()
{
    // $blogId = get_current_blog_id();
    $instanceID = get_fields('options-dealer')['event_instance'];
    $blogId = !empty($instanceID) ? $instanceID : get_current_blog_id();
   

    $url = 'https://events.dealervolvo.pl/api/getEvents/instance' . $blogId . '/';

    $response = wp_remote_get($url, [
        'headers'     => [
            'api-key' => '*~(Yrp85T:.XSF/!%A:{c-c(kz:y-Q'
        ],
        'timeout'     => 20,   
        'redirection' => 5,    
        'httpversion' => '1.1',
    ]);

    if (is_wp_error($response)) {
        return ['error' => __('Connection error', 'partners-site_v2') . ': ' . $response->get_error_message()];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        return [
            'error'       => __('HTTP error', 'partners-site_v2') . ": $status_code",
            'body_preview'=> substr(wp_remote_retrieve_body($response), 0, 500) 
        ];
    }

    $body = wp_remote_retrieve_body($response);

    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'error'       => __('Failed to parse JSON', 'partners-site_v2') . ': ' . json_last_error_msg(),
            'body_preview'=> substr($body, 0, 500)
        ];
    }

    return $data;
}


    public function ajaxDeleteEvent()
{

    if (!isset($_POST['_id'])) {
        wp_send_json_error(['message' => 'Brak ID eventu']);
    }

    $eventId = sanitize_text_field($_POST['_id']);
    // $blogId = get_current_blog_id();
    $instanceID = get_fields('options-dealer')['event_instance'];
    $blogId = !empty($instanceID) ? $instanceID : get_current_blog_id();

    
    $response = wp_remote_request('https://events.dealervolvo.pl/instance'. $blogId . '/api/collections/remove/pricing?token=4ca43516c3548033e78fa126f2ae9b', [
        'method'    => 'POST',  
        'body'      => json_encode([
            'collection' => 'pricing',
            'filter' => ['_id' => $eventId], 
        ]),  
        'headers'   => [
            'Content-Type' => 'application/json',  
        ]
    ]);
    

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

 
    if (isset($data['error'])) {
        wp_send_json_error(['message' => $data['error']]);
    }


    wp_send_json_success(['message' => __('Event deleted successfully.', 'partners-site_v2')]);
}


    
    // lista eventow


    public function renderPage()
{
   
    echo '<div class="wrap"><h1>' . esc_html__('Events', 'partners-site_v2') . '</h1>';

    $data = $this->fetchExternalEvents();
    if (!$data || empty($data['entries']) && !isset($_GET['add'])) { 
        echo '<p>' . esc_html__('No events to display.', 'partners-site_v2') . '</p>';
        echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=eventy&add=new')) . '">' . esc_html__('Add new', 'partners-site_v2') . '</a></p>';
        // return;
    }
   
   
    if (!empty($_GET['edit']) || !empty($_GET['add'])) {
        

        if (!empty($_GET['add'])) {
            $event = [
                '_id' => '',
                'name' => '',
                'second_name' => '',
                'status' => false,
                'private' => false,
                'interval' => '',
                'sms' => '',
                'description' => '',
                'opening_hours' => ['open' => ''],
                'image' => ['path' => '']
            ];
            echo '<h2>' . esc_html__('You are adding a new event', 'partners-site_v2') . '</h2>';
        } else {
            $editId = sanitize_text_field($_GET['edit']);
            $event = null;
            foreach ($data['entries'] as $e) {
                if (!empty($e['_id']) && $e['_id'] === $editId) {
                    $event = $e;
                    break;
                }
            }
            if (!$event) {
                echo '<p>' . esc_html__('No event found with ID', 'partners-site_v2') . ': ' . esc_html($editId) . '</p></div>';
                return;
            }
            $defaultFields = [
                'status' => false,
                'private' => false,
                'sms' => '',
                'description' => '',
                'interval' => '',
                'opening_hours' => ['open' => ''],
                'image' => ['path' => '']
            ];
            foreach ($defaultFields as $key => $defaultValue) {
                if (!isset($event[$key])) {
                    $event[$key] = $defaultValue;
                }
            }
            echo '<h2>' . esc_html__('You are editing the event with ID', 'partners-site_v2') . ': <code>' . esc_html($event['_id']) . '</code></h2>';
        }

        echo '<form id="event-edit-form" method="post" action="">';
        echo '<input type="hidden" name="event_id" value="' . esc_attr($event['_id']) . '" />';

        echo '<table class="form-table">';
        $allowedFields = [
            'name',
            'second_name',
            'status',
            'private',
            'interval',
            'sizing',
            'sms',
            'opening_hours',
            'image',
            'description'
        ];

        foreach ($allowedFields as $col) {
            $value = $event[$col];
            echo '<tr>';
        
            switch ($col) {
                case 'name':
                    $label = __('Event name', 'partners-site_v2');
                    break;
                case 'second_name':
                    $label = __('Alternative name', 'partners-site_v2');
                    break;
                case 'private':
                    $label = __('Private event', 'partners-site_v2');
                    break;
                case 'sizing':
                    $label = __('Number of tickets', 'partners-site_v2');
                    break;
                case 'sms':
                    $label = __('Confirmation SMS text', 'partners-site_v2');
                    break;
                case 'opening_hours':
                    $label = __('Event time', 'partners-site_v2');
                    break;
                case 'description':
                    $label = __('Description', 'partners-site_v2');
                    break;
                case 'interval':

                    echo '<tr style="display:none;">';
                    $label = '';
                    break;
                case 'image':
                    $label = __('Image', 'partners-site_v2');
                    break;
                default:
                    $label = $col; 
            }
        
            if ($col !== 'interval') {
                echo '<th><label for="' . esc_attr($col) . '">' . esc_html($label) . '</label></th>';
            }
        
            echo '<td>';

            switch ($col) {
                case 'name':
                case 'second_name':
                case 'interval':
                    echo '<input type="text" name="' . esc_attr($col) . '" id="' . esc_attr($col) . '" value="' . esc_attr($value) . '" class="regular-text" />';
                    break;

                case 'status':
                case 'private':
                    $checked = !empty($value) ? 'checked' : '';
                    echo '<input type="checkbox" name="' . esc_attr($col) . '" id="' . esc_attr($col) . '" ' . $checked . ' />';
                    break;

                case 'sizing':
                    echo '<input type="text" name="' . esc_attr($col) . '" id="' . esc_attr($col) . '" value="' . esc_attr($value) . '" class="regular-text" />';
                break;
                    

                case 'sms':
                case 'description':
                    echo '<textarea name="' . esc_attr($col) . '" id="' . esc_attr($col) . '" style="width:400px; height:100px;">' . esc_textarea($value) . '</textarea>';
                    break;

                case 'opening_hours':
                    $val = !empty($value['open']) ? esc_attr($value['open']) : '';
                    echo '<input type="text" name="' . esc_attr($col) . '[open]" value="' . $val . '" placeholder="Godzina otwarcia (np. 08:00)" />';
                    break;

                    case 'image':
                        $imgVal = !empty($value['path']) ? trim($value['path']) : '';
                    
                        if ($imgVal) {
                            
                            $imgVal = preg_replace('#https?://events\.dealervolvo\.pl/(https?://)#i', '$1', $imgVal);
                    
                            if (!preg_match('#^https?://#i', $imgVal)) {
                           
                                if (strpos($imgVal, '/app/uploads/') === 0) {
                                   
                                    $imgVal = 'https://main.volvocars-partner.pl' . $imgVal;
                                } else {
                               
                                    $imgVal = 'https://events.dealervolvo.pl/' . ltrim($imgVal, '/');
                                }
                            } else {
                          
                                if (strpos($imgVal, 'https://events.dealervolvo.pl/') === false && strpos($imgVal, 'https://main.volvocars-partner.pl/') === false) {
                               
                                    $imgVal = 'https://events.dealervolvo.pl/' . ltrim($imgVal, '/');
                                }
                            }
                        }
                    
            
                        $inputId = 'event-image-' . esc_attr($event['_id']);
                    
                        echo '<input type="text" id="' . $inputId . '" name="' . esc_attr($col) . '" value="' . esc_attr($imgVal) . '" class="regular-text" />';
                    
                        echo ' <button class="button select-image-button" data-input-id="' . $inputId . '">' . esc_html__('Select an image', 'partners-site_v2') . '</button>';
                    
                        echo '<br><img src="' . esc_url($imgVal) . '" style="max-width:150px; max-height:150px; object-fit:cover; margin-top:10px;" />';
                    
                    break;
                    
                    
                    
            }

            echo '</td></tr>';
        }

        echo '</table>';
        submit_button(esc_html__('Save changes', 'partners-site_v2'));
        echo '</form>';

        echo '<p><a href="' . esc_url(admin_url('admin.php?page=eventy')) . '">&larr; ' . esc_html__('Back to list', 'partners-site_v2') . '</a></p>';
        echo '</div>';
        return;
    }

 
$columns = [
    'name' => __('Event name', 'partners-site_v2'),
    'second_name' => __('Alternative name', 'partners-site_v2'),
    'image' => __('Image', 'partners-site_v2'),
  
];

echo '<table class="wp-list-table widefat fixed striped">';
echo '<thead><tr>';
echo '<th style="width:50px; text-align:center;">#</th>'; 
foreach ($columns as $colKey => $colLabel) {
    echo '<th>' . esc_html($colLabel) . '</th>';
}
echo '<th style="width:60px; text-align:center;">' . esc_html__('Status', 'partners-site_v2') . '</th>';  
echo '<th style="width:60px; text-align:center;">' . esc_html__('Edit', 'partners-site_v2') . '</th>';  
echo '<th style="width:60px; text-align:center;">' . esc_html__('Delete', 'partners-site_v2') . '</th>';    
echo '</tr></thead><tbody>';

$index = 1;
foreach ($data['entries'] as $event) {
    echo '<tr>';
    echo '<td style="width:50px; text-align:center;">' . $index++ . '</td>';

    foreach ($columns as $colKey => $colLabel) {
        $value = $event[$colKey] ?? '-';
        if ($colKey === 'image' && !empty($value['path'])) {
            $imgUrl = $value['path'];
            if (!preg_match('#^https?://#i', $imgUrl)) {
                $imgUrl = 'https://events.dealervolvo.pl/' . ltrim($imgUrl, '/');
            }
            echo '<td><img src="' . esc_url($imgUrl) . '" style="max-width:100px; max-height:60px; object-fit:cover;" /></td>';
        } else {
            echo '<td>' . (is_array($value) ? esc_html(json_encode($value)) : esc_html($value)) . '</td>';
        }
    }

    $statusColor = !empty($event['status']) ? 'green' : 'red';
    echo '<td style="width:60px; text-align:center;">
            <div style="width:15px; height:15px; border-radius:50%; background-color:' . $statusColor . '; margin:0 auto;"></div>
        </td>';

    $id = $event['_id'] ?? '';
    echo '<td style="width:60px; text-align:center;">';
    echo '<a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=eventy&edit=' . $id)) . '">' . esc_html__('Edit', 'partners-site_v2') . '</a>';
    echo '</td>';

    echo '<td style="width:60px; text-align:center;">';
    echo '<a class="button button-danger" href="#" data-event-id="' . $event['_id'] . '">' . esc_html__('Delete', 'partners-site_v2') . '</a>';
    echo '</td>';

    echo '</tr>';
}
echo '</tbody></table>';



    echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=eventy&add=new')) . '">' . esc_html__('Add new', 'partners-site_v2') . '</a></p>';
    echo '</div>';
}


//  spis uzytkownikow

public function renderParticipantsPage() {
    echo '<div class="wrap"><h1>' . __( 'List of participants', 'partners-site_v2') . '</h1>';

    $selectedEvent = $_GET['filter_event'] ?? '';

    $eventsData = $this->fetchExternalEvents();
    $eventsMap = [];
    $eventsSizing = [];
    if (!empty($eventsData['entries'])) {
        foreach ($eventsData['entries'] as $event) {
            $eventsMap[$event['_id']] = $event['name'] ?? json_encode(__('(no name)', 'partners-site_v2'));
            $eventsSizing[$event['_id']] = $event['sizing']['name'] ?? null;
        }
    }

    // $blogId = get_current_blog_id();
    $instanceID = get_fields('options-dealer')['event_instance'];
    $blogId = !empty($instanceID) ? $instanceID : get_current_blog_id();

    $response = wp_remote_get('https://events.dealervolvo.pl/instance' . $blogId . '/api/collections/get/events?token=4ca43516c3548033e78fa126f2ae9b');

    if (is_wp_error($response)) {
        echo '<p>' . esc_html__('API connection error', 'partners-site_v2') . ': ' . esc_html($response->get_error_message()) . '</p></div>';
        return;
    }
    $body = wp_remote_retrieve_body($response);
    $participants = json_decode($body, true)['entries'] ?? [];


    if ($selectedEvent) {
        $participants = array_filter($participants, function($p) use ($selectedEvent) {
            return isset($p['type']['_id']) && $p['type']['_id'] === $selectedEvent;
        });
    }

 
    $totalParticipants = count($participants);

    if ($selectedEvent) {
        echo '<p>' . esc_html__('Number of participants registered for the event', 'partners-site_v2') . ' <strong>' . esc_html($eventsMap[$selectedEvent]) . '</strong>: ' . $totalParticipants . '</p>';

        if (isset($eventsSizing[$selectedEvent])) {
            $available = intval($eventsSizing[$selectedEvent]);
            $color = ($totalParticipants < $available) ? 'green' : 'red';
            echo '<p><strong>' . esc_html__('Available spots', 'partners-site_v2') . ':</strong> <span style="color:' . $color . ';">' . $available . '</span></p>';
        }
    } else {
        echo '<p>' . esc_html__('Total number of participants', 'partners-site_v2') . ': ' . $totalParticipants . '</p>';
    }
    echo '<form method="get" style="margin-bottom:20px;">';
    echo '<input type="hidden" name="page" value="eventy-lista-uczestnikow" />';
    echo '<label for="filter_event">' . esc_html__('Filter by event', 'partners-site_v2') . ': </label>';
    echo '<select name="filter_event" id="filter_event">';
    echo '<option value="">-- ' . esc_html__('All', 'partners-site_v2') . ' --</option>';
    foreach ($eventsMap as $eventId => $eventName) {
        $selected = ($selectedEvent === $eventId) ? 'selected' : '';
        echo '<option value="' . esc_attr($eventId) . '" ' . $selected . '>' . esc_html($eventName) . '</option>';
    }
    echo '</select>';
    echo ' <input type="submit" class="button" value="' . esc_attr__('Filter', 'partners-site_v2') . '" />';
    echo '</form>';

    echo '<form method="post" action="' . admin_url('admin-post.php') . '" style="margin-bottom:10px;">';
    echo '<input type="hidden" name="action" value="export_events_csv">';
    echo '<input type="hidden" name="filter_event" value="' . esc_attr($selectedEvent) . '">';
    echo '<input type="submit" class="button button-primary" value="' . esc_attr__('Export CSV', 'partners-site_v2') . '">';
    echo '</form>';

    $columns = [
        'name' => __('First and last name', 'partners-site_v2'),
        'phone' => __('Phone', 'partners-site_v2'),
        'email' => __('Email', 'partners-site_v2'),
        'unique_id' => __('Participant ID', 'partners-site_v2'),
        'type' => __('Event', 'partners-site_v2'),
        'car' => __('Car', 'partners-site_v2'),
        'place' => __('Location', 'partners-site_v2'),
        'tax' => __('Tax', 'partners-site_v2'),
        'agree_1' => __('Consent 1', 'partners-site_v2'),
        'agree_2' => __('Consent 2', 'partners-site_v2'),
        'agree_3' => __('Consent 3', 'partners-site_v2'),
        'ticket_status' => __('Ticket status', 'partners-site_v2'),
        '_created' => '', 
        '_modified' => '', 
        '_id' => '', 
        'usun' => __('Delete', 'partners-site_v2')
    ];
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    foreach ($columns as $key => $label) {
        if ($label !== '') {
            echo '<th>' . esc_html($label) . '</th>';
        }
    }
    echo '</tr></thead><tbody>';
    
    foreach ($participants as $entry) {
        echo '<tr>';
        foreach ($columns as $key => $label) {
            if ($label === '') continue; 
            $value = $entry[$key] ?? '';
    
            if ($key === 'type') {
                $value = (!empty($value['_id']) && isset($eventsMap[$value['_id']])) ? $eventsMap[$value['_id']] : json_encode(__('(no associated event)', 'partners-site_v2'));
            }
    
            if ($key === 'usun') {
                $id = $entry['_id'] ?? '';
                $value = '<a class="button button-danger delete-participant" href="#" data-participant-id="' . esc_attr($id) . '">' . esc_html__('Delete', 'partners-site_v2') . '</a>';
            }
    
            if (in_array($key, ['agree_1','agree_2','agree_3','ticket_status'])) {
                $value = $value ? '✔️' : '❌';
            }
    
            echo '<td>' . ($key === 'usun' ? $value : esc_html(is_array($value) ? json_encode($value) : $value)) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
    
    echo "<script>
    jQuery(document).ready(function($) {
        $(document).on('click', '.delete-participant', function(e) {
            e.preventDefault();
            var participantId = $(this).data('participant-id');
            if (!confirm(" . json_encode(__('Are you sure you want to delete this participant?', 'partners-site_v2')) . ")) return;

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'participants_remove',
                    _id: participantId
                },
                success: function(response) {
                    if (response.success) {
                        alert(" . json_encode(__('Participant deleted!', 'partners-site_v2')) . ");
                        location.reload();
                    } else {
                        alert(" . json_encode(__('Error', 'partners-site_v2')) . " + ': ' + response.data.message);
                    }
                },
                error: function(xhr) {
                    console.error('Błąd AJAX: ', xhr);
                    alert(" . json_encode(__('An error occurred while deleting the participant.', 'partners-site_v2')) . ");
                }
            });
        });
    });
    </script>";

    echo '</div>';
}




//    Eksport CSV

public function exportEventsCsv() {
    if (!current_user_can('manage_options')) wp_die(esc_html__('No permissions to export data.', 'partners-site_v2'));

    $selectedEvent = $_POST['filter_event'] ?? '';

    // $blogId = get_current_blog_id(); 
    $instanceID = get_fields('options-dealer')['event_instance'];
    $blogId = !empty($instanceID) ? $instanceID : get_current_blog_id();

    $response = wp_remote_get('https://events.dealervolvo.pl/instance/' . $blogId . '/api/collections/get/events?token=4ca43516c3548033e78fa126f2ae9b');
    if (is_wp_error($response)) wp_die(esc_html__('API connection error', 'partners-site_v2') . ': ' . $response->get_error_message());
    $body = wp_remote_retrieve_body($response);
    $participants = json_decode($body, true)['entries'] ?? [];

    $eventsData = $this->fetchExternalEvents();
    $eventsMap = [];
    if (!empty($eventsData['entries'])) {
        foreach ($eventsData['entries'] as $event) {
            $eventsMap[$event['_id']] = $event['name'] ?? json_encode(__('(no name)', 'partners-site_v2'));
        }
    }

    if ($selectedEvent) {
        $participants = array_filter($participants, fn($p) => isset($p['type']['_id']) && $p['type']['_id'] === $selectedEvent);
        $eventName = isset($eventsMap[$selectedEvent]) ? sanitize_title($eventsMap[$selectedEvent]) : json_encode(__('No name', 'partners-site_v2'));
    } else {
        $eventName = 'wszystkie';
    }

    $csvColumns = ['name','second_name','phone','email','type','car','place','tax','agree_1','agree_2','agree_3','ticket_status','sms','opening_hours','description'];

    $csvLabels = [
        'name' => __('Event name', 'partners-site_v2'),
        'second_name' => __('Alternative name', 'partners-site_v2'),
        'phone' => __('Phone', 'partners-site_v2'),
        'email' => __('Email', 'partners-site_v2'),
        'type' => __('Event', 'partners-site_v2'),
        'car' => __('Car', 'partners-site_v2'),
        'place' => __('Location', 'partners-site_v2'),
        'tax' => __('Tax', 'partners-site_v2'),
        'agree_1' => __('Consent 1', 'partners-site_v2'),
        'agree_2' => __('Consent 2', 'partners-site_v2'),
        'agree_3' => __('Consent 3', 'partners-site_v2'),
        'ticket_status' => __('Ticket status', 'partners-site_v2'),
        'sms' => __('SMS content', 'partners-site_v2'),
        'opening_hours' => __('Event time', 'partners-site_v2'),
        'description' => __('Description', 'partners-site_v2'),
    ];

    $resource = fopen('php://memory', 'w');
    fputcsv($resource, array_map(fn($col) => $csvLabels[$col] ?? $col, $csvColumns), ';');

    foreach ($participants as $p) {
        $row = [];
        foreach ($csvColumns as $col) {
            $value = $p[$col] ?? '';

            if ($col === 'type' && !empty($value['_id']) && isset($eventsMap[$value['_id']])) $value = $eventsMap[$value['_id']];
            if (in_array($col, ['agree_1','agree_2','agree_3','ticket_status'])) $value = $value ? 1 : 0;
            if (is_array($value)) $value = json_encode($value);

            $row[] = $value;
        }
        fputcsv($resource, $row, ';');
    }

    rewind($resource);

    $filename = 'uczestnicy_' . $eventName . '_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    fpassthru($resource);
    fclose($resource);
    exit;
}

//  usuwanie uczestnika 

public function ajaxDeleteParticipant() {
    if (!isset($_POST['_id'])) {
        wp_send_json_error(['message' => __('No participant ID', 'partners-site_v2')]);
    }

    $participantId = sanitize_text_field($_POST['_id']);

    // $blogId = get_current_blog_id(); 
    
    $instanceID = get_fields('options-dealer')['event_instance'];
    $blogId = !empty($instanceID) ? $instanceID : get_current_blog_id();

    $response = wp_remote_request(
        'https://events.dealervolvo.pl/instance/'. $blogId .'/api/collections/remove/events?token=4ca43516c3548033e78fa126f2ae9b',
        [
            'method'  => 'POST',
            'body'    => json_encode([
                'collection' => 'events',
                'filter' => ['_id' => $participantId]
            ]),
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]
    );

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['error'])) {
        wp_send_json_error(['message' => $data['error']]);
    }

    wp_send_json_success(['message' => __('Participant deleted successfully.', 'partners-site_v2')]);
}  
}
