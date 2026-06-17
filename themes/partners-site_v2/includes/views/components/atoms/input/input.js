import parsePhoneNumber from 'libphonenumber-js'
class Input {
    constructor(el) {
       
        this.el = el
        this.input = this.el.querySelector('.js-input__field');
        this.label = this.el.querySelector('.js-input__label');
       
        this.input.addEventListener('focus', (e) => {
            this.addActive()
        })
        this.input.addEventListener('blur', (e) => {
            this.removeActive()
            this.dispatchEvent('field-blur', e.target.value)
        });
        this.input.addEventListener('change', (e) => {
            this.dispatchEvent('field-change', e.target.value)
        });
        if (this.input.name == 'phoneNumber') {
            this.input.onkeydown = function(el) {
                
                var check = parsePhoneNumber(this.value, 'PL');

		
		    if (document.querySelector(".submitter")) {

                    if (this.value.length < 3) {
                        document.querySelector(".submitter").setAttribute('disabled','disabled');
                        document.querySelector('input.phoneNumber').classList.add("has-error");
                        // return false;
                    }
                
                    if (!check.isPossible() && !check.isValid()) {
                        document.querySelector(".submitter").setAttribute('disabled','disabled');
                        document.querySelector('input.phoneNumber').classList.add("has-error");
                    } else {
                        document.querySelector(".submitter").removeAttribute('disabled');
                        document.querySelector('input.phoneNumber').classList.remove("has-error");
                    }

		    }

                
            }
        }
      


        if(this.input.value) {
            this.addActive();
        }
    }

    dispatchEvent(name, value) {
        const event = new CustomEvent(name, {
            detail: {
                value: value
            }
        });
        this.el.dispatchEvent(event)
    } 

    addActive() {
        this.el.classList.add('is-active')
    }

    removeActive() {
        if (!this.input.value) {
            this.el.classList.remove('is-active')
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.js-input')
    elements.forEach((el) => {
        new Input(el);
    })
});
