class TwoColumnList {
	constructor(element) {
		this.el      = element
		this.inner   = element.querySelector( '.js-two-column-list__inner' )
		this.top     = element.querySelector( '.js-two-column-list__top' )
		this.more    = element.querySelector( '.js-two-column-list__more' )
		this.columns = element.querySelectorAll( '.js-two-column-list__column' )

		this.expandTime = parseFloat( window.getComputedStyle( this.inner ).transitionDuration ) * 1000;
		this.isBlocked  = false

		this.more.addEventListener(
			'click',
			() => {
				if ( ! this.isBlocked) {
					this.isBlocked = true
					if (this.el.classList.contains( 'is-expanded' )) {
						this.shrink()
					} else {
						this.expand()
					}
				}
			}
		)
	}

	expand() {
		const topHeight                    = this.top.offsetHeight;
		const columnsHeight                = Array.from( this.columns ).reduce( (accumulator, item) => accumulator + item.offsetHeight, 0 )
		this.inner.style.maxHeight         = topHeight + 'px'
		setTimeout(
			() => {
				this.el.classList.add( 'is-expanding' )
				this.inner.style.maxHeight = columnsHeight + 'px'

				setTimeout(
					() => {
						this.el.classList.remove( 'is-expanding' )
						this.el.classList.add( 'is-expanded' )
						this.isBlocked = false
					},
					this.expandTime
				)
			},
			10
		)
	}

	shrink() {
		this.inner.style.maxHeight     = this.inner.offsetHeight + 'px'
		setTimeout(
			() => {
				this.inner.style.maxHeight = 0 + 'px'
				setTimeout(
					() => {
						this.el.classList.remove( 'is-expanded' )
						this.inner.style.removeProperty( 'max-height' )
						this.isBlocked = false
					},
					this.expandTime
				)
			},
			10
		)
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		var elements = document.querySelectorAll( '.js-two-column-list' )
		elements.forEach(
			(el) => {
				new TwoColumnList( el );
			}
		)
	}
);