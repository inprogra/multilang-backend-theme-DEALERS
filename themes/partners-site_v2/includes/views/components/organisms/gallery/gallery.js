import Swiper from 'swiper/swiper-bundle';

class Gallery {
	constructor(element) {
		this.element     = element;
		const nextButton = element.querySelector( '.js-gallery__button--next' );
		const prevButton = element.querySelector( '.js-gallery__button--prev' );

		const slider = new Swiper(
			element.querySelector( '.js-gallery__slider' ),
			{
				spaceBetween: 12,
				slidesPerView: 'auto',
				on: {
					transitionEnd: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton ),
					afterInit: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton ),
					resize: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton )
				},
			}
		);

		nextButton.addEventListener( 'click', () => slider.slideNext( 300 ) );
		prevButton.addEventListener( 'click', () => slider.slidePrev( 300 ) );
	}

	toggleSliderButtons(swiper, prev, next) {
		if (swiper.isEnd) {
			next.classList.add( 'is-hidden' );
		} else {
			next.classList.remove( 'is-hidden' );
		}
		if (swiper.isBeginning) {
			prev.classList.add( 'is-hidden' );
		} else {
			prev.classList.remove( 'is-hidden' );
		}
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		const galleries = Array.from( document.querySelectorAll( '.js-gallery' ) );
    if (galleries) {
        galleries.forEach(
        gallery => {
            new Gallery( gallery );
        }
        )
    }
	}
);