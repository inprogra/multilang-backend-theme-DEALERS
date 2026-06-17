import Swiper from 'swiper/swiper-bundle';
import {initCarGallerySliders} from "../../components/molecules/car-gallery-slider/carGallerySlider";

class VersionsSlider {
    constructor(element) {
        this.element = element;
        const isDesktopNavigationEnabled = (element.dataset.allowNavigation == 'true');
        const nextButton = element.querySelector('.js-versions-slider__button--next');
        const prevButton = element.querySelector('.js-versions-slider__button--prev');

        const slider = new Swiper(element.querySelector('.swiper-container'), {
            spaceBetween: 0,
            slidesPerView: 'auto',
            centeredSlides: true,
            on: {
                slideChange: (swiper) => this.onSlideChange(swiper.slides[swiper.realIndex]),
                transitionEnd: (swiper) => this.toggleSliderButtons(swiper, prevButton, nextButton),
                afterInit: (swiper) => this.toggleSliderButtons(swiper, prevButton, nextButton),
                resize: (swiper) => this.toggleSliderButtons(swiper, prevButton, nextButton)
            },
            breakpoints: {
                992: {
                    centeredSlides: false,
                    allowTouchMove: isDesktopNavigationEnabled
                }
            }
        });
        nextButton.addEventListener('click', () => slider.slideNext(300));
        prevButton.addEventListener('click', () => slider.slidePrev(300));

        const slides = Array.from(element.querySelectorAll('.swiper-slide'));

        slides.forEach((slide, index) => {
            slide.addEventListener('click', () => {
                slider.slideTo(index);
                this.onSlideChange(slide);
            });
        })
    }

    toggleSliderButtons(swiper, prev, next) {
        if (swiper.isEnd) {
            next.classList.add('is-hidden');
        } else {
            next.classList.remove('is-hidden');
        }
        if (swiper.isBeginning) {
            prev.classList.add('is-hidden');
        } else {
            prev.classList.remove('is-hidden');
        }
    }

    onSlideChange(slide) {
        this.element.querySelector('.swiper-slide.is-active').classList.remove('is-active');
        const newActiveSlideIndex = slide.dataset.index;
        slide.classList.add('is-active');

        const currentActiveVersionContent = document.querySelector('.js-model-single__version.is-active');

        if(currentActiveVersionContent) {
            currentActiveVersionContent.classList.remove('is-active');
        }

        const newActiveVersionContent = document.querySelector(`.js-model-single__version[data-version="${newActiveSlideIndex}"]`);

        if(newActiveVersionContent) {
            newActiveVersionContent.classList.add('is-active');
            initCarGallerySliders();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const element = document.querySelector('.js-versions-slider__slider');
    if(element) {
        new VersionsSlider(element);
    }
});