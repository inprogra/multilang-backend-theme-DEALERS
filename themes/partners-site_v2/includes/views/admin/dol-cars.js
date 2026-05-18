jQuery(document).ready(function($) {
    var carsData = {};

    function renderCarsTable(data) {
        var $tbody = $('#dol-cars-tbody');
        var $message = $('#dol-cars-message');
        var cars = data.cars;
        var total = data.total;
        var dolCount = data.dol_count;
        var usedCount = data.used_count;

        carsData = {};

        if (total === 0) {
            $tbody.html('<tr><td colspan="9">Brak samochodów dostępnych dla FIND CAR lub OTOMOTO.</td></tr>');
            $message.html('');
            return;
        }

        var html = '';
        cars.forEach(function(car, index) {
            var carKey = 'car_' + index;
            carsData[carKey] = car;

            var source = car.source || 'DOL';
            var findcarAvailable = false;
            var otomotoAvailable = false;
            var findcarSent = car.findcar_sent || false;
            var findcarStatus = car.findcar_status || null;
            var otomotoSent = car.otomoto_sent || false;
            var otomotoStatus = car.otomoto_status || null;
            var model, version, color, vin, year;

            if (source === 'DOL') {
                var carData = car.carData || car;
                model = carData.model || '';
                version = carData.version || '';
                color = carData.color || '';
                vin = car.id || '';
                year = carData.productionYear || carData.year || '';

                if (car.exposes) {
                    car.exposes.forEach(function(expose) {
                        if (expose.platform === 'FIND_CAR' && expose.value === true) {
                            findcarAvailable = true;
                        }
                        if (expose.platform === 'OTOMOTO' && expose.value === true) {
                            otomotoAvailable = true;
                        }
                    });
                }
            } else {
                model = car.model || '';
                version = car.version || '';
                color = car.color || '';
                vin = car.vin || '';
                year = car.year || '';
                findcarAvailable = car.findcar_available || false;
                otomotoAvailable = car.otomoto_available || false;
            }

            html += '<tr>';
            html += '<td><strong>' + source + '</strong></td>';
            html += '<td>' + model + '</td>';
            html += '<td>' + version + '</td>';
            html += '<td>' + color + '</td>';
            html += '<td>' + vin + '</td>';
            html += '<td>' + year + '</td>';
            var findcarReason = '';
            var otomotoReason = '';

            if (!findcarAvailable) {
                if (source === 'DOL') {
                    findcarReason = 'Nie udostępniony dla FIND CAR w DOL';
                } else {
                    findcarReason = 'Status FIND CAR nie jest aktywny lub oczekujący';
                }
            }

            if (!otomotoAvailable) {
                if (source === 'DOL') {
                    otomotoReason = 'Nie udostępniony dla OTOMOTO w DOL';
                } else {
                    otomotoReason = 'OTOMOTO wyłączone w ustawieniach dealera';
                }
            }

            var findcarStatusHtml = '';
            if (findcarSent) {
                if (findcarStatus === 'inactive') {
                    findcarStatusHtml = '<span style="color:orange" title="Zatrzymana">⏸</span>';
                } else {
                    findcarStatusHtml = '<span style="color:green" title="Aktywna">✓</span>';
                }
            } else {
                findcarStatusHtml = '<span style="color:red" title="' + findcarReason + '">✗</span>';
            }

            var otomotoStatusHtml = '';
            if (otomotoSent) {
                if (otomotoStatus === 'inactive') {
                    otomotoStatusHtml = '<span style="color:orange" title="Zatrzymane">⏸</span>';
                } else {
                    otomotoStatusHtml = '<span style="color:green" title="Aktywne">✓</span>';
                }
            } else {
                otomotoStatusHtml = '<span style="color:red" title="' + otomotoReason + '">✗</span>';
            }

            html += '<td>' + findcarStatusHtml + '</td>';
            html += '<td>' + otomotoStatusHtml + '</td>';
            html += '<td>';

            if (findcarAvailable) {
                if (findcarSent) {
                    if (findcarStatus === 'inactive') {
                        html += '<button class="button button-small resume-findcar" data-car-key="' + carKey + '">Wznów FIND CAR</button> ';
                    } else {
                        html += '<button class="button button-small stop-findcar" data-car-key="' + carKey + '">Zatrzymaj FIND CAR</button> ';
                    }
                    if (car.findcar_listing_url) {
                        html += '<a href="' + car.findcar_listing_url + '" target="_blank" class="button button-small">Zobacz</a> ';
                    }
                } else {
                    html += '<button class="button button-small send-to-findcar" data-car-key="' + carKey + '">Wyślij do FIND CAR</button> ';
                }
            }
            if (otomotoAvailable) {
                if (otomotoSent) {
                    if (otomotoStatus === 'inactive') {
                        html += '<button class="button button-small resume-otomoto" data-car-key="' + carKey + '">Wznów OTOMOTO</button> ';
                    } else {
                        html += '<button class="button button-small stop-otomoto" data-car-key="' + carKey + '">Zatrzymaj OTOMOTO</button> ';
                    }
                    if (car.otomoto_advert_url) {
                        html += '<a href="' + car.otomoto_advert_url + '" target="_blank" class="button button-small">Zobacz</a>';
                    }
                } else {
                    html += '<button class="button button-small send-to-otomoto" data-car-key="' + carKey + '">Wyślij do OTOMOTO</button>';
                }
            }

            html += '</td>';
            html += '</tr>';
        });

        $tbody.html(html);
        $message.html('<div class="notice notice-success"><p>Załadowano ' + total + ' samochodów (DOL: ' + dolCount + ', Używane: ' + usedCount + ').</p></div>');
    }

    if (dolCarsAjax.initial_cars && dolCarsAjax.initial_cars.total > 0) {
        renderCarsTable(dolCarsAjax.initial_cars);
    }

    $('#refresh-dol-cars').on('click', function(e) {
        e.preventDefault();

        var $button = $(this);
        var $spinner = $('#dol-cars-spinner');
        var $message = $('#dol-cars-message');

        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        $message.html('');

        $.ajax({
            url: dolCarsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_dol_cars',
                nonce: dolCarsAjax.nonce
            },
            success: function(response) {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);

                if (response.success) {
                    renderCarsTable(response.data);
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                $message.html('<div class="notice notice-error"><p>Nie udało się pobrać samochodów.</p></div>');
            }
        });
    });

    $(document).on('click', '.send-to-findcar', function(e) {
        e.preventDefault();

        var $button = $(this);
        var carKey = $button.data('car-key');
        var carData = carsData[carKey];
        var $message = $('#dol-cars-message');

        if (!carData) {
            $message.html('<div class="notice notice-error"><p>Nie znaleziono danych samochodu.</p></div>');
            return;
        }

        $button.prop('disabled', true).text('Wysyłanie...');

        $.ajax({
            url: dolCarsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'send_dol_car_to_findcar',
                nonce: dolCarsAjax.nonce,
                car_data: JSON.stringify(carData)
            },
            success: function(response) {
                if (response.success) {
                    var msg = response.data.message;
                    if (response.data.listing_url) {
                        msg += ' <a href="' + response.data.listing_url + '" target="_blank">Zobacz ofertę</a>';
                    }
                    $message.html('<div class="notice notice-success"><p>' + msg + '</p></div>');
                    $button.replaceWith('<button class="button button-small stop-findcar" data-car-key="' + carKey + '">Zatrzymaj FIND CAR</button>');
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    $button.prop('disabled', false).text('Wyślij do FIND CAR');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>Nie udało się wysłać samochodu do FIND CAR.</p></div>');
                $button.prop('disabled', false).text('Wyślij do FIND CAR');
            }
        });
    });

    $(document).on('click', '.send-to-otomoto', function(e) {
        e.preventDefault();

        var $button = $(this);
        var carKey = $button.data('car-key');
        var carData = carsData[carKey];
        var $message = $('#dol-cars-message');

        if (!carData) {
            $message.html('<div class="notice notice-error"><p>Nie znaleziono danych samochodu.</p></div>');
            return;
        }

        $button.prop('disabled', true).text('Wysyłanie...');

        $.ajax({
            url: dolCarsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'send_dol_car_to_otomoto',
                nonce: dolCarsAjax.nonce,
                car_data: JSON.stringify(carData)
            },
            success: function(response) {
                if (response.success) {
                    var msg = response.data.message;
                    if (response.data.advert_url) {
                        msg += ' <a href="' + response.data.advert_url + '" target="_blank">Zobacz ogłoszenie</a>';
                    }
                    $message.html('<div class="notice notice-success"><p>' + msg + '</p></div>');
                    $button.replaceWith('<button class="button button-small stop-otomoto" data-car-key="' + carKey + '">Zatrzymaj OTOMOTO</button>');
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    $button.prop('disabled', false).text('Wyślij do OTOMOTO');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>Nie udało się wysłać samochodu do OTOMOTO.</p></div>');
                $button.prop('disabled', false).text('Wyślij do OTOMOTO');
            }
        });
    });

    $(document).on('click', '.stop-findcar', function(e) {
        e.preventDefault();

        var $button = $(this);
        var carKey = $button.data('car-key');
        var carData = carsData[carKey];
        var $message = $('#dol-cars-message');

        if (!carData || !carData.id) {
            $message.html('<div class="notice notice-error"><p>Nie znaleziono danych samochodu.</p></div>');
            return;
        }

        $button.prop('disabled', true).text('Zatrzymywanie...');

        $.ajax({
            url: dolCarsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'stop_dol_car_on_findcar',
                nonce: dolCarsAjax.nonce,
                car_id: carData.id
            },
            success: function(response) {
                if (response.success) {
                    $message.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    $button.replaceWith('<button class="button button-small resume-findcar" data-car-key="' + carKey + '">Wznów FIND CAR</button>');
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    $button.prop('disabled', false).text('Zatrzymaj FIND CAR');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>Nie udało się zatrzymać oferty w FIND CAR.</p></div>');
                $button.prop('disabled', false).text('Zatrzymaj FIND CAR');
            }
        });
    });

    $(document).on('click', '.resume-findcar', function(e) {
        e.preventDefault();

        var $button = $(this);
        var carKey = $button.data('car-key');
        var carData = carsData[carKey];
        var $message = $('#dol-cars-message');

        if (!carData || !carData.id) {
            $message.html('<div class="notice notice-error"><p>Nie znaleziono danych samochodu.</p></div>');
            return;
        }

        $button.prop('disabled', true).text('Wznawianie...');

        $.ajax({
            url: dolCarsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'resume_dol_car_on_findcar',
                nonce: dolCarsAjax.nonce,
                car_id: carData.id
            },
            success: function(response) {
                if (response.success) {
                    var msg = response.data.message;
                    if (response.data.listing_url) {
                        msg += ' <a href="' + response.data.listing_url + '" target="_blank">Zobacz ofertę</a>';
                    }
                    $message.html('<div class="notice notice-success"><p>' + msg + '</p></div>');
                    $button.replaceWith('<button class="button button-small stop-findcar" data-car-key="' + carKey + '">Zatrzymaj FIND CAR</button>');
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    $button.prop('disabled', false).text('Wznów FIND CAR');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>Nie udało się wznowić oferty w FIND CAR.</p></div>');
                $button.prop('disabled', false).text('Wznów FIND CAR');
            }
        });
    });

    $(document).on('click', '.stop-otomoto', function(e) {
        e.preventDefault();

        var $button = $(this);
        var carKey = $button.data('car-key');
        var carData = carsData[carKey];
        var $message = $('#dol-cars-message');

        if (!carData || !carData.id) {
            $message.html('<div class="notice notice-error"><p>Nie znaleziono danych samochodu.</p></div>');
            return;
        }

        $button.prop('disabled', true).text('Zatrzymywanie...');

        $.ajax({
            url: dolCarsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'stop_dol_car_on_otomoto',
                nonce: dolCarsAjax.nonce,
                car_id: carData.id
            },
            success: function(response) {
                if (response.success) {
                    $message.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    $button.replaceWith('<button class="button button-small resume-otomoto" data-car-key="' + carKey + '">Wznów OTOMOTO</button>');
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    $button.prop('disabled', false).text('Zatrzymaj OTOMOTO');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>Nie udało się zatrzymać ogłoszenia w OTOMOTO.</p></div>');
                $button.prop('disabled', false).text('Zatrzymaj OTOMOTO');
            }
        });
    });

    $(document).on('click', '.resume-otomoto', function(e) {
        e.preventDefault();

        var $button = $(this);
        var carKey = $button.data('car-key');
        var carData = carsData[carKey];
        var $message = $('#dol-cars-message');

        if (!carData || !carData.id) {
            $message.html('<div class="notice notice-error"><p>Nie znaleziono danych samochodu.</p></div>');
            return;
        }

        $button.prop('disabled', true).text('Wznawianie...');

        $.ajax({
            url: dolCarsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'resume_dol_car_on_otomoto',
                nonce: dolCarsAjax.nonce,
                car_id: carData.id
            },
            success: function(response) {
                if (response.success) {
                    var msg = response.data.message;
                    if (response.data.advert_url) {
                        msg += ' <a href="' + response.data.advert_url + '" target="_blank">Zobacz ogłoszenie</a>';
                    }
                    $message.html('<div class="notice notice-success"><p>' + msg + '</p></div>');
                    $button.replaceWith('<button class="button button-small stop-otomoto" data-car-key="' + carKey + '">Zatrzymaj OTOMOTO</button>');
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                    $button.prop('disabled', false).text('Wznów OTOMOTO');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>Nie udało się wznowić ogłoszenia w OTOMOTO.</p></div>');
                $button.prop('disabled', false).text('Wznów OTOMOTO');
            }
        });
    });
});
