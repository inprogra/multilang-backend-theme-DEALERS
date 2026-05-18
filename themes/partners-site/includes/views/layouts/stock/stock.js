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
console.log('dzialam2')
class Stock {
    constructor(element) {
        this.element = element;
        this.form = element.querySelector('.js-stock__form');
        this.formFields = [];
        this.getFormFields()

        this.initToggleMoreFilters()

        this.form.addEventListener('submit', (event) => this.submit(event));
        
        this.pagination = new StockPagination(this.element);
        let url = window.location.href.split('#');
        let query = {};
       
        if (url[1]) {
            let params = url[1].split('/');
            
            for (const  elem of params) {
                let temp = elem.split('=');
                query[temp[0]] = temp[1];
                console.log('test');
                switch(temp[0]) {                    
                    case 'model':
                        let cars = temp[1].split(',');                        
                        for (const c of cars) {
                            $('li[data-value="'+c+'"]').trigger('click');
                        //    $('select[name="model[]"]').change();
                        }
                    break;
                    case 'engine':
                        let engine = temp[1].split(',');                        
                        for (const e of engine) {                           
                            $('li[data-value="'+e.replace(/%20/g,' ')+'"]').trigger('click');
                        //    $('select[name="model[]"]').change();
                        }
                    break;
                    case 'color':
                        let colors = temp[1].split(',');                        
                        for (const c of colors) {                           
                           $('li[data-value="'+c.replace(/%20/g,' ')+'"]').trigger('click');
                        //    $('select[name="model[]"]').change();
                        }
                    break;
                    case 'max-power':
                        let maxPower = temp[1].split(',');                        
                        for (const c of maxPower) {                           
                           $('li[data-value="'+c.replace(/%20/g,' ')+'"]').trigger('click');
                        //    $('select[name="model[]"]').change();
                        }
                    break;
                    case 'version':
                        let version = temp[1].split(',');                        
                        for (const c of version) {                           
                           $('li[data-value="'+c.replace(/%20/g,' ')+'"]').trigger('click');
                        //    $('select[name="model[]"]').change();
                        }
                    break;
                    case 'production-year':
                        let productionYear = temp[1].split(',');                        
                        for (const c of productionYear) {                           
                           $('li[data-value="'+c.replace(/%20/g,' ')+'"]').trigger('click');
                        //    $('select[name="model[]"]').change();
                        }
                    break;
                    case 'showroom':
                        let showroom = temp[1].split(',');                        
                        if (showroom.length > 0) {
                            $('.js-showroom-filter input[value="'+showroom[0]+'"]').attr('checked','checked').trigger('click');
                        }
                        for (const c of showroom) {                 
                           
                        //    $('.js-showroom-filter input[value="all"]').removeAttr('checked');
                         //   $('input[value="'+c.replace(/%20/g,' ')+'"]').attr('checked','').trigger('change');
                      //    $('input[value="'+c.replace(/%20/g,' ')+'"]').parent().click();
                        //    $('select[name="model[]"]').change();
                        }
                    break;
                    case 'discount-price-max':                       
                        $('#price-range-max').val(temp[1])
                    break;
                    case 'discount-price-min':
                        $('#price-range-min').val(temp[1])
                    break;
                    case 'car_type':
                        $('input[value="'+temp[1]+'"]').trigger('click');
                    break;

                }



            }
            
            $('.content__submit button[type="submit"]').click();
        }
        
        
      
        // const esc = encodeURIComponent;
        
      
        
    }

    getFormFields() {
        const inputs = this.form.querySelectorAll('input, select')
        inputs.forEach(item => {
            if (!this.formFields.includes('s_' + item.name)) {
                this.formFields.push('s_' + item.name)
            }
        })
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

    mutateUrl(filters) {
       
        const esc = encodeURIComponent;
        let query = {};
        let validate = new URLSearchParams(window.location.search);
        for (const [key, value] of new URLSearchParams(window.location.search).entries()) {
            if (!this.formFields.includes(key)) {
                query[key] = value
            }

        }
        
        query = Object.assign(query, filters)

        let queryString = [];
        for (const [queryKey, queryValue] of Object.entries(query)) {
            
            
             if (!['action', 'resetPagination', 'page','utm_source','utm_campaign','utm_medium'].includes(queryKey)) {
                 if (Array.isArray(queryValue)) {
                    if (queryValue.length > 0) { 
                         let temp = queryKey+'=';
                         let counter = queryValue.length;
                         let i = 0;
                         queryValue.forEach(value => {
                            if (queryValue) {
                            temp += esc(value)+(i + 1 == counter ? '' : ',');
                            i++;
                            }
                        
                        //  queryString.push('s_' + esc(queryKey) + '[]=' + esc(value))
                     })
                     queryString.push(temp);
                    }
                    
                 } else {
                    if (queryValue) {
                     queryString.push(esc(queryKey) + '=' + esc(queryValue))
                    }
                 }
             }
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
        let url = window.location.href.split('#');
        let query = {};
       
       
        Array.from(formData.keys())
            .forEach(key => {
                if (key.includes('[]')) {
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
        if (url[1]) {
            let params = url[1].split('/');
            
            for (const  elem of params) {
                let temp = elem.split('=');
                query[temp[0]] = temp[1];
              
                switch(temp[0]) {                                        
                    case 'showroom':
                        let showroom = temp[1].split(',');                        
                        if (showroom.length > 0 && showroom[0].includes('s')) {
                            filters['showroom'] = showroom[0].slice(0, -1);                                                
                        }
                        for (const c of showroom) {                 
                     
                        }
                    break;                    
                }
            }    
        }
        this.mutateUrl(filters)
       // console.log(filters);
        //return false;
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
            this.pagination = new StockPagination(this.element);
            const stockCars = Array.from(document.querySelectorAll('.js-stock-car')).map(item => new StockCar(item));
            stockCarsWrapper.classList.remove('is-loading');
            ScrollToSection.scroll(stockCarsWrapper.offsetTop - 32);
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
        })
    }

    paginationChanged(pageNumber) {
        this.pagination.querySelector('.js-stock__currentPage').value = pageNumber;
        this.pagination.querySelector('.js-stock__resetPagination').value = 0;
    }
}


document.addEventListener('DOMContentLoaded', () => {
    const stockElement = document.querySelector('.js-stock');
    if (stockElement) {
        new Stock(document.querySelector('.js-stock'));
    }
    const stockCars = Array.from(document.querySelectorAll('.js-stock-car')).map(item => new StockCar(item));
});