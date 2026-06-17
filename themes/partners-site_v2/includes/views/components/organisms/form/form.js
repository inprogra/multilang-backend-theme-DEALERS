import axios from 'axios';
import qs from 'qs';
import Cookies from 'js-cookie'


export class Form {

	constructor(formElement, validationConfiguration, functions) {
    this.el = formElement;
    this.validationConfiguration = validationConfiguration;
    this.functions = functions ?? {};
    this.sendTime = 0;

    const fields = Array.from(this.el.querySelectorAll('[data-field-name]'));

    fields.forEach(field => {
        const validator = this.validationConfiguration[field.dataset.fieldName];
        if (!validator) return;

        const inputEl = field.querySelector('input, textarea, select, .js-textarea__field');
        if (!inputEl) return;

		if (inputEl.type === 'checkbox') {
			field.addEventListener('field-change', (e) => {
				console.log('ZMIANA CHECKBOXA:', e.detail.value); 
				this.validateField(validator, field.dataset.fieldName, e.detail.value);
			});
		} else {
			const eventName = validator.triggerEvent || 'input';
			inputEl.addEventListener(eventName, (e) => {
				this.validateField(validator, field.dataset.fieldName, e.target.value);
			});
		
			inputEl.addEventListener('blur', (e) => {
				this.validateField(validator, field.dataset.fieldName, e.target.value);
			});
		}
		

        if (inputEl.type !== 'checkbox') {
            inputEl.addEventListener('blur', (e) => {
                this.validateField(validator, field.dataset.fieldName, e.target.value);
            });
        }
    });

    this.el.addEventListener('submit', event => this.submit(event));
}

	

validateField(validator, fieldName, fieldValue) {
    const field = this.el.querySelector(`[data-field-name="${fieldName}"]`);
    if (!field) return false;

    const input = field.querySelector('input, textarea, select, .js-textarea__field');

    let value;
    if (input && input.type === 'checkbox') {
        value = input.checked; 
    } else {
        value = fieldValue !== undefined ? fieldValue : (input ? input.value : '');
    }

 
    let errorPlaceholder = this.el.querySelector(`[data-error-for="${fieldName}"]`) ||
                           document.querySelector(`[data-error-for="${fieldName}"]`);
    if (!errorPlaceholder) return false;

    const errorTextEl = errorPlaceholder.querySelector('.a-error__text');

    let hasError = false;
    let message = '';

    if (validator.validators) {
        hasError = validator.validators.some(v => !v.validate(value));
        if (hasError) {
            const failingValidator = validator.validators.find(v => !v.validate(value));
            message = failingValidator?.message || '';
        }
    } else if (validator.validate && validator.messages) {
        const result = validator.validate(value);
        if (result) {
            hasError = true;
            message = validator.messages[result] || '';
        }
    }

    if (errorTextEl) errorTextEl.innerText = hasError ? message : '';

    if (hasError) {
        errorPlaceholder.classList.add('is-visible', 'is-active');
        field.classList.add('has-error');
    } else {
        errorPlaceholder.classList.remove('is-visible', 'is-active');
        field.classList.remove('has-error');
    }

    return hasError;
}


	submit(event) {
		event.preventDefault();
	

		const fields = this.el.querySelectorAll('.form__content-field');
		fields.forEach(f => f.style.display = '');

		const formData = new FormData( event.target );
		let errorCount = 0

		Object.keys(this.validationConfiguration).forEach(fieldName => {
			let value;
			if (fieldName === 'leadType') {
				value = formData.get('lead-type');
			} else {
				value = fieldName.includes('[]') ? formData.getAll(fieldName) : formData.get(fieldName);
			}
		
			this.validateField(this.validationConfiguration[fieldName], fieldName, value) ? errorCount++ : false;
		});
		

		if (formData.has('lead-type')) {
			const leadTypeValue = formData.get('lead-type');
		
			if (!leadTypeValue || leadTypeValue.trim() === '') {
		
				const field = this.el.querySelector('[data-field-name="leadType"]');
				if (field) {
					field.classList.add('has-error');
				}
		
				const errorPlaceholder = this.el.querySelector('[data-error-for="leadType"]');
				if (errorPlaceholder) {
					errorPlaceholder.classList.add('is-visible', 'is-active');
		
					const errorTextEl = errorPlaceholder.querySelector('.a-error__text');
					if (errorTextEl) {
						errorTextEl.innerText = 'Wybierz kategorię!';
					}
				}
		
				errorCount++;
			}
		}
	
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
			const value = email.trim();
			if (!value) return 'empty';
	
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(value)) return 'invalid';
	
			return null;
		},
		messages: {
			empty: 'Podaj adres e-mail',
			invalid: 'Wpisz prawidłowy adres e-mail (np. jan.kowalski@workmail.com)'
		}
	},
	
	leadType: {
		validate: (value) => value.trim() !== '',
		message: 'Wybierz kategorię!'
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
		validators: [{
			validate: (value) => value.trim().length > 0,
			message: 'Podaj swoje imię'
		}]
	},
	surname: {
		validators: [{
			validate: (value) => value.trim().length > 0,
			message: 'Podaj swoje nazwisko'
		}]
	},
	phoneNumber: {
		validate: (value) => {
			const digits = value.replace(/\D/g, '');
			if (!digits.length) return 'empty';       
			if (digits.length < 9) return 'short';   
			return null;                               
		},
		messages: {
			empty: 'Podaj numer telefonu',
			short: 'Podaj prawidłowy numer telefonu'
		}
	},
	email: {
		validate: (email) => {
			const value = email.trim();
			if (!value) return 'empty';
	
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(value)) return 'invalid';
	
			return null;
		},
		messages: {
			empty: 'Podaj adres e-mail',
			invalid: 'Wpisz prawidłowy adres e-mail (np. jan.kowalski@workmail.com)'
		}
	},
	
	leadType: {
		validators: [{
			validate: (value) => typeof value === 'string' && value.trim() !== '',
			message: 'Wybierz kategorię!'
		}],
		triggerEvent: 'change'
	},
	
	message: { 
		validators: [formValidators.requiredText]
	},

	dataProcessingConsent: {
		validators: [{
			validate: (value) => value === true || value === 'on',
			message: 'Musisz wyrazić zgodę na przetwarzanie danych'
		}],
		triggerEvent: 'change'
	},

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




