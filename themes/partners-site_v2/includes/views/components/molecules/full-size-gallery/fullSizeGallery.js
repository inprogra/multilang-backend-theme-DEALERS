import PhotoSwipe from 'photoswipe'
import PhotoSwipeUI_Default from 'photoswipe/dist/photoswipe-ui-default'

class FullSizeGallery {
    constructor(pswpElement, parent) {
        this.pswpElement = pswpElement
        this.parent = parent
        this.open = this.open.bind(this)

        const triggers = parent.querySelectorAll('.js-full-size-gallery__trigger')
        triggers.forEach(trigger => trigger.addEventListener('click', event => this.open(event)))
    }

    open(event) {
        this.getItems();
        let target = event.target;

        if(!target.classList.contains('js-full-size-gallery__trigger')) {
            target = target.closest('.js-full-size-gallery__trigger');
        }

        const index = target.dataset.galleryIndex ? parseInt(target.dataset.galleryIndex, 10) : 1;

        const options = {
            index: index,
            loadingIndicatorDelay: 0,

            barsSize: {top: 44, bottom: 44},
            captionEl: false,
            fullscreenEl: false,
            zoomEl: false,
            shareEl: false,

            bgOpacity: 0.8,
            showHideOpacity: true,
            showAnimationDuration: 500,
            hideAnimationDuration: 500,
        }

        const gallery = new PhotoSwipe(this.pswpElement, PhotoSwipeUI_Default, this.getItems(), options);
        gallery.init();
    }

    getItems() {
        const images = this.parent.querySelectorAll('[data-gallery-image]')
        const items = []

        images.forEach((image) => {
            items.push({
                src: image.dataset.galleryImage,
                w: image.dataset.galleryWidth,
                h: image.dataset.galleryHeight,
            })
        })
        return items
    }
}

export function initFullSizeGallery () {
    const galleries = document.querySelectorAll('.js-full-size-gallery')
    const pswpElement = document.querySelectorAll('.pswp')[0]
    galleries.forEach(gallery => new FullSizeGallery(pswpElement, gallery))
}

document.addEventListener('DOMContentLoaded', () => {
    initFullSizeGallery()
});