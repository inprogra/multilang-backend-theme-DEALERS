import noUiSlider from 'nouislider';
import Inputmask from 'inputmask';

class SetableInput {
	constructor(element) {
		this.element = element;
	}

	getValue() {
		return this.element.querySelector( 'input' ).value;
	}

	setValue(value) {
		this.element.querySelector( 'input' ).value = value;
	}

	onChange(onChange) {
		this.element.addEventListener( 'change', (event) => onChange( event.target.value ) );
	}

	getInput() {
		return this.element.querySelector( 'input' );
	}
}

class PriceRange {
	constructor(el) {
		this.el          = el
		this.elementMin  = new SetableInput( this.el.querySelector( '.js-input-range__min' ) );
		this.elementMax  = new SetableInput( this.el.querySelector( '.js-input-range__max' ) );
		this.rangeSlider = el.querySelector( '.js-input-range__slider' )
		this.slider      = this.createSlider(
			this.rangeSlider,
			parseInt( this.elementMin.getValue() ),
			parseInt( this.elementMax.getValue() ),
			parseInt( this.el.dataset.selectedMin ),
			parseInt( this.el.dataset.selectedMax )
		);
		this.applyPriceMasks();
		this.elementMin.onChange( (value) => this.slider.set( [value, null] ) );
		this.elementMax.onChange( (value) => this.slider.set( [null, value] ) );
		this.slider.on( 'update', (values, handle) => this.handleSliderUpdate( values, handle ) );
	}

	createSlider(element, min, max, selectedMin, selectedMax) {
		return noUiSlider.create(
			element,
			{
				start: [selectedMin, selectedMax],
				connect: true,
				range: {
					'min': min,
					'max': max
				}
			}
		);
	}

	handleSliderUpdate(values, handle) {
		const value = values[handle];

		if (handle) {
			this.elementMax.setValue( Math.round( value ) );
		} else {
			this.elementMin.setValue( Math.round( value ) );
		}
	}

	applyPriceMasks() {
		const mask = new Inputmask(
			'currency',
			{
				groupSeparator: " ",
				suffix: " zł",
				allowMinus: false,
				digits: 0,
				digitsOptional: false,
				rightAlign: false,
				removeMaskOnSubmit: false,
				autoUnmask: true,
			}
		);
		mask.mask( this.elementMin.getInput() );
		mask.mask( this.elementMax.getInput() );
	}

}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		const rangeElements = document.querySelectorAll( '.js-input-range' )
		rangeElements.forEach(
			(el) => {
				new PriceRange( el );
			}
		);
	}
);