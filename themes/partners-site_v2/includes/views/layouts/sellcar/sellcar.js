var showContentFound = '';
var showContentNotFound = '';
var showContent = null;
var elStatus = false;
var autoForm = false;
function generateShowContentFound(vin) {
    showContentNotFound = '';
    showContentNotFound += '<p>Nie udało nam się wygenerować danych pojazdu o numerze VIN: <b>' + vin + '</b>. Prosimy przejść proces manualnie, aby otrzymać szacunkową wycenę pojazdu.</p>';
    showContentNotFound += '<a href="#" id="useManual" class="content__submit-button a-button">ROZPOCZNIJ PROCES WYCENY</a>';
    elStatus = true;
    return showContentNotFound;
}
function validateVIN(el) {
    console.log(el.value.length);
    if (el.value.length !== 17) {
        $('.content__submit-button').addClass("disabled").attr('disabled','disabled');
        showContentNotFound = '';
        elStatus = false;
        $('.enterVin').addClass('error').next('.legend').addClass('error').html('<span style="">&#9432;</span> Niepoprawny numer VIN - wpisz 17 znaków');
    } else {
        $('.content__submit-button').removeAttr('disabled').removeClass('disabled');
        if (elStatus == false && el.value.length == 17) {
            showContent = generateShowContentFound(el.value);
            $('.enterVin').removeClass('error').next('.legend').removeClass('error').html('<span style="">&#9432;</span> Numer VIN zawiera 17 znaków');
            elStatus = true;
        }
    }
}
var API_DOM = 'https://valuation.poznajvolvo.pl/api/';
var Dates = [];
var datePicker = null;
var customRoute = false;
var data = null;
var unsetModels = ['C30', 'C70', 'S40', 'S70', 'S80', 'V50'];
// var unsetModels = [];
function querySteps(data) {
    if (autoForm) {
        return false;
    }
    $('#nextStep').attr('disabled', 'disabled');
    $('#nextStep').addClass('blocked');
    setTimeout(function() {
        $('#nextStep').removeClass('blocked');
    },5000);
    jQuery.ajax({
        url: '/wp/wp-admin/admin-ajax.php',
        type: "POST",
        data: { action: 'querySteps', data },
        dataType: "json",
        contentType: "application/x-www-form-urlencoded",
        success: function (r) {
            var r_size = r.length
            var x = 0;
            if (r.length == 0) {
                $('.global_warning').text('Przepraszamy, ale proces wyceny nie jest dostępny dla wybranego modelu auta.').show();
                $('#nextStep').attr('disabled', 'disabled').addClass('disabled');
                return false;
            } else {
                $('#nextStep').removeClass('disabled').removeAttr('disabled');
                $('.global_warning').hide();
            }
            // switch (value[0].rel) {

            // }
           
            $.each(r, function (index, value) {
                switch (value.rel) {
                    case 'wheeldrive':
                        if (!value.name) {
                            value.name = value.summary;
                        }
                        if ($('select[name="car_drive"] option[value="' + value.name + '"]').length == 0) {
                        $('select[name="car_drive"]').append('<option value="' + value.name + '">' + value.name + '</option>');
                        }
                        $('.singleField.car_drive.hidden').removeClass('hidden').addClass('forhidden');
                        $('.singleField.car_drive select').niceSelect();
                        $('.singleField.car_drive select').niceSelect('update');
                        if (customRoute) {

                        }
                        break;
                    case 'facelift':
                        if (!value.name) {
                            value.name = value.summary;
                        }
                        if ($('select[name="car_body"] option[value="' + value.name + '"]').length == 0) {
                            $('select[name="car_body').append('<option value="' + value.name + '">' + value.summary + '</option>');
                        }
                        $('.singleField.body.hidden').removeClass('hidden').addClass('forhidden');
                        $('.singleField.body label:eq(0)').removeAttr('class').text('Generacja pojazdu');
                        $('.singleField.body select').niceSelect();
                        $('.singleField.body select').niceSelect('update');
                        // $('.singleField.car_drive select').attr('name','car_generation');
                        //   $('.singleField.car_drive').insertAfter($('.stepContent[data-step="step2"] .singleField:eq(0)'));
                        customRoute = true;
                        if (customRoute) {

                        }
                        break;
                    case 'trim':
                        if (!value.name) {
                            value.name = value.summary;
                        }
                        if ($('select[name="car_version"] option[value="' + value.name + '"]').length == 0) {
                            $('select[name="car_version"]').append('<option value="' + value.name + '">' + value.summary + '</option>');
                        }
                      //  $('select[name="car_version"]').append('<option value="' + value.name + '">' + value.name + '</option>');

                        $('.trim.hidden').removeClass('hidden').addClass('forhidden');
                        $('.trim select').niceSelect();
                        $('.trim select').niceSelect('update');
                        if (customRoute) {

                        }

                        break;
                    case 'transmission':
                        if (!value.name) {
                            value.name = value.summary;
                        }
                        if ($('select[name="car_gearbox"] option[value="' + value.name + '"]').length == 0) {
                            $('select[name="car_gearbox"]').append('<option value="' + value.name + '">' + value.summary + '</option>');
                        }
                       // $('select[name="car_gearbox"]').append('<option value="' + value.name + '">' + value.name + '</option>');
                        $('.gearbox.hidden').removeClass('hidden').addClass('forhidden');
                        $('.gearbox select').niceSelect();
                        $('.gearbox select').niceSelect('update');
                        if (customRoute) {

                        }
                        break;
                    case 'model':
                       
                        if ($.inArray(value.name, unsetModels) < 0) {
                            $('.carmodel select').append('<option value="' + value.name + '">' + value.name + '</option>');
                        }
                        // $('.carmodel ul').remove().append('<li class="list__item item js-select-multi__item" data-value="'+value.name+'"><span class="item__icon"></span><span class="item__label">'+value.name+'</span></li>')
                        break;
                    case 'engine':
                        if ($($('select[name="car_engine"] option[value="' + value.summary + '"]').length == 0)) {
                            $('select[name="car_engine').append('<option value="' + value.summary + '">' + value.summary + '</option>')

                        }
                        $('select[name="car_engine').niceSelect();
                        $('select[name="car_engine').niceSelect('update');
                        break;
                    case 'body':
                        customRoute = true;
                        if (!value.name) {
                            value.name = value.summary;
                        }
                        if ($($('select[name="car_body"] option[value="' + value.name + '"]').length == 0)) {
                            $('select[name="car_body').append('<option value="' + value.name + '">' + value.summary + '</option>')
                        }
                        $('.singleField.body.hidden').removeClass('hidden').addClass('forhidden');

                        x++;
                        break;
                    case 'regdate':
                        
                        if (x == 0 || r.length - 1 == x) {
                           
                            if (x == 0) {
                                Dates.push(new Date('12-01-' + (parseInt(value.name) + 1)));
                            } else if (r.length - 1 == x) {
                                Dates.push(new Date('01-01-' + parseInt(value.name)));
                            }
                        }
                        console.log(Dates);
                        if (Dates.length == 2 || r.length == 1) {
                            Dates = Dates.reverse();
                            const d = new Date();
                            var month = d.getMonth();
                            var year = d.getYear();
                            if (datePicker == null) {
                                
                                datePicker = $('input.car_year').MonthPicker({
                                    OnAfterChooseMonth: function (selectedDate) {
                                        let d = new Date(selectedDate);
                                        var sDate = d.getMonth() + 1 + '/' + d.getFullYear()
                                        let model = $('select[name="car_model"] option:selected').val();

                                        let data = {
                                            'endpoint': 'getEngine/' + model + '/' + sDate,
                                        }
                                        querySteps(data);
                                    },
                                    Button: false,
                                    MinMonth: (Dates[1] ? Dates[0] : null),
                                    MaxMonth: (Dates[1] ? Dates[1] : Dates[0]), // Or you could just pass 18.
                                    i18n: (function() {
                                        // Detect language from HTML lang attribute
                                        var lang = document.documentElement.lang || 'pl';
                                        var locale = lang.split('-')[0]; // Get 'cs' from 'cs-CZ' or 'cs_CZ'
                                        
                                        var translations = {
                                            'cs': {
                                                year: ' ',
                                                closeText: "Zavřít",
                                                prevYear: "<",
                                                nextYear: ">",
                                                currentText: "Dnes",
                                                months: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
                                                dayNames: ["Neděle", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota"],
                                                dayNamesShort: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
                                                dayNamesMin: ["N", "Po", "Út", "St", "Čt", "Pá", "So"],
                                                weekHeader: "Týd",
                                                dateFormat: "dd.mm.yy",
                                                firstDay: 1,
                                                isRTL: false,
                                                showMonthAfterYear: false,
                                                yearSuffix: ""
                                            },
                                            'pl': {
                                                year: ' ',
                                                closeText: "Zamknij",
                                                prevYear: "<",
                                                nextYear: ">",
                                                currentText: "Dziś",
                                                months: ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"],
                                                dayNames: ["Niedziela", "Poniedziałek", "Wtorek", "Środa", "Czwartek", "Piątek", "Sobota"],
                                                dayNamesShort: ["Nie", "Pn", "Wt", "Śr", "Czw", "Pt", "So"],
                                                dayNamesMin: ["N", "Pn", "Wt", "Śr", "Cz", "Pt", "So"],
                                                weekHeader: "Tydz",
                                                dateFormat: "dd.mm.yy",
                                                firstDay: 1,
                                                isRTL: false,
                                                showMonthAfterYear: false,
                                                yearSuffix: ""
                                            }
                                        };
                                        
                                        return translations[locale] || translations['pl'];
                                    })()

                                });
                                // datePicker = $(".car_year").flatpickr({
                                //     "locale": 'pl',
                                //     minDate: Dates[0],
                                //     maxDate: Dates[1],
                                //     defaultDate: Dates[0],
                                //     disableMobile: "false",
                                //     plugins: [
                                //         new monthSelectPlugin({
                                //             shorthand: false, //defaults to false
                                //             dateFormat: "m/Y", //defaults to "F Y"
                                //             altFormat: "m/Y", //defaults to "F Y"                                                
                                //         })
                                //     ],
                                //     onChange: function (selectedDates, dateStr, instance) {
                                //         let model = $('select[name="car_model"] option:selected').val();

                                //         let data = {
                                //             'endpoint': 'getEngine/' + model + '/' + dateStr,
                                //         }
                                //         querySteps(data);
                                //     }
                                // });
                                $('.car_year').trigger('change');

                            }
                        }
                        x++;
                        break;
                    case 'seats':
                        customRoute = true;

                        $('.carsell-seats-data.hidden').insertAfter($('.stepContent[data-step="step2"] .singleField:eq(0)'));
                        $('.carsell-seats-data.hidden').removeClass('hidden');
                        $('.carsell-seats-data select').niceSelect();
                        $('.carsell-seats-data select').niceSelect('update');
                        let data = {
                            'endpoint': 'getNextStep/' + $('select[name="car_model"] option:selected').val() + '/' + $('.car_year').val() + '/' + $('select[name="car_seats"] option:eq(1)').val()
                        }
                        querySteps(data);
                        $('.stepContent[data-step="step2"] select[name="car_seats"]').on('change', function () {
                            //  alert(customRoute);
                        });
                        //    $('select[name="car_engine"]').append('<option value="'+value.name+'">'+value.name+'</option>');
                        break;

                }
            })
         
            $('.carmodel select').niceSelect();
            $('#nextStep').removeClass('blocked');
        }
    });
}
function getModels(data) {
 
    $.data.each(function (index, value) {
    //    console.log(value.name);
    })

}
$(document).ready(function() {

    $('.startManual').click(function (e) {
        e.preventDefault();
        $('h1.a-site-heading__heading').parent().parent().hide();
        $('.SellContainer,.sellContainer').hide();
        $('.manualForm').show();
        return false;
    });
    setTimeout(function() {
    $('.car_condition select').niceSelect();
    }, 1000)
    setInterval(function () {
        $('.enterVin').trigger('keyUp');
        if ($('.enterVin').val().length == 17) {
            $('.getVinData').removeClass('disabled');
        }

    }, 500)
    var selectYear = null;
    $('.content__submit-button.getVinData').click(function (e) {
        e.preventDefault();
        if ($(this).val().length < 17) {
            // $(this).addClass('disabled');
            // return false;
        }
        //YV1UZH7V8S1000259

        var searchedVin = $('.enterVin').val();
        jQuery.ajax({
            url: '/wp/wp-admin/admin-ajax.php',
            type: "POST",
            data: { action: 'checkVIN', vin: searchedVin },
            dataType: "json",
            contentType: "application/x-www-form-urlencoded",
            success: function (r) {

                if (r) {

                    let car = r.vehicle;
                    let productionYear = car.productionDate.split('-');
                    let registerDate = car.firstRegistrationDate.split('T');
                    let engine = car.variantDescription.split(', ');
                    showContentFound = '<p>Na podstawie podanego numeru VIN - <b>' + searchedVin + '</b> - wygenerowaliśmy dane pojazdu. Zatwierdź wybraną konfigurację, jeśli wszystkie parametry się zgadzają lub wypełnij formularz ręcznie jeśli dane się nie zgadzają.</p>';
                    showContentFound += '<div class="specContainer">';
                    showContentFound += '<h3>Podstawowe Dane Pojazdu</h3>';
                    showContentFound += '<div><span>Model Pojazdu:</span> <span>' + car.modelDescription + '</span></div>';
                    showContentFound += '<div><span>Rok produkcji:</span> <span>' + productionYear[0] + '</span></div>';
                    showContentFound += '<div><span>Kolor:</span> <span>' + car.externalColour + '</span></div>';
                    showContentFound += '<div><span>Data pierwszej rejestracji:</span> <span>' + registerDate[0] + '</span></div>';
                    showContentFound += '<h3>Dane techniczne</h3>';
                    showContentFound += '<div><span>Silnik:</span> <span>' + car.engineCapacity + '</span></div>';
                    showContentFound += '<div><span>Skrzynia biegów:</span> <span>' + car.gearboxDescription + '</span></div>';
                    showContentFound += '<div><span>Wersja:</span> <span>' + car.salesVersionDescription + '</span></div>';
                    showContentFound += '<div><span>Liczba miejsc:</span> <span>' + car.numberofSeats + '</span></div>';
                    showContentFound += '</div>';
                    showContentFound += '<a href="#" id="useManual" class="content__submit-button white a-button">Wypełniam formularz ręcznie</a>';
                    showContentFound += '<a href="#" id="useConfig" class="content__submit-button a-button">POTWIERDZAM KONFIGURACJĘ</a>';
                    showContent = showContentFound;
                    let noData = new Popup({
                        id: "my-popup",
                        title: "Dane Twojego pojazdu",
                        showImmediately: true,
                        content: `` + showContent + ``,
                        borderRadius: 0,
                        hideCallback: () => {
                            $('.popup.my-popup').remove();
                        },
                    });
                    $('.popup-close').text('');
                    $('#useManual').bind('click',function (e) {
                        
                        e.preventDefault();
                        noData.hide();
                        $('.popup.my-popup').remove();
                        $('.SellContainer,.sellContainer').hide();
                        $('h1.a-site-heading__heading').parent().parent().hide();
                        $('.manualForm').show();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return false;
                    });
                    $('#useConfig').bind('click',function () {
                        autoForm = true;
                        noData.hide();
                        $('.popup.my-popup').remove();
                        $('h1.a-site-heading__heading').parent().parent().hide();
                        $('.global_info').text('Pobraliśmy dane Twojego pojazdu. Uzupełnij brakujące informacje, aby uzyskać dokładniejszą wycenę.').show();
                        $('.manualForm').show();
                        $('input[name="car_vin_number"]').val($('.enterVin').val());
                        $('.SellContainer,.sellContainer').hide();
                        $('select[name="car_model"] option').remove();
                        $('select[name="car_engine"] option').remove();
                        $('select[name="car_model"]').append('<option selected="selected" value="' + car.modelDescription + '">' + car.modelDescription + '</option>');
                        $('select[name="car_model"]').niceSelect('update');
                        $('select[name="car_engine"]').append('<option selected="selected" value="' + car.engineCapacity + '">' + car.engineCapacity + '</option>')
                        $('input.car_year').attr('value', productionYear[1] + '/' + productionYear[0]).attr('disabled', 'disabled');
                        $('.stepContent[data-step="step1"] select,.stepContent[data-step="step2"] select,.stepContent[data-step="step3"] select,.stepContent[data-step="step4"] select').attr('disabled', 'disabled');
                        // $('select[name="car_year"] option[value="'+productionYear[0]+'"]').attr('selected','selected');
                        // $('input[name="car_color"]').attr('value',car.externalColour);
                        // $('input[name="car_registered"]').attr('value',registerDate[0]);
                        // dateSelect.setDate(registerDate[0]);
                        $('.singleField.gearbox').removeClass('hidden');
                        if (car.gearboxDescription.includes('AUT')) {
                            $('select[name="car_gearbox"] option').remove();
                            $('select[name="car_gearbox"]').append('<option selected="selected" value="' + car.gearboxDescription + '">Automatyczna</option>');
                            // $('select[name="car_gearbox"] option[value="Automatyczna"]').attr('selected','selected');
                        } else {
                            $('select[name="car_gearbox"]').append('<option selected="selected" value="' + car.gearboxDescription + '">Manualna</option>');
                        }
                        $('.singleField.trim').removeClass('hidden');
                        $('select[name="car_version"] option').remove();
                        $('select[name="car_version"]').append('<option selected="selected" value="' + car.salesVersionDescription + '">' + car.salesVersionDescription + '</option>');
                        $('.singleField.car_seats').removeClass('hidden');
                        $('select[name="car_seats"] option[value="' + car.numberofSeats + '"]').attr('selected', 'selected');

                        $('#nextStep').click().click().click().click();
                        $('.stage span span').text('');

                        window.scrollTo({ top: 0, behavior: 'smooth' });


                        return false;
                    })
                } else {
                    let showContent = generateShowContentFound(searchedVin);
                    let noData = new Popup({
                        id: "my-popup",
                        title: "Brak danych pojazdu",
                        showImmediately: true,
                        content: `` + showContent + ``,
                        borderRadius: 0,
                        hideCallback: () => {
                            $('.popup.my-popup').remove();
                        },
                        
                    });
                    $('.popup-close').text('');
                    $('#useManual').click(function (e) {
                        e.preventDefault();
                        noData.hide();

                        $('.SellContainer,.sellContainer').hide();
                        $('h1.a-site-heading__heading').parent().parent().hide();
                        $('.manualForm').show();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return false;
                    });
            
                }
            }
        });
        return false;
    });
    // $('.SellContainer,.sellContainer').hide();
    // $('.manualForm').show();
    $('.startManual').click(function (e) {
        e.preventDefault();
        $('h1.a-site-heading__heading').parent().parent().hide();
        $('.SellContainer,.sellContainer').hide();
        $('.manualForm').show();
        return false;
    });
    let data = {
        'endpoint': 'getData/models',
    }
    let step1 = querySteps(data);
    $('select[name="car_model"]').on('change', function () {
        data = {
            'endpoint': 'getYears/' + $(this).find('option:selected').val() + '/1'
        }
        querySteps(data);
    })

    $('.stepContent[data-step="step2"] select[name="car_seats"]').on('change', function () {

    })
    $('select[name="car_engine"]').on('change', function () {
        if (customRoute) {
            var customStep = ($('select[name="car_body"] option').length > 1 ? $('select[name="car_body"] option:selected').val() : $('select[name="car_seats"] option:selected').val());

            data = {
                'endpoint': 'getVersion/' + $('select[name="car_model"] option:selected').val() + '/' + $('.car_year').val() + '/' + customStep + '/' + $('select[name="car_engine"] option:selected').val()
            }

        } else {
            data = {
                'endpoint': 'getVersions/' + $('select[name="car_model"] option:selected').val() + '/' + $('.car_year').val() + '/' + $('select[name="car_engine"] option:selected').val()
            }
        }
        querySteps(data);
    })
    $('select[name="car_body"]').on('change', function () {
        if (customRoute) {
            if ($('select[name="car_body"] option:selected').length > 0) {
                data = {
                    'endpoint': 'getNextStep/' + $('select[name="car_model"] option:selected').val() + '/' + $('.car_year').val() + '/' + $(this).find('option:selected').val()
                }
                querySteps(data);
            }
        }
    });
    $('select[name="car_gearbox"]').on('change', function () {
        if (customRoute) {
            if ($('select[name="car_drive"] option').length > 1) {

                data = {
                    'endpoint': 'getStep7/' + $('select[name="car_model"] option:selected').val() + '/' + $('.car_year').val() + '/' + $('select[name="car_seats"] option:selected').val() + '/' + $('select[name="car_engine"] option:selected').val() + '/' + $('select[name="car_drive"] option:selected').val() + '/' + $(this).find('option:selected').val()
                }
                querySteps(data);

                return false
            }
            
            if ($('select[name="car_body"] option:selected').length > 0) {
                
                console.log($('select[name="car_body"]').length);
                console.log($('select[name="car_body"] option:selected').val());
                data = {
                    'endpoint': 'getStep6/' + $('select[name="car_model"] option:selected').val() + '/' + $('.car_year').val() + '/' + ($('select[name="car_body"] option').length > 1 ? $('select[name="car_body"] option:selected').val() : $('select[name="car_seats"] option:selected').val()) + '/' + $('select[name="car_engine"] option:selected').val() + '/' + $(this).find('option:selected').val()
                }
                querySteps(data);
            }
        }
    });
    $('select[name="car_drive"]').on('change', function () {
        if (customRoute) {
            // if ($('select[name="car_body"] option:selected').length > 0) {
            console.log($('select[name="car_body"]').length);
            console.log($('select[name="car_body"] option:selected').val());
            data = {
                'endpoint': 'getStep6/' + $('select[name="car_model"] option:selected').val() + '/' + $('.car_year').val() + '/' + ($('select[name="car_body"] option').length > 1 ? $('select[name="car_body"] option:selected').val() : $('select[name="car_seats"] option:selected').val()) + '/' + $('select[name="car_engine"] option:selected').val() + '/' + $(this).find('option:selected').val()
            }
            querySteps(data);
            // }
        }
    })
 
   
      
       
  


    $('p > span.more').click(function () {
        $(this).hide();
        $(this).parent().next('div').show();
    })
    $('div > span.more').click(function () {
        $(this).hide();
        $(this).next('div').show();
    })
    $('.hide_info').click(function () {
        $(this).parent().parent().hide();
        $(this).parent().parent().prev('p').find('span').show();
    })
    $('.hide_info_custom').click(function () {
        $(this).parent().hide();
        $(this).parent().prev('span').show();
    })

    $('.select_all').click(function () {
        if ($(this).find('input[type="checkbox"]').is(':checked')) {
            $('.automark:not(.is-active) label').click();
        } else {
            $('.automark.is-active label').click();
        }
        $('.automark input[type="checkbox"]').attr('checked', 'checked').trigger('change');
    })
    $('.select_all_1').click(function () {
        if ($(this).find('input[type="checkbox"]').is(':checked')) {
            $('.automark1:not(.is-active) label').click();
        } else {
            $('.automark1.is-active label').click();
        }
        $('.automark input[type="checkbox"]').attr('checked', 'checked').trigger('change');
    })
})

