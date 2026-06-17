import {Form, defaultValidationConfiguration} from '../form/form'
import {ScrollToSection} from '../../../../../assets/private/js/scrollToSection';

class FormTestDrive {
	constructor(el) {
		this.el = el

		new Form(
			this.el,
			defaultValidationConfiguration,
			{
				error: () => {
					this.error()
				},
			}
		)

	}

	error() {
		const firstField         = this.el.querySelector( '.has-error[data-field-name]' )
		const firstFieldPosition = firstField.getBoundingClientRect();
		ScrollToSection.scroll( firstFieldPosition.top + window.scrollY - 32 )
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		const el = document.querySelector( '.js-form-test-drive' )
		if (el) {
			new FormTestDrive( el );
		}
	}
);