class SetableShowroomFilter {
    constructor(element) {
        this.element = element;
    }

    getInputs() {
        return Array.from(this.element.querySelectorAll('input[type="radio"]'))
    }
}

class ContactSearch {
    constructor(element) {
        this.form = element;
        this.searchInput = element.querySelector('.js-contact-search__input');
        this.searchInput.addEventListener('keyup', () => this.search());
        const showroomFilterElement = element.querySelector('.js-contact-search__showroom-filter');
        if (showroomFilterElement) {
            this.showroomFilter = new SetableShowroomFilter(showroomFilterElement);
            this.showroomFilter.getInputs().forEach(input => {
                input.addEventListener('change', () => this.search());
            });
        }
    }

    changeShowroom(showroom) {
        if (this.showroomFilter) {
            this.showroomFilter.getInputs().forEach(input => {
                if(input.value === showroom) {
                    input.checked = true
                    this.search()
                }
            });
        }
    }

    search() {
        const formData = new FormData(this.form);
        const filters = {};
        Array.from(formData.keys()).forEach(key => {
            filters[key] = formData.get(key) === 'on' ? true : formData.get(key);
        });
        if (filters['showroom']) {
            this.getEmployees(filters['employee-name'], filters['showroom']);
        } else {
            this.getEmployees(filters['employee-name']);
        }
    }

    getEmployees(phrase, showroom = false) {
        let selectedShowroom;
        if (showroom) {
            selectedShowroom = document.querySelector(`.js-contact-showroom[data-showroom=${showroom}]`);
        } else {
            selectedShowroom = document.querySelector(`.js-contact-showroom`);
        }
        const contactCategories = Array.from(selectedShowroom.querySelectorAll('.js-contact-category'));
        const formattedPhrase = this.formatString(phrase);
        const activeCategories = [];
        contactCategories.forEach(category => {
            const activeEmployees = [];
            const employees = Array.from(category.querySelectorAll('.js-contact-employee'));

            employees.forEach(employee => {
                const name = this.formatString(employee.dataset.name);
                const phone = this.formatString(employee.dataset.phone);

                if (name.includes(formattedPhrase) || phone.includes(formattedPhrase)) {
                    employee.classList.add('is-active');
                    activeEmployees.push(employee);
                } else {
                    employee.classList.remove('is-active');
                }
            });
            if (activeEmployees.length > 0) {
                category.classList.add('is-active');
                activeCategories.push(category);
            } else {
                category.classList.remove('is-active');
            }

        });

        if (activeCategories.length === 0) {
            selectedShowroom.querySelector('.js-contact-showroom-not-found').classList.add('is-active');
        } else {
            selectedShowroom.querySelector('.js-contact-showroom-not-found').classList.remove('is-active');
        }

        document.querySelector('.js-contact-showroom.is-active').classList.remove('is-active');
        selectedShowroom.classList.add('is-active');
    }

    formatString(text) {
        text = text.toString().toLowerCase();
        return text.replace(/ /g, '');
    }
}

class Contact {
    constructor(el) {
        this.el = el
        this.openButton = this.el.querySelector('.js-contact__aside-open');
        this.closeButtons = this.el.querySelectorAll('.js-contact__close');
        this.seeEmployeesButtons = Array.from(this.el.querySelectorAll('.js-showroom-info__see-employees'));
        this.contactSearchEl = this.el.querySelector('.js-contact-search');
        this.ContactSearch = null;

        if (this.contactSearchEl) {
            this.ContactSearch = new ContactSearch(this.contactSearchEl);

            this.seeEmployeesButtons.forEach((seeEmployeesButton) => {
                seeEmployeesButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.ContactSearch.changeShowroom(seeEmployeesButton.dataset.showroomName)
                })
            })

        }
        this.openButton.addEventListener('click', (event) => {
            event.preventDefault();
            this.open();
        })

        this.closeButtons.forEach((closeButton) => {
            closeButton.addEventListener('click', (event) => {
                event.preventDefault();
                this.close();
            })
        })
    }

    open() {
        this.el.classList.add('is-opened');
    }

    close() {
        this.el.classList.remove('is-opened');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('.js-contact');
    if (el) {
        new Contact(el);
    }
});