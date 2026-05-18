import axios from 'axios';
import qs from 'qs';
import {initCarGallerySliders} from "../../components/molecules/car-gallery-slider/carGallerySlider";
import {toggleSlide} from '../../../../assets/private/js/toggleSlide';
import '../../components/organisms/stock-car/stockCar';
import {StockCar} from "../../components/organisms/stock-car/stockCar";
import {ScrollToSection} from '../../../../assets/private/js/scrollToSection';
import {initTooltips} from "../../components/atoms/tooltip/tooltip";
import {initFullSizeGallery} from "../../components/molecules/full-size-gallery/fullSizeGallery";
import {initStockCarsSliders} from "../../components/organisms/stock-cars-slider/stockCarsSlider";
import $ from "jquery";
window.__PAGE_AUTOCLICK_DONE__ = false;

class Stock {
    constructor(element) {
        this.element = element;
        this.form = element.querySelector('.js-stock__form');
        this.formFields = [];
        this.getFormFields();
        this.urlFiltersApplied = false;
        this.initToggleMoreFilters();
        this.form.addEventListener('submit', (event) => this.submit(event));
        this.pagination = new StockPagination(this.element);
        this.setFiltersFromUrl();
        this.stockInstance = this;
        


        this.intersectionObserver = new StockIntersectionObserver(this.element.querySelector('.js-stock__intersection-observer'));
        let url = window.location.href.split('#');
        
    }

    getFormFields() {
        const inputs = this.form.querySelectorAll('input, select');
        inputs.forEach(item => {
            if (!this.formFields.includes('s_' + item.name)) {
                this.formFields.push('s_' + item.name);
            }
        });
    }

    initToggleMoreFilters() {
        const moreFiltersButton = this.element.querySelector('.js-stock__filters-more-button');
        const moreFiltersBottom = document.querySelector('.js-stock__filters-more');
        const moreFiltersBottomInner = moreFiltersBottom.querySelector('.js-stock__filters-more-inner');

        moreFiltersButton.addEventListener('click', () => {
            toggleSlide(moreFiltersButton, moreFiltersBottom, () => moreFiltersBottomInner.offsetHeight);
            moreFiltersButton.textContent = moreFiltersButton.classList.contains('is-active')
                ? moreFiltersButton.dataset.less : moreFiltersButton.dataset.more;
        });
    }

    setFiltersFromUrl() {
    if (this.urlFiltersApplied) return;
    const hash = window.location.href.split('#')[1];
    
    let hasParams = false;
    if (hash) {
        const params = hash.split('/');
        params.forEach(elem => {
            const [key, rawValue] = elem.split('=');
            if (!key || !rawValue) return;
    
            hasParams = true;
            const value = decodeURIComponent(rawValue);
    
            switch(key) {
                case 'page': 
                    this.pagination.setPage(Number(value));  
                    break;

                    case 'showroom':
                        const showroomItems = value.split(',');
                        document.querySelectorAll('input[name="showroom"]').forEach(i => i.checked = false);
    
                        showroomItems.forEach(v => {
                            const trimmed = v.trim();
                            const input = document.querySelector(`input[name="showroom"][value="${trimmed}"]`);
                            if (input) {
                                input.removeAttribute('checked');
                                input.checked = true;
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                setTimeout(() => input.checked = true, 300);
                            }
                        });
                        break;
    
                    case 'model':
                        value.split(',').forEach(c => $(`li[data-value="${c}"]`).trigger('click'));
                        break;
    
                    case 'engine':
                        value.split(',').forEach(e => $(`li[data-value="${e.replace(/%20/g,' ')}"]`).trigger('click'));
                        break;
    
                    case 'color':
                        value.split(',').forEach(c => $(`li[data-value="${c.replace(/%20/g,' ')}"]`).trigger('click'));
                        break;
    
                    case 'max-power':
                        value.split(',').forEach(c => $(`li[data-value="${c.replace(/%20/g,' ')}"]`).trigger('click'));
                        break;
    
                    case 'version':
                        value.split(',').forEach(c => $(`li[data-value="${c.replace(/%20/g,' ')}"]`).trigger('click'));
                        break;
    
                    case 'production-year':
                        value.split(',').forEach(c => $(`li[data-value="${c.replace(/%20/g,' ')}"]`).trigger('click'));
                        break;
    
                    case 'discount-price-max':
                        $('#price-range-max').val(value);
                        break;
    
                    case 'discount-price-min':
                        $('#price-range-min').val(value);
                        break;
    
                    case 'car_type':
                        $(`input[value="${value}"]`).trigger('click');
                        break;
                }
            });
        }
    

        if (!hasParams) {
            const defaultShowroom = document.querySelector('input[name="showroom"][value="all"]');
            if (defaultShowroom) {
                defaultShowroom.checked = true;
            }
        }
    
        this.urlFiltersApplied = true; 
    

        const submitButton = this.form.querySelector('button.content__submit-button.a-button[type="submit"]');
        if (submitButton) {
            setTimeout(() => {
                submitButton.click();
            }, 500); 
        }
    }
    
    

    mutateUrl(filters) {
        const esc = encodeURIComponent;
        let query = {};
        let validate = new URLSearchParams(window.location.search);

        for (const [key, value] of new URLSearchParams(window.location.search).entries()) {
            if (!this.formFields.includes(key)) {
                query[key] = value;
            }
        }

        query = Object.assign(query, filters);
        let queryString = [];

        for (const [queryKey, queryValue] of Object.entries(query)) {
            if (!['action', 'resetPagination', 'page','utm_source','utm_campaign','utm_medium'].includes(queryKey)) {
                if (Array.isArray(queryValue) && queryValue.length > 0) {
                    let temp = queryKey + '=';
                    queryValue.forEach((value, i) => {
                        temp += esc(value) + (i + 1 === queryValue.length ? '' : ',');
                    });
                    queryString.push(temp);
                } else if (queryValue) {
                    queryString.push(esc(queryKey) + '=' + esc(queryValue));
                }
            }
        }
        // Dodanie numeru strony do URL, jeśli jest ustawiony
        if (filters.page) {
            queryString.push('page=' + filters.page);
        }

        if (!validate.get('disableUrl')) {
            const url = window.location.origin + window.location.pathname + '#' + queryString.join('/');
            history.pushState({}, document.title, url);
        }
    }

    submit(event) {
        event.preventDefault();

        const stockCarsWrapper = document.querySelector('.js-stock__cars-wrapper');
        stockCarsWrapper.classList.add('is-loading');

        const filters = {
            'action': 'searchFilter'
        };

        const formData = new FormData(event.target);
        Array.from(formData.keys()).forEach(key => {
            if (key === 'showroom') {
                const showroomInput = document.querySelector('input[name="showroom"]:checked');
                filters[key] = showroomInput ? showroomInput.value : null;
            } else if (key.includes('[]')) {
                filters[key.slice(0, key.length - 2)] = formData.getAll(key);
            } else if (['discount-price-min', 'discount-price-max'].includes(key)) {
                filters[key] = formData.get(key).replace(/\D/g, '');
            } else {
                filters[key] = formData.get(key);
            }
        });

        if (formData.get('resetPagination') === '1') {
            filters['page'] = '1';
        }

        this.mutateUrl(filters);

        axios({
            method: 'POST',
            headers: {'content-type': 'application/x-www-form-urlencoded'},
            data: qs.stringify(filters),
            url: '/wp/wp-admin/admin-ajax.php'
        }).then(response => {
            stockCarsWrapper.innerHTML = response.data;


            initCarGallerySliders();
            initTooltips();
            initFullSizeGallery();
            initStockCarsSliders();
            window.lazyLoading.updateLazyLoading();


            // this.setFiltersFromUrl();

            this.pagination = new StockPagination(this.element);
            const stockCars = Array.from(document.querySelectorAll('.js-stock-car'))
                .map(item => new StockCar(item));

            stockCarsWrapper.classList.remove('is-loading');
            ScrollToSection.scroll(stockCarsWrapper.offsetTop - 32);

            // --- AUTOKLIK strony ---
            if (!window.__PAGE_AUTOCLICK_DONE__) { // **Sprawdzamy, czy autoklik został już wykonany**
                let hash = window.location.hash.replace('#', '');
                let parts = hash ? hash.split('/') : [];
                let pagePart = parts.find(p => p.startsWith('page='));

                if (pagePart) {
                    let pageFromHash = pagePart.split('=')[1]; // **Wyciągamy numer strony z URL**
                    const btn = document.querySelector(
                        `.js-stock__pagination-item[data-pagination-number="${pageFromHash}"]` // **Wybieramy odpowiedni przycisk paginacji**
                    );

                    if (btn) {
                        window.__PAGE_AUTOCLICK_DONE__ = true; // **Ustawiamy blokadę, żeby nie klikać ponownie**
                        btn.click(); // **Klikamy przycisk, aby załadować odpowiednią stronę**
                    }
                }
            }

        }).catch(error => {
            console.error(error);
            stockCarsWrapper.classList.remove('is-loading');
        });
    }
}

class StockPagination {
    constructor(parent) {
        this.pagination = parent.querySelector('.js-stock__pagination');
        this.paginationItems = Array.from(this.pagination.querySelectorAll('.js-stock__pagination-item'));
        this.paginationItems.forEach(item => {
            item.addEventListener('click', () => this.paginationChanged(item.dataset.paginationNumber));
        });
    }

    paginationChanged(pageNumber) {
        this.pagination.querySelector('.js-stock__currentPage').value = pageNumber;
        this.pagination.querySelector('.js-stock__resetPagination').value = 0;
        
        let hash = window.location.hash.replace('#', '');
        let parts = hash ? hash.split('/') : [];
        parts = parts.filter(p => !p.startsWith('page='));
        parts.push('page=' + pageNumber);

        let newHash = '#' + parts.join('/');
        history.replaceState(null, null, newHash); 
    }
    setPage(pageNumber) {
        const btn = this.pagination.querySelector(
            `.js-stock__pagination-item[data-pagination-number="${pageNumber}"]`
        );
        if (btn) {
            btn.click(); 
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const stockElement = document.querySelector('.js-stock');
    if (stockElement) {
        new Stock(stockElement);
    }

    const stockCars = Array.from(document.querySelectorAll('.js-stock-car'))
        .map(item => new StockCar(item));
});