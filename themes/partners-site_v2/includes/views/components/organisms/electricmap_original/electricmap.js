(g => {
    var h,
        a,
        k,
        p = "The Google Maps JavaScript API",
        c = "google",
        l = "importLibrary",
        q = "__ib__",
        m = document,
        b = window;
    b = b[c] || (b[c] =
        {});
    var d = b.maps || (b.maps =
        {}),
        r = new Set,
        e = new URLSearchParams,
        u = () => h || (h = new Promise(async (f, n) => {
            await (a = m.createElement("script"));
            e.set("libraries", [...r] + "");
            for (k in g)
                e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);



            e.set("callback", c + ".maps." + q);
            a.src = `https://maps.${c}apis.com/maps/api/js?` + e + '&libraries=places';
            d[q] = f;
            a.onerror = () => h = n(Error(p + " could not load."));
            a.nonce = m.querySelector("script[nonce]")?.nonce || "";
            m.head.append(a)
        }));
    d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
})({ key: "AIzaSyAAS9bINQevUgNwYhcbo_gPnjetHYLEfh0", v: "weekly" });

let map;
let results;
let radiuses = [];
let set_radius;
let markerspoints;
let bounds;
let range = $('#myRange').val();
let set_radius1;

async function initMap() {

    const { Map } = await google.maps.importLibrary("maps");
    markers = [];
    geocoder = new google.maps.Geocoder();
    const styledMapType = new google.maps.StyledMapType([
        {
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#f5f5f5"
                }
            ]
        },
        {
            "elementType": "labels.icon",
            "stylers": [
                {
                    "visibility": "on"
                }
            ]
        },
        {
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#616161"
                }
            ]
        },
        {
            "elementType": "labels.text.stroke",
            "stylers": [
                {
                    "color": "#f5f5f5"
                }
            ]
        }, {
            "featureType": "administrative.land_parcel",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#bdbdbd"
                }
            ]
        }, {
            "featureType": "poi",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#eeeeee"
                }
            ]
        }, {
            "featureType": "poi",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#757575"
                }
            ]
        }, {
            "featureType": "poi.park",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#e5e5e5"
                }
            ]
        }, {
            "featureType": "poi.park",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#9e9e9e"
                }
            ]
        }, {
            "featureType": "road",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#ffffff"
                }
            ]
        }, {
            "featureType": "road.arterial",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#757575"
                }
            ]
        }, {
            "featureType": "road.highway",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#dadada"
                }
            ]
        }, {
            "featureType": "road.highway",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#616161"
                }
            ]
        }, {
            "featureType": "road.local",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#9e9e9e"
                }
            ]
        }, {
            "featureType": "transit.line",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#e5e5e5"
                }
            ]
        }, {
            "featureType": "transit.station",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#eeeeee"
                }
            ]
        }, {
            "featureType": "water",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#c9c9c9"
                }
            ]
        }, {
            "featureType": "water",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#9e9e9e"
                }
            ]
        }
    ], {
        name: "Styled Map"
    },);

    map = new Map(document.getElementById("map"), {
        center: {
            lat: 52.28228062778781,
            lng: 19.32738891197663
        },
        zoom: 6,
        mapTypeControl: false,

        mapId: '6ae072f94aa185ea'
    });
    /* const ctaLayer = new google.maps.KmlLayer({
        url: "https://karlik.volvotest.pl/img/chargers.kml",
        map: map,
        zoom: 6,
      });*/
    map.mapTypes.set("styled_map", styledMapType);
    map.setMapTypeId("styled_map");
    map.setZoom(6);
    // console.log(ctaLayer);

    CustomCircle = function (center, radius, map) { // Calculate the bounds with the Circle API
        this.bounds_ = new google.maps.Circle({ center: center, radius: radius }).getBounds();
        this.map_ = map;
        this.div_ = null;
        this.setMap(map);
    };
    CustomCircle.prototype = new google.maps.OverlayView();
    CustomCircle.prototype.getBounds = function () {
        return this.bounds_;
    };
    CustomCircle.prototype.onAdd = function () {
        var div = document.createElement('div');
        div.style.position = 'absolute';

        var circle = document.createElement('div');
        circle.className = 'circle'; // class with custom styling
        div.appendChild(circle);

        this.div_ = div;
        var panes = this.getPanes();
        panes.overlayLayer.appendChild(div);
    };
    CustomCircle.prototype.draw = function () {
        var overlayProjection = this.getProjection();
        var sw = overlayProjection.fromLatLngToDivPixel(this.bounds_.getSouthWest());
        var ne = overlayProjection.fromLatLngToDivPixel(this.bounds_.getNorthEast());
        var div = this.div_;
        div.style.left = sw.x + 'px';
        div.style.top = ne.y + 'px';
        div.style.width = (ne.x - sw.x) + 'px';
        div.style.height = (sw.y - ne.y) + 'px';
    };
    CustomCircle.prototype.onRemove = function () {
        this.div_.parentNode.removeChild(this.div_);
        this.div_ = null;
    };

    bounds = new google.maps.LatLngBounds();
    let locationButton = document.getElementById('get_location');
    infoWindow = new google.maps.InfoWindow();

    locationButton.addEventListener("click", () => { // Try HTML5 geolocation.
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                // infoWindow.setPosition(pos);
                // infoWindow.setContent("Location found.");
                // infoWindow.open(map);
                // map.setCenter(pos);
                // map.setZoom(10);

                markerspoints.push(new google.maps.Marker({ map, icon: '/img/center_marker.png', title: '', position: pos }),)
                bounds.extend(pos);
            }, () => {
                handleLocationError(true, infoWindow, map.getCenter());
            },);
        } else { // Browser doesn't support Geolocation
            handleLocationError(false, infoWindow, map.getCenter());
        }
    });

    // Create the search box and link it to the UI element.
    const input = document.getElementById("citysearch");
    const searchBox = new google.maps.places.SearchBox(input);
    markerspoints = [];

    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();
        results = places;

        if (places.length == 0) {
            return;
        }

        // Clear out the old markers.
        markerspoints.forEach((marker) => {
            marker.setMap(null);
        });
        markerspoints = [];
        radiuses.forEach((radius) => {
            radius.setMap(null)
        })
        // For each place, get the icon, name and location.


        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) { // console.log("Returned place contains no geometry");
                return;
            }

            const icon = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
            };

            // Create a marker for each place.
            markerspoints.push(new google.maps.Marker({ map, icon: '/img/center_marker.png', title: '', position: place.geometry.location }),);
            if (place.geometry.viewport) { // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);

        $('.gmap.options button').removeClass('disabled');
        $('.gmap.options button').click();
    });

    // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    // Bias the SearchBox results towards current map's viewport.
    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
    });


    $('.gmap.options button').click(function (event) {
        for (var i = 0; locations.length > i; i++) {
            codeAddress(locations[i]);
        }

        if (event.target.classList.contains('disabled')) {
            return;
        }

        let car = $('#selectCar3 .a-input__field').text().replace(/ /g, '_');

        if (!car) {
            car = $.trim($('#electricmap-selected-model').val()).replace(/ /g, '_');
        }

        let engine = $('#selectEngine4 .a-input__field').text().replace(/ /g, '_');
        let key = car + '_' + engine;
        let count;
        let range_traffic = $('#selectCar3').parent().find('#myRange').val();
        $.each(ranges, function (index, value) {
            if (index == key) {
                count = value[range_traffic];
            }
        })

        if ($('#selectCar3 .a-select__list .is-active').length == 0 && $('#electricmap-selected-model') === undefined) {

            $('#selectCar3 .a-input__field').css('border', '1px solid red');
            $('#selectCar3 .a-input__label').text('Wybierz model').css('color', 'red');

            return false;
        } else {
            $('#selectCar3 .a-input__label').text('Model').removeAttr('style');
            $('#selectCar3 .a-input__field').removeAttr('style');
        }
        if ($('#selectEngine4 .a-select__list .is-active').length == 0) {
            $('#selectEngine4 .a-input__field').css('border', '1px solid red');
            $('#selectEngine4 .a-input__label').text('Wybierz rodzaj napędu').css('color', 'red');
            return false;
        } else {
            $('#selectEngine4 .a-input__label').text('Rodzaj napędu').removeAttr('style');
            $('#selectEngine4 .a-input__field').removeAttr('style');
        }
        if ($('#citysearch').val() == '') {

            return false;
        }

        radiuses.forEach((radius) => {
            radius.setMap(null)
        })
        /* if (set_radius) {
                    set_radius.setMap(null);
                    set_radius1.setMap(null);
                }*/
        // console.log((count)*1000);
        if (results) { // console.log('Wyliczony zasięg dla Autostrada '+range_traffic+' to '+count*1000);
            results.forEach((result) => {
                radiuses.push(new CustomCircle(new google.maps.LatLng(result.geometry.location), (count) * 1000 * 1.08, map),);

            });
            var bounds = new google.maps.LatLngBounds();

            $.each(radiuses, function (index, circle) {
                bounds.union(circle.getBounds());
            });
            $('#limit-dashboard').text(count + ' km').removeClass('no-active');
            map.fitBounds(bounds);
            // map.setZoom(8);
        }
        
        // event.target.classList += ' disabled';
    })

    for (var i = 0; locations.length > i; i++) {
        
        codeAddress(locations[i]);
    }
}
function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ? "Error: The Geolocation service failed." : "Error: Your browser doesn't support geolocation.",);
    infoWindow.open(map);
}
var addedMarkers = [];
function codeAddress(address) {
    console.log(address);
    addedMarkers.forEach(function(marker) {
        marker.setMap(null);
    })
    var typeFilter = $('[name=charger_type]:checked').val();
    if (typeFilter == 'all' || (typeFilter == 'fast' && address[3]) || (typeFilter == 'standard' && !address[3])) {
        geocoder.geocode({
            'address': address[0]
        }, function (results, status) { // console.log(results);
            var latLng = {
                lat: results[0].geometry.location.lat(),
                lng: results[0].geometry.location.lng()
            };
            // console.log (latLng);
            if (status == 'OK') {
                // address[1] = address[1].replace('|*','<p>',$address[1]);
                // address[1] = address[1].replace('*|','</p>',$address[1]);
                // console.log(address[1]);
                var infowindow = new google.maps.InfoWindow({ content: address[1].replaceAll('|*','<p>').replaceAll('*|','</p>').replaceAll('||','<br/>') });
                var marker = new google.maps.Marker({
                    position: latLng,
                    map: map,
                    icon: (address[3] ? "/img/fast.png" : "/img/charge.png"),
                    title: ''
                });
               
                addedMarkers.push(marker);
                google.maps.event.addListener(marker, 'click', function () {
                    infowindow.open(map, marker);
                });
                marker.addListener('mouseover', () => infowindow.open(map, marker))
                marker.addListener('mouseout', () => infowindow.close())
                // console.log (map);
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    }
}

$(document).ready(function () {
    $('#selectEngine4 ul li.is-active').removeClass('is-active');
    $('#selectCar3 ul li.is-active').removeClass('is-active');
    $('#selectEngine4 .a-input__field').text('');
    $('#selectCar3 .a-input__field').text('');
    var model = '';
    var engine = ''
    var range = ''
    $('.myRange').on('change', function () {
        console.log('zmiana el');
        // range = $(this).val();
        $(this).parent().find('.fakeprogress').css('width', ($(this).val() - 2) + '%');
        $('.gmap.options button').click();

    });

    $('[name=charger_type]').on('change', function () {
        // alert('aaa');
        $('.gmap.options button').trigger('click');
    })
    /* $('#myRange').on('drag',function(e) {
        
          
        })*/
    $('#selectCar3 ul li').on('click', function () {
        let updateClass = false
        $('#selectCar3 .a-input__field').text('');
        if ($(this).hasClass('is-active')) {
            updateClass = true;
        }
        $('#selectCar3 ul li.is-active').removeClass('is-active');
        if (updateClass) {
            $(this).addClass('is-active');
            $('#selectCar3 .a-input__field').text($(this).find('.item__label').text());
        }
        if ($('#selectCar3 ul li.is-active').length == 0) {
            $('#selectCar3 .a-input__field').text('');
        }
        if ($('#selectCar3 li.is-active').length > 0) {
            $('#selectEngine4 li').show();
            let selected = $.trim($(this).text());
            $('#selectEngine4 li').each(function () {
                let el = $.trim($(this).text());
                // console.log(el.replace(' ','_'));
                // console.log(combinations[selected]);
                if (combinations[selected].includes(el.replace(/ /g, '_'))) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            })

        } else {
            $('#selectEngine4 li').show();
        }
    });
    $('#selectCar11 ul li').on('click', function () { // $('#selectCar11 ul li.is-active').removeClass('is-active');
        $('#selectCar11 .a-input__field').text('');
        let updateClass = false
        if ($(this).hasClass('is-active')) {
            updateClass = true;
        }
        $('#selectCar11 ul li.is-active').removeClass('is-active');
        if (updateClass) {
            $(this).addClass('is-active');
            $('#selectCar11 .a-input__field').text($(this).find('.item__label').text());

        } else { }
        if ($('#selectCar11 ul li.is-active').length == 0) {
            $('#selectCar11 .a-input__field').text('');
        }
        if ($('#selectCar11 li.is-active').length > 0) {
            $('#selectEngine100 li').show();
            let selected = $.trim($(this).text());
            $('#selectEngine100 li').each(function () {
                let el = $.trim($(this).text());
                // console.log(el.replace(' ','_'));
                // console.log(combinations[selected]);
                if (combinations[selected].includes(el.replace(/ /g, '_'))) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            })

        } else {
            $('#selectEngine100 li').show();
        }
        $('.form-section .element.is-active').click();
    });
    $('#selectEngine4 > div').click(function () { })

    $('#reset__filters').on('click', function () {
        radiuses.forEach((radius) => {
            radius.setMap(null)
        })
        markerspoints.forEach((marker) => {
            marker.setMap(null);
        });
        $('.range-container').addClass('no-active');
        $('#citysearch').val('')


        $('#selectEngine4 ul li.is-active').removeClass('is-active');
        $('#selectCar3 ul li.is-active').removeClass('is-active');
        $('#selectCar3 .a-input__field').text('');
        $('#selectEngine4 .a-input__field').text('');
        $('.gmap.options button').removeClass('disabled');
    });
})

initMap();

const rangeInputs = document.querySelectorAll('input[type="range"]')
const numberInput = document.querySelector('input[type="number"]')
let isRTL = document.documentElement.dir === 'rtl'

function handleInputChange(e) {
    let target = e.target
    if (e.target.type !== 'range') {
        target = document.getElementById('range')
    }
    const min = target.min
    const max = target.max
    const val = target.value
    let percentage = (val - min) * 100 / (max - min)
    if (isRTL) {
        percentage = (max - val)
    }

    if (target.nextElementSibling) {
        target.nextElementSibling.style.width = percentage - 3 + '%';
    }

}

rangeInputs.forEach(input => {
    input.addEventListener('input', handleInputChange)
})
if (numberInput) {
    numberInput.addEventListener('input', handleInputChange)
}
// Handle element change, check for dir attribute value change
function callback(mutationList, observer) {
    mutationList.forEach(function (mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'dir') {
            isRTL = mutation.target.dir === 'rtl'
        }
    })
}

// Listen for body element change
const observer = new MutationObserver(callback)
observer.observe(document.documentElement, { attributes: true })