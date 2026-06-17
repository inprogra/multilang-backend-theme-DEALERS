import { toggleSlide } from '../../../../../assets/private/js/toggleSlide';

class AccordionSection {
    constructor(element) {
        this.element = element;
        this.accordionItems = Array.from(this.element.querySelectorAll('.js-accordion__item'));
        this.initToggleAccordion();
    }

    initToggleAccordion() {
        this.accordionItems.forEach(item => {
            if(item.classList.contains('is-active')) {
                item.querySelector('.js-accordion__item-bottom').style.maxHeight = item.querySelector('.js-accordion__item-bottom-inner').offsetHeight + 'px';
            }
            item.querySelector('.js-accordion__item-top').addEventListener('click', () => {
                toggleSlide(item,
                    item.querySelector('.js-accordion__item-bottom'),
                    () => item.querySelector('.js-accordion__item-bottom-inner').offsetHeight);
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
   Array.from(document.querySelectorAll('.js-accordion')).forEach(element => {
       new AccordionSection(element);
   });
});