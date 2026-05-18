jQuery(document).ready(function($) {
    var $regionField = $('.acf-field[data-key="otomoto_region_id"]');
    var $cityField = $('.acf-field[data-key="otomoto_city_id"]');
    var $districtField = $('.acf-field[data-key="otomoto_district_id"]');
    var $latitudeField = $('.acf-field[data-key="otomoto_latitude"]');
    var $longitudeField = $('.acf-field[data-key="otomoto_longitude"]');

    var $regionSelect = $regionField.find('select');
    var $citySelect = $cityField.find('select');
    var $districtSelect = $districtField.find('select');
    var $latitudeInput = $latitudeField.find('input');
    var $longitudeInput = $longitudeField.find('input');

    var savedRegion = $regionSelect.val();
    var savedCity = $citySelect.val();
    var savedDistrict = $districtSelect.val();

    // Track if we're in the middle of populating to avoid triggering change events prematurely
    var isPopulating = false;

    function setSelectOptions($select, items, placeholder) {
        $select.empty();
        $select.append('<option value="">' + (placeholder || 'Wybierz...') + '</option>');
        if (items && items.length) {
            $.each(items, function(i, item) {
                $select.append('<option value="' + item.id + '">' + item.name + '</option>');
            });
        }
    }

    function disableSelect($select, message) {
        $select.empty();
        $select.append('<option value="">' + message + '</option>');
        $select.prop('disabled', true);
    }

    function enableSelect($select) {
        $select.prop('disabled', false);
    }

    // Test connection button
    $(document).on('click', '#otomoto-test-connection', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $status = $('#otomoto-connection-status');

        $btn.prop('disabled', true).text('Testowanie...');
        $status.html('');

        $.ajax({
            url: otomotoLocationAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'otomoto_test_connection',
                nonce: otomotoLocationAjax.nonce,
            },
            success: function(response) {
                $btn.prop('disabled', false).text('Testuj połączenie');
                if (response.success) {
                    $status.html('<span style="color:green;">✓ ' + (response.data.message || 'Połączenie OK') + '</span>');
                    // If connection works, try to load regions
                    loadRegions();
                } else {
                    $status.html('<span style="color:red;">✗ ' + (response.data.message || 'Błąd') + '</span>');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Testuj połączenie');
                $status.html('<span style="color:red;">✗ Błąd połączenia z serwerem</span>');
            },
        });
    });

    function loadRegions() {
        if (!$regionSelect.length) return;

        disableSelect($regionSelect, 'Ładowanie regionów...');
        disableSelect($citySelect, 'Najpierw wybierz region');
        disableSelect($districtSelect, 'Najpierw wybierz miasto');

        $.ajax({
            url: otomotoLocationAjax.ajax_url,
            type: 'GET',
            data: {
                action: 'otomoto_fetch_regions',
                nonce: otomotoLocationAjax.nonce,
            },
            success: function(response) {
                if (response.success && response.data.regions) {
                    isPopulating = true;
                    enableSelect($regionSelect);
                    setSelectOptions($regionSelect, response.data.regions, 'Wybierz region...');

                    // Restore saved value if exists
                    if (savedRegion) {
                        $regionSelect.val(savedRegion).trigger('change');
                    }
                    isPopulating = false;
                } else {
                    disableSelect($regionSelect, 'Błąd ładowania regionów');
                }
            },
            error: function() {
                disableSelect($regionSelect, 'Błąd ładowania regionów');
            },
        });
    }

    function loadCities(regionId) {
        if (!$citySelect.length) return;

        isPopulating = true;
        $citySelect.val('');
        $districtSelect.val('');
        disableSelect($citySelect, 'Ładowanie miast...');
        disableSelect($districtSelect, 'Najpierw wybierz miasto');

        $.ajax({
            url: otomotoLocationAjax.ajax_url,
            type: 'GET',
            data: {
                action: 'otomoto_fetch_cities',
                nonce: otomotoLocationAjax.nonce,
                region_id: regionId,
            },
            success: function(response) {
                if (response.success && response.data.cities) {
                    enableSelect($citySelect);
                    setSelectOptions($citySelect, response.data.cities, 'Wybierz miasto...');

                    // Store lat/lng data on options for later
                    $.each(response.data.cities, function(i, city) {
                        if (city.latitude && city.longitude) {
                            $citySelect.find('option[value="' + city.id + '"]').data('lat', city.latitude).data('lng', city.longitude);
                        }
                    });

                    // Restore saved value if exists
                    if (savedCity) {
                        $citySelect.val(savedCity).trigger('change');
                    }
                } else {
                    disableSelect($citySelect, 'Błąd ładowania miast');
                }
                isPopulating = false;
            },
            error: function() {
                disableSelect($citySelect, 'Błąd ładowania miast');
                isPopulating = false;
            },
        });
    }

    function loadDistricts(cityId) {
        if (!$districtSelect.length) return;

        isPopulating = true;
        $districtSelect.val('');
        disableSelect($districtSelect, 'Ładowanie dzielnic...');

        $.ajax({
            url: otomotoLocationAjax.ajax_url,
            type: 'GET',
            data: {
                action: 'otomoto_fetch_districts',
                nonce: otomotoLocationAjax.nonce,
                city_id: cityId,
            },
            success: function(response) {
                if (response.success && response.data.districts) {
                    enableSelect($districtSelect);
                    setSelectOptions($districtSelect, response.data.districts, 'Wybierz dzielnicę...');

                    // Restore saved value if exists
                    if (savedDistrict) {
                        $districtSelect.val(savedDistrict);
                    }
                } else {
                    disableSelect($districtSelect, 'Brak dzielnic lub błąd');
                }
                isPopulating = false;
            },
            error: function() {
                disableSelect($districtSelect, 'Błąd ładowania dzielnic');
                isPopulating = false;
            },
        });
    }

    function autoFillLatLng(cityId) {
        var $option = $citySelect.find('option[value="' + cityId + '"]');
        var lat = $option.data('lat');
        var lng = $option.data('lng');

        if (lat && $latitudeInput.length) {
            $latitudeInput.val(lat);
        }
        if (lng && $longitudeInput.length) {
            $longitudeInput.val(lng);
        }
    }

    // Event handlers for cascading selects
    $regionSelect.on('change', function() {
        if (isPopulating) return;
        var regionId = $(this).val();
        if (regionId) {
            loadCities(regionId);
        } else {
            disableSelect($citySelect, 'Najpierw wybierz region');
            disableSelect($districtSelect, 'Najpierw wybierz miasto');
        }
    });

    $citySelect.on('change', function() {
        if (isPopulating) return;
        var cityId = $(this).val();
        if (cityId) {
            loadDistricts(cityId);
            autoFillLatLng(cityId);
        } else {
            disableSelect($districtSelect, 'Najpierw wybierz miasto');
        }
    });

    // If Otomoto is enabled, try to load regions automatically if credentials exist
    // Wait a moment for ACF to fully render
    setTimeout(function() {
        if ($regionSelect.length && $regionSelect.find('option').length <= 1) {
            // If the select is empty or only has placeholder, it means we should populate it
            // Check if credentials fields have values
            var hasCreds = true; // Assume true, the server will handle auth errors
            if (hasCreds) {
                loadRegions();
            }
        }
    }, 500);
});
