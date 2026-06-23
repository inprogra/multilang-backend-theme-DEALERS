class Textarea {
	constructor(el) {
		this.el    = el
		this.input = this.el.querySelector( '.js-textarea__field' );
		this.label = this.el.querySelector( '.js-textarea__label' );

		this.input.addEventListener(
			'focus',
			() => {
				this.addActive()
				this.focus()
			}
		)
		this.input.addEventListener(
			'blur',
			() => {
				this.removeActive()
				this.blur()
			}
		)
	}

	addActive() {
		this.el.classList.add( 'is-active' )
	}

	removeActive() {
		if ( ! this.input.value) {
			this.el.classList.remove( 'is-active' )
		}
	}

	focus() {
		this.el.classList.add( 'is-focused' )
	}

	blur() {
		this.el.classList.remove( 'is-focused' )
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		var elements = document.querySelectorAll( '.js-textarea' )
		elements.forEach(
			(el) => {
				new Textarea( el );
			}
		)
	}
);
