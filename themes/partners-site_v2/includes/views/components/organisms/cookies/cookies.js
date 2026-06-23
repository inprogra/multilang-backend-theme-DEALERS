import Cookies from 'js-cookie'

class CookiesForm {
    constructor(element) {
        this.element = element;
        this.btnAcceptAll = element.querySelector('.js-cookies__accept-all')
        this.btnAcceptMandatory = element.querySelector('.js-cookies__accept-mandatory')
        this.openBtns = document.querySelectorAll('.js-cookies-open-form')

        this.btnAcceptAll.addEventListener('click', () => this.handleAcceptAll());
        this.btnAcceptMandatory.addEventListener('click', () => this.handleAcceptMandatory());

        if (Cookies.get('cookie-consent') === undefined) {
            this.open()
        }

        Array.from(this.openBtns).map(btn => {
            btn.addEventListener('click', () => {
                this.open()

                var cookies = document.cookie.split("; ");
                for (var c = 0; c < cookies.length; c++) {
                    var d = window.location.hostname.split(".");
                    while (d.length > 0) {
                        var cookieBase = encodeURIComponent(cookies[c].split(";")[0].split("=")[0]) + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; domain=' + d.join('.') + ' ;path=';
                        var p = location.pathname.split('/');
                        document.cookie = cookieBase + '/';
                        while (p.length > 0) {
                            document.cookie = cookieBase + p.join('/');
                            p.pop();
                        }
                        ;
                        d.shift();
                    }
                }

                Cookies.remove('cookie-consent', {
                    expires: 30
                });
            })
        })
    }

    open() {
        this.element.classList.add('is-visible')
    }

    close() {
        this.element.classList.remove('is-visible')
    }

    handleAcceptAll() {
        Cookies.set('cookie-consent', 'all', {expires: 30});
        this.close()

        const GTMscript = document.createElement('script');
        GTMscript.innerHTML = GTMcode;

        const head = document.getElementsByTagName('head')
        head[0].appendChild(GTMscript)
    }

    handleAcceptMandatory() {
        Cookies.set('cookie-consent', 'mandatory', {expires: 30});
        this.close()
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const element = document.querySelector('.js-cookies')
    if (element) {
        new CookiesForm(element);
    }
});