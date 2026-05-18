import { findGetParameter } from '../../../../assets/private/js/findGetParameter';

class TestDrive {
    constructor(el) {
        this.el = el
        this.models = this.el.querySelector('.js-test-drive__models')
        this.modelsItems = this.el.querySelectorAll('.js-test-drive__model')
        this.form = this.el.querySelector('.js-test-drive__form')

        const selectedModel = findGetParameter('s_model')
        if (selectedModel) {
            const model = this.models.querySelector('input[value="' + selectedModel + '"]').closest('.js-test-drive__model')
            this.toggle(model)
        }

        this.updateFormField('preferred_models', this.getModelsString())

        this.modelsItems.forEach((model) => {
            model.addEventListener('click', (event) => {
                event.preventDefault();
                this.toggle(model)
            })
        })
    }

    toggle(model) {
        const input = model.querySelector('input')
        let checked = model.classList.toggle('is-active');

        input.checked = !!checked

        this.updateFormField('preferred_models', this.getModelsString())
    }

    getModelsString() {
        const items = [];
        this.models.querySelectorAll('input').forEach((input) => {
            if (input.checked) {
                items.push(input.value)
            }
        })
        return items.join(', ');
    }

    updateFormField(fieldName, value) {
        const field = this.form.querySelector('input[name="' + fieldName + '"]')
        if (field) {
            field.value = value
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    let element = document.querySelector('.js-test-drive')
    if (element) {
        new TestDrive(element);
    }
});
