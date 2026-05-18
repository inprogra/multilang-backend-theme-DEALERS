import axios from "axios";
import qs from "qs";
import "../polyfills/eventSubmitter";
import "../polyfills/requestSubmit";

class CarSpecification {
    constructor(element) {
        this.element = element;
        this.backdrop = document.querySelector('.js-car-specification-backdrop');
        this.button = this.element.querySelector('.js-car-specification__button');
        this.spinner = this.element.querySelector('.js-car-specification__spinner');
        this.postIdInput = this.element.querySelector('.js-car-specification__input-post-id');
        this.VINField = acf.getField('field_602b972302941');
        this.CONField = acf.getField('field_602b972302942');
        this.modelField = acf.getField('field_602b96dc02486');
        this.versionField = acf.getField('field_602b9c5902ce8');
        this.form = document.querySelector('.js-car-specification-form');
        this.form.addEventListener('submit', e => {
            e.preventDefault();
            this.handleButtonClick();
        });
        this.button.addEventListener('click', () => {
            this.form.requestSubmit();
        });
    }

    handleButtonClick = () => {
        this.spinner.classList.add('is-active');
        this.button.classList.add('disabled');
        this.backdrop.classList.add('is-active');
        const validationResult = this.validateFields();
        if (!validationResult) {
            this.spinner.classList.remove('is-active');
            this.button.classList.remove('disabled');
            this.backdrop.classList.remove('is-active');
            return;
        }
        this.submit();
    }

    submit = () => {
        const data = {
            VIN: this.VINField.val(),
            CON: this.CONField.val(),
            model: this.modelField.val(),
            version: this.versionField.val(),
            postId: this.postIdInput.value,
            action: 'carSpecification'
        }
        axios({
            method: 'POST',
            headers: {'content-type': 'application/x-www-form-urlencoded'},
            data: qs.stringify(data),
            url: '/wp/wp-admin/admin-ajax.php'
        }).then(() => {
            const queryParams = qs.parse(window.location.search.substr(1, window.location.search.length - 1));
            queryParams['car-specification-data-imported'] = 1;
            window.location.href = location.protocol + '//' + location.host + location.pathname + '?' + qs.stringify(queryParams);
        }).catch(error => {
            console.log(error.response.data);
            if (error.response.status === 404) {
                this.showVinAndConNotice('Nie znaleziono samochodu o tym numerze VIN lub CON');
            }
            this.spinner.classList.remove('is-active');
            this.button.classList.remove('disabled');
            this.backdrop.classList.remove('is-active');
        });
    }

    validateFields = () => {
        let isValid = true;
        this.VINField.removeNotice();
        this.CONField.removeNotice();
        this.modelField.removeNotice();
        this.versionField.removeNotice();

        if (!this.VINField.val().trim().length && !this.CONField.val().trim().length) {
            const message = 'Podanie VIN lub CON jest wymagane do działania integracji z systemem DOL';
            this.showVinAndConNotice(message);

            isValid = false;
        }

        if (!this.modelField.val().trim().length) {
            const message = 'Podanie modelu jest wymagane do działania integracji z systemem DOL';

            this.modelField.showNotice({
                text: message,
                type: 'error',
                dismiss: true,
            });

            document.querySelector('.acf-tab-group li:first-child a').click();

            isValid = false;
        }

        if (!this.versionField.val().trim().length) {
            const message = 'Podanie wersji jest wymagane do działania integracji z systemem DOL';

            this.versionField.showNotice({
                text: message,
                type: 'error',
                dismiss: false,
            });

            document.querySelector('.acf-tab-group li:first-child a').click();

            isValid = true;
        }

        return isValid;
    }

    showVinAndConNotice = message => {
        this.VINField.removeNotice();
        this.CONField.removeNotice();

        this.VINField.showNotice({
            text: message,
            type: 'error',
            dismiss: false,
        });

        this.CONField.showNotice({
            text: message,
            type: 'error',
            dismiss: false,
        });

        document.querySelector('.acf-tab-group li:first-child a').click();
    }
}

document.addEventListener('DOMContentLoaded', () => {
   const element = document.querySelector('.js-car-specification');

   if (element) {
       new CarSpecification(element);
   }
});