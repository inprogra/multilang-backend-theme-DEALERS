import {Loader} from "@googlemaps/js-api-loader";

const mapStyle = [
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
				"visibility": "off"
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
	},
	{
		"featureType": "administrative.land_parcel",
		"elementType": "labels.text.fill",
		"stylers": [
			{
				"color": "#bdbdbd"
		}
		]
	},
	{
		"featureType": "poi",
		"elementType": "geometry",
		"stylers": [
			{
				"color": "#eeeeee"
		}
		]
	},
	{
		"featureType": "poi",
		"elementType": "labels.text.fill",
		"stylers": [
			{
				"color": "#757575"
		}
		]
	},
	{
		"featureType": "poi.park",
		"elementType": "geometry",
		"stylers": [
			{
				"color": "#e5e5e5"
		}
		]
	},
	{
		"featureType": "poi.park",
		"elementType": "labels.text.fill",
		"stylers": [
			{
				"color": "#9e9e9e"
		}
		]
	},
	{
		"featureType": "road",
		"elementType": "geometry",
		"stylers": [
			{
				"color": "#ffffff"
		}
		]
	},
	{
		"featureType": "road.arterial",
		"elementType": "labels.text.fill",
		"stylers": [
			{
				"color": "#757575"
		}
		]
	},
	{
		"featureType": "road.highway",
		"elementType": "geometry",
		"stylers": [
			{
				"color": "#dadada"
		}
		]
	},
	{
		"featureType": "road.highway",
		"elementType": "labels.text.fill",
		"stylers": [
			{
				"color": "#616161"
		}
		]
	},
	{
		"featureType": "road.local",
		"elementType": "labels.text.fill",
		"stylers": [
			{
				"color": "#9e9e9e"
		}
		]
	},
	{
		"featureType": "transit.line",
		"elementType": "geometry",
		"stylers": [
			{
				"color": "#e5e5e5"
		}
		]
	},
	{
		"featureType": "transit.station",
		"elementType": "geometry",
		"stylers": [
			{
				"color": "#eeeeee"
		}
		]
	},
	{
		"featureType": "water",
		"elementType": "geometry",
		"stylers": [
			{
				"color": "#c9c9c9"
		}
		]
	},
	{
		"featureType": "water",
		"elementType": "labels.text.fill",
		"stylers": [
			{
				"color": "#9e9e9e"
		}
		]
	}
	]

	class Map {
		constructor(elements) {
			this.mapsApiKey = mapsApiKey
			this.items      = []

			elements.forEach(
				el => {
                this.items.push(
						{
							el: el
						}
					)
				}
			)

			this.initMapApi()
		}

		initMapApi() {
			this.loader = new Loader(
				{
					apiKey: this.mapsApiKey,
					version: "weekly",
				}
			);

			this.loader.load().then(
				() => {
                this.initMaps()
				}
			);
		}

		initMaps() {
			this.items.forEach(
				item => {
                const pin         = item.el.querySelector( '.js-map__pin' )
					const pinHtml     = pin.outerHTML
					const coordinates = {
						lat: parseFloat( item.el.dataset.mapLat ),
						lng: parseFloat( item.el.dataset.mapLng )
					}

					item.map    = new google.maps.Map(
						item.el,
						{
							center: coordinates,
							zoom: 16,
							styles: mapStyle
						}
					);
				const icon      = {
					url: item.el.dataset.pin,
					size: new google.maps.Size( 40, 50 ),
					origin: new google.maps.Point( 0, 0 ),
					anchor: new google.maps.Point( 20, 42 ),
					};
                item.marker = new google.maps.Marker(
						{
							position: coordinates,
							map: item.map,
							icon: icon
						}
					);
				}
			)
		}
	}

	document.addEventListener(
		'DOMContentLoaded',
		() => {
        const elements = document.querySelectorAll( '.js-map' );
        if (typeof mapsApiKey !== 'undefined' && elements) {
            new Map( elements );
        }
		}
	);
