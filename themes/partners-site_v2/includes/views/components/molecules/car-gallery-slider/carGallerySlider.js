import Swiper from 'swiper/swiper-bundle';

class ThumbCarGallerySlider {
    constructor(element) {
        const nextButton = element.querySelector('.js-car-gallery-thumb-slider__button--next');
        const prevButton = element.querySelector('.js-car-gallery-thumb-slider__button--prev');

        this.slider = new Swiper(element, {
            spaceBetween: 8,
            slidesPerView: 5,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,
            init: false,
            allowTouchMove: false
        });

        if(nextButton && prevButton) {
            nextButton.addEventListener('click', () => this.slider.slideNext(300));
            prevButton.addEventListener('click', () => this.slider.slidePrev(300));

            this.slider.on('slideChange', (swiper) => {
                if (swiper.isEnd) {
                    nextButton.classList.add('is-hidden');
                    prevButton.classList.remove('is-hidden');
                } else if (swiper.isBeginning) {
                    prevButton.classList.add('is-hidden');
                    nextButton.classList.remove('is-hidden');
                } else {
                    nextButton.classList.remove('is-hidden');
                    prevButton.classList.remove('is-hidden');
                }
            });

            this.slider.on('afterInit', (swiper) => {
                if (swiper.isEnd) {
                    nextButton.classList.add('is-hidden');
                    prevButton.classList.remove('is-hidden');
                } else if (swiper.isBeginning) {
                    prevButton.classList.add('is-hidden');
                    nextButton.classList.remove('is-hidden');
                } else {
                    nextButton.classList.remove('is-hidden');
                    prevButton.classList.remove('is-hidden');
                }
            });
        }

        this.slider.init();
    }

    getSlider() {
        return this.slider;
    }
}

class CarGallerySlider {
    constructor(element, thumbGallery) {
        const nextButton = element.querySelector('.js-preview__button--next');
        const prevButton = element.querySelector('.js-preview__button--prev');

        const slider = new Swiper(element.querySelector('.swiper-container'), {
            spaceBetween: 12,
            slidesPerView: 1,
            thumbs: {
                swiper: thumbGallery.getSlider(),
            },
            on: {
                slideChange: (swiper) => {
                    if (swiper.isEnd) {
                        nextButton.classList.add('is-hidden');
                        prevButton.classList.remove('is-hidden');
                    } else if (swiper.isBeginning) {
                        prevButton.classList.add('is-hidden');
                        nextButton.classList.remove('is-hidden');
                    } else {
                        nextButton.classList.remove('is-hidden');
                        prevButton.classList.remove('is-hidden');
                    }
                },
                afterInit: (swiper) => {
                    if (swiper.isEnd) {
                        nextButton.classList.add('is-hidden');
                        prevButton.classList.remove('is-hidden');
                    } else if (swiper.isBeginning) {
                        prevButton.classList.add('is-hidden');
                        nextButton.classList.remove('is-hidden');
                    } else {
                        nextButton.classList.remove('is-hidden');
                        prevButton.classList.remove('is-hidden');
                    }
                }
            },
        });
        nextButton.addEventListener('click', () => slider.slideNext(300));
        prevButton.addEventListener('click', () => slider.slidePrev(300));
    }
}

export function initCarGallerySliders() {

    const carGalleryThumbSliders = Array.from(document.querySelectorAll('.js-car-gallery-thumb-slider'))
        .map((element) => new ThumbCarGallerySlider(element));
    let carGallerySliders = Array.from(document.querySelectorAll('.js-car-gallery-slider'));
    carGallerySliders
        .forEach((element, index) => new CarGallerySlider(element, carGalleryThumbSliders[index]));
}

document.addEventListener('DOMContentLoaded', () => {
    initCarGallerySliders();
});