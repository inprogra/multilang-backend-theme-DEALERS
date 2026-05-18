import Swiper from 'swiper/swiper-bundle';
import {debounce} from 'debounce'

class HeroSlider {
	constructor(element) {
		this.element         = element
		this.swiperEl        = element.querySelector( '.swiper-container' )
		this.nextButton      = element.querySelector( '.js-hero-gallery__button--next' );
		this.prevButton      = element.querySelector( '.js-hero-gallery__button--prev' );
		this.paginationItems = Array.from( element.querySelectorAll( '.js-hero-gallery__paginationItem' ) );
	
		window.addEventListener(
			"resize",
			debounce(
				() => {
                this.initSlider()
				}
			)
		);

		this.initSlider()
	}

	initSlider() {
		//toRemove
		var autoplay = true;
		if ( ! ! this.swiperEl.offsetParent && ! this.slider) {    // conditional is element visible
			this.slider = new Swiper(
				this.swiperEl,
				{
					loop: true,
					autoplay: {
						enabled:autoplay,
						delay: 5000,
					},
					speed: 800
				}
			);
			this.slider.on(
				'slideChange',
				swiper => {
					this.changePagination( swiper.realIndex )
				}
			);
			this.nextButton.addEventListener( 'click', () => this.slider.slideNext( 800 ) );
			this.prevButton.addEventListener( 'click', () => this.slider.slidePrev( 800 ) );

			this.paginationItems.forEach(
				(item, index) => {
					item.addEventListener(
					'click',
					() => {
						this.changeSlide( index )
						}
					)
				}
			)
		}
	}

	changeSlide(index) {
		this.slider.slideToLoop( index )
		this.changePagination( index )
	}

	changePagination(index) {
		const last = this.element.querySelector( '.js-hero-gallery__paginationItem.is-active' );
		last.classList.remove( 'is-active' )
		this.paginationItems[index].classList.add( 'is-active' )
	}
}

export function initHeroGallery() {
	const element = document.querySelector( '.js-hero-gallery' );
	if (element) {
		new HeroSlider( element );
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		initHeroGallery();
	}
);