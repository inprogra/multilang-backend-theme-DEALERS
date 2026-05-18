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
            'Eventy',
            'Eventy',
            'manage_options',
            'eventy', 
            [$this, 'renderPage']
        );
        add_submenu_page(
            'eventy', 
            'Lista uczestników', 
            'Lista uczestników',
            'manage_options',
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
            var confirmDelete = confirm('Czy na pewno chcesz usunąć ten event?');
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
                        alert('Event usunięty!');
                        location.reload(); 
                    } else {
                        alert('Błąd: ' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Błąd AJAX: ', xhr, status, error);  
                    alert('Wystąpił błąd podczas usuwania eventu.');
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
                title: 'Wybierz obrazek',
                button: { text: 'Wstaw obrazek' },
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
                $('<p class=\"validation-message\">Pole nazwa jest wymagane.</p>').insertAfter(nameField);
                valid = false;
            }

            var imageField = form.find('input[name=\"image\"]');
            if (!imageField.val().trim()) {
                imageField.addClass('validation-error');
                $('<p class=\"validation-message\">Pole obrazek jest wymagane.</p>').insertAfter(imageField);
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

            // edycja wydarzeń
            var blogId = eventyParams.blogId;

            $.ajax({
                url: 'https://events.dealervolvo.pl/instance' + blogId + '/api/collections/save/pricing?token=4ca43516c3548033e78fa126f2ae9b', 
                type: 'POST',
                data: JSON.stringify({ data: [ payload ] }),
                contentType: 'application/json',
                success: function(response) {
                    $('<div class=\"notice notice-success\"><p>Zmiany zapisane poprawnie!</p></div>').insertBefore('.wrap h1');
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

                    $('<div class=\"notice notice-error\"><p>Błąd podczas zapisu: ' + msg + '</p></div>').insertBefore('.wrap h1');
                }
            });
        });

    });
    ");
}


    // spis eventow
    public function fetchExternalEvents()
{
    $blogId=get_current_blog_id();
    
    $response = wp_remote_get('https://events.dealervolvo.pl/api/getEvents/instance' . $blogId . '/', [
        'headers' => [
            'api-key' => '*~(Yrp85T:.XSF/!%A:{c-c(kz:y-Q' 
        ]
    ]);
    

        if (is_wp_error($response)) return ['error' => $response->get_error_message()];

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data ?: ['error' => 'Nie udało się sparsować JSON'];
    }

    public function ajaxDeleteEvent()
{

    if (!isset($_POST['_id'])) {
        wp_send_json_error(['message' => 'Brak ID eventu']);
    }

    $eventId = sanitize_text_field($_POST['_id']);
    $blogId = get_current_blog_id();

    
    $response = wp_remote_request('https://events.dealervolvo.pl/instance/'. $blogId . '/api/collections/remove/pricing?token=4ca43516c3548033e78fa126f2ae9b', [
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


    wp_send_json_success(['message' => 'Event usunięty poprawnie.']);
}


    
    // lista eventow


    public function renderPage()
{
    echo '<div class="wrap"><h1>Eventy</h1>';

    $data = $this->fetchExternalEvents();
    if (!$data || empty($data['entries'])) {
        echo '<p>Brak eventów do wyświetlenia.</p>';
        echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=eventy&add=new')) . '">Dodaj nowy</a></p></div>';
        return;
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
            echo '<h2>Dodajesz nowy event</h2>';
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
                echo '<p>Nie znaleziono eventu o ID: ' . esc_html($editId) . '</p></div>';
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
            echo '<h2>Edytujesz event o ID: <code>' . esc_html($event['_id']) . '</code></h2>';
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
                    $label = 'Nazwa wydarzenia';
                    break;
                case 'second_name':
                    $label = 'Dodatkowa nazwa';
                    break;
                case 'private':
                    $label = 'Wydarzenie prywatne';
                    break;
                case 'sizing':
                    $label = 'Ilość biletów';
                    break;
                case 'sms':
                    $label = 'Treść sms z potwierdzeniem';
                    break;
                case 'opening_hours':
                    $label = 'Godzina wydarzenia';
                    break;
                case 'description':
                    $label = 'Opis';
                    break;
                case 'interval':

                    echo '<tr style="display:none;">';
                    $label = '';
                    break;
                case 'image':
                    $label = 'Obrazek';
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
                        $imgVal = !empty($value['path']) ? esc_attr($value['path']) : '';
                    
               
                        if ($imgVal && !preg_match('#^https?://#i', $imgVal)) {
                            $imgVal = 'https://events.dealervolvo.pl/' . ltrim($imgVal, '/');
                        }
                    
                        $inputId = 'event-image-' . esc_attr($event['_id']); 
                        echo '<input type="text" id="' . $inputId . '" name="' . esc_attr($col) . '" value="' . $imgVal . '" class="regular-text" />';
                        echo ' <button class="button select-image-button" data-input-id="' . $inputId . '">Wybierz obrazek</button>';

                    
                 
                        if ($imgVal) {
                            echo '<br><img src="' . esc_url($imgVal) . '" style="max-width:150px; max-height:150px; object-fit:cover; margin-top:10px;" />';
                        } else {
                  
                            echo '<br><img src="" style="max-width:150px; max-height:150px; object-fit:cover; margin-top:10px;" />';
                        }
                        break;
                    
            }

            echo '</td></tr>';
        }

        echo '</table>';
        submit_button('Zapisz zmiany');
        echo '</form>';

        echo '<p><a href="' . esc_url(admin_url('admin.php?page=eventy')) . '">&larr; Powrót do listy</a></p>';
        echo '</div>';
        return;
    }

 
$columns = [
    'name' => 'Nazwa wydarzenia',
    'second_name' => 'Dodatkowa nazwa',
    'image' => 'Obrazek',
  
];

echo '<table class="wp-list-table widefat fixed striped">';
echo '<thead><tr>';
echo '<th style="width:50px; text-align:center;">#</th>'; 
foreach ($columns as $colKey => $colLabel) {
    echo '<th>' . esc_html($colLabel) . '</th>';
}
echo '<th style="width:60px; text-align:center;">Status</th>';  
echo '<th style="width:60px; text-align:center;">Edytuj</th>';  
echo '<th style="width:60px; text-align:center;">Usuń</th>';    
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
    echo '<a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=eventy&edit=' . $id)) . '">Edytuj</a>';
    echo '</td>';

    echo '<td style="width:60px; text-align:center;">';
    echo '<a class="button button-danger" href="#" data-event-id="' . $event['_id'] . '">Usuń</a>';
    echo '</td>';

    echo '</tr>';
}
echo '</tbody></table>';



    echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=eventy&add=new')) . '">Dodaj nowy</a></p>';
    echo '</div>';
}


//  spis uzytkownikow

public function renderParticipantsPage() {
    echo '<div class="wrap"><h1>Lista uczestników</h1>';

    $selectedEvent = $_GET['filter_event'] ?? '';

    $eventsData = $this->fetchExternalEvents();
    $eventsMap = [];
    $eventsSizing = [];
    if (!empty($eventsData['entries'])) {
        foreach ($eventsData['entries'] as $event) {
            $eventsMap[$event['_id']] = $event['name'] ?? '(brak nazwy)';
            $eventsSizing[$event['_id']] = $event['sizing']['name'] ?? null;
        }
    }

    $blogId = get_current_blog_id();
    $response = wp_remote_get('https://events.dealervolvo.pl/instance/' . $blogId . '/api/collections/get/events?token=4ca43516c3548033e78fa126f2ae9b');

    if (is_wp_error($response)) {
        echo '<p>Błąd połączenia z API: ' . esc_html($response->get_error_message()) . '</p></div>';
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
        echo '<p>Liczba uczestników zapisanych na wydarzenie <strong>' . esc_html($eventsMap[$selectedEvent]) . '</strong>: ' . $totalParticipants . '</p>';

        if (isset($eventsSizing[$selectedEvent])) {
            $available = intval($eventsSizing[$selectedEvent]);
            $color = ($totalParticipants < $available) ? 'green' : 'red';
            echo '<p><strong>Dostępne miejsca:</strong> <span style="color:' . $color . ';">' . $available . '</span></p>';
        }
    } else {
        echo '<p>Łączna liczba uczestników: ' . $totalParticipants . '</p>';
    }
    echo '<form method="get" style="margin-bottom:20px;">';
    echo '<input type="hidden" name="page" value="eventy-lista-uczestnikow" />';
    echo '<label for="filter_event">Filtruj według eventu: </label>';
    echo '<select name="filter_event" id="filter_event">';
    echo '<option value="">-- Wszystkie --</option>';
    foreach ($eventsMap as $eventId => $eventName) {
        $selected = ($selectedEvent === $eventId) ? 'selected' : '';
        echo '<option value="' . esc_attr($eventId) . '" ' . $selected . '>' . esc_html($eventName) . '</option>';
    }
    echo '</select>';
    echo ' <input type="submit" class="button" value="Filtruj" />';
    echo '</form>';

    echo '<form method="post" action="' . admin_url('admin-post.php') . '" style="margin-bottom:10px;">';
    echo '<input type="hidden" name="action" value="export_events_csv">';
    echo '<input type="hidden" name="filter_event" value="' . esc_attr($selectedEvent) . '">';
    echo '<input type="submit" class="button button-primary" value="Eksportuj CSV">';
    echo '</form>';

    $columns = [
        'name' => 'Imię i nazwisko',
        'phone' => 'Telefon',
        'email' => 'E-mail',
        'unique_id' => 'ID uczestnika',
        'type' => 'Wydarzenie',
        'car' => 'Samochód',
        'place' => 'Miejsce',
        'tax' => 'Podatek',
        'agree_1' => 'Zgoda 1',
        'agree_2' => 'Zgoda 2',
        'agree_3' => 'Zgoda 3',
        'ticket_status' => 'Status biletu',
        '_created' => '', 
        '_modified' => '', 
        '_id' => '', 
        'usun' => 'Usuń'
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
                $value = (!empty($value['_id']) && isset($eventsMap[$value['_id']])) ? $eventsMap[$value['_id']] : '(brak powiązanego eventu)';
            }
    
            if ($key === 'usun') {
                $id = $entry['_id'] ?? '';
                $value = '<a class="button button-danger delete-participant" href="#" data-participant-id="' . esc_attr($id) . '">Usuń</a>';
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
            if (!confirm('Czy na pewno chcesz usunąć tego uczestnika?')) return;

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'participants_remove',
                    _id: participantId
                },
                success: function(response) {
                    if (response.success) {
                        alert('Uczestnik usunięty!');
                        location.reload();
                    } else {
                        alert('Błąd: ' + response.data.message);
                    }
                },
                error: function(xhr) {
                    console.error('Błąd AJAX: ', xhr);
                    alert('Wystąpił błąd podczas usuwania uczestnika.');
                }
            });
        });
    });
    </script>";

    echo '</div>';
}




//    Eksport CSV

public function exportEventsCsv() {
    if (!current_user_can('manage_options')) wp_die('Brak uprawnień do eksportu danych.');

    $selectedEvent = $_POST['filter_event'] ?? '';

    $blogId = get_current_blog_id(); 

    $response = wp_remote_get('https://events.dealervolvo.pl/instance/' . $blogId . '/api/collections/get/events?token=4ca43516c3548033e78fa126f2ae9b');
    if (is_wp_error($response)) wp_die('Błąd połączenia z API: ' . $response->get_error_message());
    $body = wp_remote_retrieve_body($response);
    $participants = json_decode($body, true)['entries'] ?? [];

    $eventsData = $this->fetchExternalEvents();
    $eventsMap = [];
    if (!empty($eventsData['entries'])) {
        foreach ($eventsData['entries'] as $event) {
            $eventsMap[$event['_id']] = $event['name'] ?? '(brak nazwy)';
        }
    }

    if ($selectedEvent) {
        $participants = array_filter($participants, fn($p) => isset($p['type']['_id']) && $p['type']['_id'] === $selectedEvent);
        $eventName = isset($eventsMap[$selectedEvent]) ? sanitize_title($eventsMap[$selectedEvent]) : 'brak_nazwy';
    } else {
        $eventName = 'wszystkie';
    }

    $csvColumns = ['name','second_name','phone','email','type','car','place','tax','agree_1','agree_2','agree_3','ticket_status','sms','opening_hours','description'];

    $csvLabels = [
        'name' => 'Nazwa wydarzenia',
        'second_name' => 'Dodatkowa nazwa',
        'phone' => 'Telefon',
        'email' => 'E-mail',
        'type' => 'Wydarzenie',
        'car' => 'Samochód',
        'place' => 'Miejsce',
        'tax' => 'Podatek',
        'agree_1' => 'Zgoda 1',
        'agree_2' => 'Zgoda 2',
        'agree_3' => 'Zgoda 3',
        'ticket_status' => 'Status biletu',
        'sms' => 'Treść SMS',
        'opening_hours' => 'Godzina wydarzenia',
        'description' => 'Opis',
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
        wp_send_json_error(['message' => 'Brak ID uczestnika']);
    }

    $participantId = sanitize_text_field($_POST['_id']);

    $blogId = get_current_blog_id(); 

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

    wp_send_json_success(['message' => 'Uczestnik usunięty poprawnie.']);
}  
}
