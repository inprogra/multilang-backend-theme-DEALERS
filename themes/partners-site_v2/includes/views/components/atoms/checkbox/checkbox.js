class Checkbox {
	constructor(el) {
		this.el        = el
		this.input     = this.el.querySelector( 'input' )
		this.icon      = this.el.querySelector( '.a-checkbox__icon' )
		this.labelMain = this.el.querySelector( '.a-checkbox__label-main' )

		this.icon.addEventListener(
			'click',
			() => {
				this.toggle()
			}
		)
		this.labelMain.addEventListener(
			'click',
			() => {
				this.toggle()
			}
		)

		this.details = this.el.querySelector( '.details' )
		if (this.details) {
			this.detailsText          = this.el.querySelector( '.details__text' )
			this.detailsTextInner     = this.el.querySelector( '.details__text-inner' )
			this.detailsTriggerExpand = this.el.querySelector( '.details__trigger-expand' )
			this.detailsTriggerShrink = this.el.querySelector( '.details__trigger-shrink' )

			this.detailsTriggerExpand.addEventListener(
				'click',
				() => {
					this.expandDetails()
				}
			)
			this.detailsTriggerShrink.addEventListener(
				'click',
				() => {
					this.shrinkDetails()
				}
			)
		}
	}

	dispatchEvent(name) {
		const event = new CustomEvent(
			name,
			{
				detail: {
					label: this.labelMain.innerText,
					value: this.input.checked
				}
			}
		);
		this.el.dispatchEvent( event )
	}

	toggle() {
		let checked = this.el.classList.toggle( 'is-active' );
		if (checked) {
			this.input.checked = true;

		} else {
			this.input.checked = false;
		}
		this.dispatchEvent( 'field-change' )
	}

	expandDetails() {
		this.details.classList.add( 'is-expanded' )
		this.detailsText.style.maxHeight = (this.detailsTextInner.offsetHeight + 24) + 'px';
		this.detailsTriggerExpand.classList.add( 'is-hidden' )
	}

	shrinkDetails() {
		this.details.classList.remove( 'is-expanded' )
		this.detailsText.style.removeProperty( 'max-height' );
		this.detailsTriggerExpand.classList.remove( 'is-hidden' )
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		let elements = document.querySelectorAll( '.a-checkbox' )
		elements.forEach(
			(el) => {
				new Checkbox( el );
			}
		)
	}
);

document.addEventListener('click', (e) => {
	// OTWIERANIE
	const moreBtn = e.target.closest('.moreInformation');
	if (moreBtn) {
		const wrapper   = moreBtn.closest('.informationPersonalData');
		const container = wrapper.querySelector('.detailsInformationContainer');

		if (container) {
			container.classList.add('is-open');
		}

		moreBtn.classList.add('is-hidden');
		return;
	}

	// ZAMYKANIE
	const lessBtn = e.target.closest('.lessInformation');
	if (lessBtn) {
		const container = lessBtn.closest('.detailsInformationContainer');
		const wrapper   = lessBtn.closest('.informationPersonalData');
		const moreBtn   = wrapper.querySelector('.moreInformation');

		if (container) {
			container.classList.remove('is-open');
		}

		if (moreBtn) {
			moreBtn.classList.remove('is-hidden');
		}
	}

});
