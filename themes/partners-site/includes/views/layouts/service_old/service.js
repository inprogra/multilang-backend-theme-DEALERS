import {ScrollToSection} from '../../../../assets/private/js/scrollToSection';

class Service {
	constructor(element) {
		this.element             = element;
		this.goToAccordionButton = element.querySelector( '.js-service__go-to-accordion-button' );
		this.accordionSection    = element.querySelector( '.js-service__accordion-section' );
		if (this.goToAccordionButton) {
		this.goToAccordionButton.addEventListener( 'click', (e) => this.handleGoToAccordionButtonClick( e ) );
	}
	}

	handleGoToAccordionButtonClick(e) {
		e.preventDefault();
		ScrollToSection.scroll( this.accordionSection.offsetTop );
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		const serviceElement = document.querySelector( '.js-service' );
		if (serviceElement) {
			new Service( serviceElement );
		}
	}
);