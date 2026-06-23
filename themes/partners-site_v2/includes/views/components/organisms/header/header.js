class Header {
    constructor() {
        this.el = document.querySelector('.js-header');
        this.burgerEls = this.el.querySelectorAll('.js-header__hamburger');
        this.sideNavEl = this.el.querySelector('.js-header__side-nav');
        this.backdrop = this.el.querySelector('.js-header__backdrop');


        this.burgerEls.forEach(burgerEl => {
            burgerEl.addEventListener('click', () => {
                this.toggleActive();
                this.toggleSideNav();
                this.toggleHamburger();
            });
        });

        if (this.backdrop) {
            this.backdrop.addEventListener('click', () => {
                this.toggleActive();
                this.toggleSideNav();
                this.toggleHamburger();
            });
        }
    }

    toggleActive() {
        this.el.classList.toggle('is-active');
    }

    toggleSideNav() {
        this.sideNavEl.classList.toggle('is-active');
    }

    toggleHamburger() {
        this.burgerEls.forEach(burgerEl => {
            burgerEl.classList.toggle('is-active');
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const header = new Header();
});
