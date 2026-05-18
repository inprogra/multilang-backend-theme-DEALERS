class ShowroomFilter {
    constructor(el) {
        this.el = el
        this.inputs = this.el.querySelectorAll('.js-showroom-filter__input');

        this.inputs.forEach((input) => {
            input.addEventListener('change', (e) => {
                // this.dispatchEvent('field-change', e.target.value)
            });
        })
    }

    dispatchEvent(name, value) {
        const event = new CustomEvent(name, {
            detail: {
                value: value
            }
        });
        this.el.dispatchEvent(event)
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.js-showroom-filter')
    elements.forEach((el) => {
        new ShowroomFilter(el);
    })
});