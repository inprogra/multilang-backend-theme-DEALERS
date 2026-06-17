import { toggleSlide } from '../../../../../assets/private/js/toggleSlide';

export class StockCar {
    constructor(element) {
        this.element = element;
        this.initToggleCarTechnicalDetails();
    }

    initToggleCarTechnicalDetails() {
        const stockCarDetailsTop = this.element.querySelector('.js-stock-car__details-top')
        if(stockCarDetailsTop) {
            stockCarDetailsTop.addEventListener('click', () => {
                toggleSlide(this.element.querySelector('.js-stock-car__details'),
                    this.element.querySelector('.js-stock-car__details-bottom'),
                    () => this.element.querySelector('.js-stock-car__details-bottom-inner').offsetHeight);
            });
        }
      
       $('.show_form').click(function(e) {
           e.preventDefault();
           var el = $(this).parent().attr('data-index');
           var elem = $(this).parent().attr('data-loop');
           $('.step_'+el).hide();
           $('.step_'+el+'_a').show();
           console.log('aaa');
       })
       $('.show_details').click(function(e) {
        e.preventDefault();
        $('.step__a').hide();
        $('.step_').show();
       })
       $('.form_tabs li').on('click',function() {
        let name = $(this).attr("data-id")
           if (!$(this).hasClass('active_tab')) {
           $(this).siblings().removeClass('active_tab');
           $(this).addClass('active_tab');
           var active_el = $(this).index();
           $(this).parent().siblings('.tabs_container').find('> div').hide();
           $(this).parent().siblings('.tabs_container').find('> div#'+name).show();
        }
       });
       $('.slide__combo_inner > div').on('click', function() {
            if (!$(this).hasClass('combo__selected')) {
                $(this).siblings('.combo__selected').removeClass('combo__selected');
                $(this).addClass('combo__selected');
            }
       });
       $('.activate_payment').click(function() {
        $('.step__a').show();
        $('.step_').hide();
       });
       if(window.location.href.indexOf('finansowanie') > -1){
        $('.step__a').show();
        $('.step_').hide();        
       } else {
        $('.step__a').hide();
        $('.step_').show();
       }

    }
}