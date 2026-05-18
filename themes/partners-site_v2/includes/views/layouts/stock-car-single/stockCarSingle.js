import Swiper from 'swiper/swiper-bundle';
import initYlProductDataPush from './youLead';

class StockCarSingleGallery {
	constructor(element) {
		this.element     = element;
		const nextButton = element.querySelector( '.js-stock-car-single-gallery__button--next' );
		const prevButton = element.querySelector( '.js-stock-car-single-gallery__button--prev' );

		const slider = new Swiper(
			element.querySelector( '.js-stock-car-single-gallery__slider' ),
			{
				spaceBetween: 12,
				slidesPerView: 'auto',
				on: {
					transitionEnd: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton ),
					afterInit: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton ),
					resize: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton )
				},
				breakpoints: {
					700: {
						slidesPerView: 3
					}
				}
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
		initYlProductDataPush();
		const gallery = document.querySelector( '.js-stock-car-single-gallery' );
		if (gallery) {
			new StockCarSingleGallery( gallery );
		}
	}
);