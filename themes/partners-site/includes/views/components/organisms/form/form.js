import axios from 'axios';
import qs from 'qs';
import Cookies from 'js-cookie'

export class Form {
	constructor(formElement, validationConfiguration, functions) {
		this.el                      = formElement;
		this.validationConfiguration = validationConfiguration;
		this.functions               = functions ?? {};

		this.sendTime = 0

		// Attach validation to custom change/blur event
		Array.from( this.el.querySelectorAll( '[data-field-name]' ) ).map(
			field => {
				const validator = this.validationConfiguration[field.dataset.fieldName];
            if (validator) {
                field.addEventListener(
                validator.triggerEvent || 'field-blur',
                (event) => {
                    this.validateField( validator, field.dataset.fieldName, event.detail.value );
                }
                );
            }
			}
		);

		this.el.addEventListener( 'submit', event => this.submit.bind( this )( event ) );
	}

	validateField(validator, fieldName, fieldValue) {
		let error = validator.validators
			.filter( validator => ! validator.validate( fieldValue ) )
			.map( validator => validator.message )[0];

		let field = this.el.querySelector( `[data-field-name="${fieldName}"]` );
		if (field) {
			let errorPlaceholder = this.el.querySelector( `[data-error-for="${fieldName}"]` );
			if (errorPlaceholder) {
				errorPlaceholder.querySelector( '.a-error__text' ).innerText = error;
				if (error) {
					errorPlaceholder.classList.add( 'is-visible' )
					field.classList.add( 'has-error' )
				} else {
					errorPlaceholder.classList.remove( 'is-visible' )
					field.classList.remove( 'has-error' )
				}
			} else {
				console.error( `Error placeholder for ${fieldName} not found ! ` );
			}
			return ! ! error;
		} else {
			console.error( 'Field ' + fieldName + ' not found!') 
				return false;
                
				
			
		}
	}

	submit(event) {
		event.preventDefault();
		const formData = new FormData( event.target );
		let errorCount = 0

		// Validate all fields
		Object.keys( this.validationConfiguration ).forEach(
			fieldName => {
            this.validateField(
				this.validationConfiguration[fieldName],
				fieldName,
				(fieldName.includes( '[]' ) ? formData.getAll( fieldName ) : formData.get( fieldName ))
			) ? errorCount++ : false;
			}
		);

		// Send
		if (errorCount < 1) {
			if (this.functions.send) {
				this.functions.send( this.el )
			}

			this.sendTime = Date.now()
			this.el.classList.add( 'is-loading' )

			const data = {
				'action': 'leadReceiver',
				'originUrl': window.location.href,
				'referrer': document.referrer
			};

			Array.from( formData.keys() ).forEach(
				key => {
                if (key.includes( '[]' )) {
                    data[key.slice( 0, key.length - 2 )] = formData.getAll( key );
                } else {
						data[key] = formData.get( key ) === 'on' ? true : formData.get( key );
                }
				}
			);

			if (Cookies.get( 'cookie-consent' ) === 'all' && Cookies.get( 'ylid' )) {
				data['youLead'] = {
					'ylid': Cookies.get( 'ylid' ),
					'ylssid': Cookies.get( 'ylssid' ),
					'ylutm': Cookies.get( 'ylutm' )
				};
			}

			axios(
				{
					method: 'POST',
					headers: {'content-type': 'application/x-www-form-urlencoded'},
					url: '/wp/wp-admin/admin-ajax.php',
					data: qs.stringify( data )
				}
			).then(
				response => {
                let dataLayerEvent = 'form_sent';
                if (data.origin === 'test-drive') {
                    dataLayerEvent = 'form_sent_test_drive'
                } else if (data.origin === 'service') {
						dataLayerEvent = 'form_sent_service'
                }

						const responseData = JSON.parse( response.data )
					if (responseData && responseData.returnData.ylid) {
						if (responseData.returnData.ylid !== '') {
							const ylData = window.ylData = window.ylData || [];
							ylData.push( {'switch': {'ylSetId': responseData.returnData.ylid}} )
						}
					}

					if (this.functions.success) {
						this.functions.success( this.el )
					}

						const timeDiff = Date.now() - this.sendTime
						setTimeout(
							() => {
                            this.el.classList.add( 'is-completed' )
								this.el.classList.remove( 'is-loading' )

							},
							(timeDiff < 200 ? Math.abs( timeDiff - 200 ) : 0)
						)
				if (window.dataLayer) {
					window.dataLayer.push( {'event': dataLayerEvent, 'type': data.origin ?? 'default',} );
				}

					}
			).catch(
				error => {
                console.log( error );
                console.log( error.response.data )
					}
			);
		} else {
			if (this.functions.error) {
				this.functions.error( this.el );
			}
		}
	}
}

export const formValidators = {
	showroom: {
		validate: (value) => {
			return ! ! value;
		},
		message: 'Musisz wybrać jeden z salonów!'
	},
	requiredText: {
		validate: (value) => {
			return value.trim().length;
		},
		message: 'Pole jest wymagane!'
	},
	email: {
		validate: (email) => {
			return email.includes( '@' )
		},
		message: 'Nieprawidłowy email!'
	},
	consent: {
		validate: (value) => {
			return ! ! value;
		},
		message: 'Ta zgoda jest wymagana!'
	},
	valueLength: {
		validate: (value) => {
			return value && value.length;
		},
		message: 'Pole nie może być puste!'
	}
}

export const defaultValidationConfiguration = {
	showroom: {
		validators: [formValidators.showroom],
		triggerEvent: 'field-change'
	},
	name: {
		validators: [formValidators.requiredText]
	},
	surname: {
		validators: [formValidators.requiredText]
	},
	phoneNumber: {
		validators: [formValidators.requiredText]
	},
	email: {
		validators: [
			formValidators.requiredText,
			formValidators.email
		]
	},
	dataProcessingConsent: {
		validators: [formValidators.consent],
		triggerEvent: 'field-change'
	}
};

export const defaultFunctions = {
	error: (el) => {
		const firstField      = el.querySelector( '.has-error[data-field-name]' )
		firstField.scrollIntoView(
			{
				behavior: 'smooth',
			}
		);
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
    const el = document.querySelector( '.js-form' );
    console.log( el );
    if (el) {
        new Form( el, defaultValidationConfiguration, defaultFunctions );

    }
	//remove console errors
    if (document.getElementById( 'reset__filters' ) && document.getElementById( 'reset__filters' ).length > 0) {
        document.getElementById( 'reset__filters' ).addEventListener(
        'click',
        function () {
            console.log( window.location.href );
            if (window.location.href.includes( '?' )) {
                const url         = window.location.href.split( '?' )[0];
                top.location.href = url;
            } else {
                var list = document.getElementById( "active_filters" ).getElementsByTagName( "li" );
                Array.from( list ).forEach(
                function (e) {
                    var event = new Event( 'click' );
                    document.querySelector( '[data-value="' + e.getAttribute( 'data-element' ) + '"]' ).dispatchEvent( event );
                }
                );

            }
        }
        )
    }
	if (document.getElementById( 'form_block_8' )) {
    var block_8 = document.getElementById( 'form_block_8' )
		var block_6 = document.getElementById( 'form_block_6' );
    var block_0 = document.getElementById( 'form_block_0' );
    var block_1 = document.getElementById( 'form_block_1' );
    var block_2 = document.getElementById( 'form_block_2' );
    var block_3 = document.getElementById( 'form_block_3' );
    var block_7 = document.getElementById( 'form_block_7' );
    var block_5 = document.getElementById( 'form_block_5' );
    if (window.innerWidth < 992) {

        // var block_1 = document.getElementById('form_block_1');

        document.getElementsByClassName( 'js-stock__filters-more-inner' )[0].prepend( block_1 );
        document.getElementsByClassName( 'js-stock__filters-more-inner' )[0].prepend( block_2 );
        document.getElementsByClassName( 'js-stock__filters-more-inner' )[0].prepend( block_3 );
        document.getElementById( 'mobile_fields' ).appendChild( block_8 );
        document.getElementById( 'mobile_fields' ).appendChild( block_6 );

    } else {
			document.getElementsByClassName( 'filters__content' )[0].prepend( block_3 );
			document.getElementsByClassName( 'filters__content' )[0].prepend( block_2 );
			document.getElementsByClassName( 'filters__content' )[0].prepend( block_1 );
			document.getElementsByClassName( 'filters__content' )[0].prepend( block_0 );
			block_5.after( block_6 );
			block_7.after( block_8 );
    }

		window.addEventListener(
			'resize',
			function (event) {
				if (window.innerWidth < 992) {

					// var block_1 = document.getElementById('form_block_1');

					document.getElementsByClassName( 'js-stock__filters-more-inner' )[0].prepend( block_1 );
					document.getElementsByClassName( 'js-stock__filters-more-inner' )[0].prepend( block_2 );
					document.getElementsByClassName( 'js-stock__filters-more-inner' )[0].prepend( block_3 );
					document.getElementById( 'mobile_fields' ).appendChild( block_8 );
					document.getElementById( 'mobile_fields' ).appendChild( block_6 );

				} else {
					document.getElementsByClassName( 'filters__content' )[0].prepend( block_3 );
					document.getElementsByClassName( 'filters__content' )[0].prepend( block_2 );
					document.getElementsByClassName( 'filters__content' )[0].prepend( block_1 );
					document.getElementsByClassName( 'filters__content' )[0].prepend( block_0 );
					block_5.after( block_6 );
					block_7.after( block_8 );

				}
			}
		);
	}
	}
);
