import Swiper from 'swiper/swiper-bundle';

class StockCarsSlider {
	constructor(element) {
		this.element     = element;
		const nextButton = element.querySelector( '.js-stock-cars-slider__button--next' );
		const prevButton = element.querySelector( '.js-stock-cars-slider__button--prev' );

		const slider = new Swiper(
			element.querySelector( '.js-stock-cars-slider__slider' ),
			{
				spaceBetween: 12,
				slidesPerView: 'auto',
				on: {
					transitionEnd: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton ),
					afterInit: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton ),
					resize: (swiper) => this.toggleSliderButtons( swiper, prevButton, nextButton )
				},
				breakpoints: {
					992: {
						slidesPerView: 4,
						spaceBetween: 24,
						allowTouchMove: false
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

export function initStockCarsSliders() {
	const stockCarsSliders = Array.from( document.querySelectorAll( '.js-stock-cars-slider' ) );
	stockCarsSliders
		.forEach( (element) => new StockCarsSlider( element ) );
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		const element = document.querySelector( '.js-stock-cars-slider' );
		if (element) {
			new StockCarsSlider( element );
		}
	}
);