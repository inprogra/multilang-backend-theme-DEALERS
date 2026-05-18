import LazyLoad from "vanilla-lazyload";

class LazyLoading {
    constructor() {
        if (this.isSupportingNativeLazyLoading()) {
            this.initNativeLazyLoading();
        } else {
            this.initLibraryLazyLoading();
        }
    }

    isSupportingNativeLazyLoading() {
        return 'loading' in HTMLImageElement.prototype;
    }

    initNativeLazyLoading() {
        const lazyLoadingImages = document.querySelectorAll('[loading]');
        lazyLoadingImages.forEach(image => {
            if (image.dataset.src) {
                image.src = image.dataset.src;
            }

            if (image.dataset.srcset) {
                image.srcset = image.dataset.srcset;
            }

            if (image.dataset.sizes) {
                image.sizes = image.dataset.sizes;
            }

        });
    }

    initLibraryLazyLoading() {
        this.libraryLazyload = new LazyLoad({
            elements_selector: '.js-lazyload',
            class_loading: 'js-lazyload--loading'
        });
    }

    updateLazyLoading() {
        if (this.isSupportingNativeLazyLoading()) {
            this.updateNativeLazyLoading();
        } else {
            this.updateLibraryLazyLoading();
        }
    }

    updateNativeLazyLoading() {
        const lazyLoadingImages = document.querySelectorAll('[loading]');
        lazyLoadingImages.forEach(image => {
            if (image.dataset.src && !image.src) {
                image.src = image.dataset.src;
            }

            if (image.dataset.srcset && !image.srcset) {
                image.srcset = image.dataset.srcset;
            }

            if (image.dataset.sizes && !image.sizes) {
                image.sizes = image.dataset.sizes;
            }

        });
    }

    updateLibraryLazyLoading() {
        this.libraryLazyload.update();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.lazyLoading = new LazyLoading();
});