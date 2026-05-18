class acfBlocksValidation {
    constructor() {
        this.scrollable = document.querySelector('.interface-interface-skeleton__content')
        this.saveButton = this.replaceSaveButton()

        this.getFields()

        this.saveButton.addEventListener('click', () => {
            if (!this.validateForm()) {
                wp.data.dispatch('core/editor').savePost()
            }
        })
    }

    getFields(){
        this.acfFields = acf.getFields()
    }

    replaceSaveButton() {
        const defaultSaveButton = document.querySelector('body.block-editor-page .editor-post-publish-button')

        const saveButton = defaultSaveButton.cloneNode(true)
        saveButton.classList.add('is-validation-added')
        $(saveButton).insertAfter(defaultSaveButton)

        defaultSaveButton.style.display = 'none';
        return saveButton
    }

    validateForm() {
        let errors = 0;
        let firstError = null

        this.getFields()

        this.acfFields.forEach(acfField => {
            if (!this.validateField(acfField)) {
                if (!firstError) {
                    firstError = acfField

                    setTimeout(() => {
                        this.scrollable.scroll({
                            top: this.getOffsetToScrollable(firstError.$el[0], 0) - 40,
                            left: 0,
                            behavior: 'smooth'
                        });
                    }, 150)
                }
                errors++
            }

            acfField.on('change', () => {
                this.validateField(acfField)
            })
        })

        return errors
    }

    validateField(acfField) {
        if (acfField.data.required && !acfField.val()) {
            acfField.showNotice({
                text: 'To pole jest wymagane!',
                type: 'error',
                dismiss: false,
            })
            return false;
        } else {
            acfField.removeNotice()
            return true;
        }
    }

    getOffsetToScrollable(el, offset) {
        const parent = el.parentElement

        if (parent === this.scrollable) {
            return el.offsetTop
        } else {
            return el.offsetTop + this.getOffsetToScrollable(parent, offset)
        }
    }
}

window.addEventListener('load', () => {
    new acfBlocksValidation();
});
