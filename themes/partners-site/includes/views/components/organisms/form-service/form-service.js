import {Form, defaultValidationConfiguration} from '../form/form'
import {ScrollToSection} from '../../../../../assets/private/js/scrollToSection';

class FormServiceModel {
    constructor(el) {
        this.el = el.querySelector('.js-form-service-models')
        this.toggler = this.el.querySelector('.js-form-service-models__toggle')
        this.container = this.el.querySelector('.js-form-service-models__container')
        this.inner = this.el.querySelector('.js-form-service-models__inner')

        if (this.toggler) {
            this.toggler.addEventListener('click', (e) => {
                e.preventDefault()
                this.toggle()
            })
        }
    }

    toggle() {
        const newToggleText = this.toggler.dataset.altText
        const oldToggleText = this.toggler.innerText
        this.toggler.innerText = newToggleText
        this.toggler.dataset.altText = oldToggleText

        if (!this.el.classList.contains('is-expanded')) {
            const height = this.inner.offsetHeight
            this.el.classList.add('is-expanded')
            this.container.style.maxHeight = height + 'px'
        } else {
            this.el.classList.remove('is-expanded')
            this.container.style.removeProperty('max-height')
        }
    }
}

class FormServiceCategories {
    constructor(el) {
        const categories = el.querySelectorAll('.js-form-service-category')
        this.items = []
        categories.forEach((el) => {
            const item = {
                el: el,
                triggers: el.querySelectorAll('.js-form-service-category__trigger'),
                input: el.querySelector('input'),
                description: el.querySelector('.js-form-service-category__description'),
                descriptionInner: el.querySelector('.js-form-service-category__description-inner'),
                expand: el.querySelector('.js-form-service-category__description-expand'),
                shrink: el.querySelector('.js-form-service-category__description-shrink'),
            };

            item.triggers.forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    this.toggle(item)
                })
            })

            item.expand.addEventListener('click', (e) => {
                e.preventDefault()
                this.expand(item)
            })

            item.shrink.addEventListener('click', (e) => {
                e.preventDefault()
                this.shrink(item)
            })

            this.items.push(item)
        })
    }

    toggle(item) {
        if (!item.el.classList.contains('is-active')) {
            item.el.classList.add('is-active')
            item.input.checked = true;
        } else {
            item.el.classList.remove('is-active')
            item.input.checked = false;
        }
    }

    expand(item) {
        item.description.classList.add('is-hidden')
        setTimeout(() => {
            item.description.classList.add('is-expanded')
            setTimeout(() => {
                const height = item.descriptionInner.offsetHeight
                item.description.style.maxHeight = height + 'px'
                setTimeout(() => {
                    item.description.classList.remove('is-hidden')
                }, 200)
            }, 10)
        }, 200)
    }

    shrink(item) {
        item.description.classList.add('is-hidden')
        setTimeout(() => {
            item.description.style.removeProperty('max-height')
            setTimeout(() => {
                item.description.classList.remove('is-expanded')
                setTimeout(() => {
                    item.description.classList.remove('is-hidden')
                }, 10)
            }, 200)
        }, 200)
    }
}

class FormService {
    constructor(el) {
        this.el = el
        this.inner = el.querySelector('.js-form-service__inner')
        this.stepForm = el.querySelector('.js-form-service__step-form')
        this.stepThanks = el.querySelector('.js-form-service__step-thanks')
        this.form = el.querySelector('.js-form-service__form')
        this.thanks = el.querySelector('.js-form-service__thanks')
        this.revertBtn = el.querySelector('.js-form-service__revert-btn')

        new FormServiceModel(el)
        new FormServiceCategories(el)

        new Form(
            this.el.querySelector('form'),
            defaultValidationConfiguration,
            {
                send: () => {
                    this.send()
                },
                success: () => {
                    this.success()
                },
                error: () => {
                    this.error()
                },
            }
        )

        this.revertBtn.addEventListener('click', (e) => {
            e.preventDefault()
            this.revert()
        })
    }

    send() {
        this.el.classList.add('is-loading')
    }

    success() {
        const height = this.form.offsetHeight;

        ScrollToSection.scroll(this.el.offsetTop - 32)
        this.el.classList.remove('is-loading')
        this.el.classList.add('is-hidden')
        this.inner.style.height = '99999px'
        this.inner.style.maxHeight = height + 'px'

        setTimeout(() => {
            const height = this.thanks.offsetHeight;
            this.inner.style.maxHeight = height + 'px'
            this.el.classList.add('is-step-thanks')

            setTimeout(() => {
                this.inner.style.removeProperty('height')
                this.inner.style.removeProperty('max-height')
                this.el.classList.remove('is-hidden')
            }, 300)
        }, 200)
    }

    revert() {
        const height = this.thanks.offsetHeight;

        // this.el.classList.remove('is-loading')
        this.el.classList.add('is-hidden')
        this.inner.style.height = '99999px'
        this.inner.style.maxHeight = height + 'px'

        setTimeout(() => {
            const height = this.form.offsetHeight;
            this.inner.style.maxHeight = height + 'px'
            this.el.classList.remove('is-step-thanks')

            setTimeout(() => {
                this.inner.style.removeProperty('height')
                this.inner.style.removeProperty('max-height')
                this.el.classList.remove('is-hidden')
            }, 300)
        }, 200)
    }

    error() {
        const firstField = this.el.querySelector('.has-error[data-field-name]')
        const firstFieldPosition = firstField.getBoundingClientRect();
        ScrollToSection.scroll(firstFieldPosition.top + window.scrollY - 32)
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('.js-form-service')
    if (el) {
        new FormService(el);
    }
});