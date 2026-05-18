class SideForm {
    constructor(el) {
        this.el = el
        this.openButton = this.el.querySelector('.js-side-form__open');
        this.externalOpenTriggers = document.querySelectorAll('.js-open-side-form');
        this.extraFields = document.querySelectorAll('.js-open-extra-fields');
        this.leasingEndpoint = document.querySelectorAll('.js-leasing-endpoint');
        this.closeButtons = this.el.querySelectorAll('.js-side-form__close');
        this.backdrop = this.el.querySelector('.js-side-form__backdrop');
        this.sendTime = 0

        this.openButton.addEventListener('click', (event) => {
            event.preventDefault();
            this.open();
        })

        this.externalOpenTriggers.forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                this.open();
            })
        })

        this.leasingEndpoint.forEach((trigger) => {
            trigger.addEventListener('click', (e) => {
                // let xhr = new XMLHttpRequest();
                // const income = document.getElementById('income').value;
                // const hireMonth = document.getElementById('hireMonth');
                // const nPar1 = hireMonth.querySelector('.combo__selected').innerHTML;
                // const hireFee = (document.getElementById('hireFee') ? document.getElementById('hireFee') : null);
                // const nPar2 = hireFee.querySelector('.combo__selected').innerHTML;
                // const hireMileage = document.getElementById('hireMileage');
                // const nPar3 = hireMileage.querySelector('.combo__selected').innerHTML;
                // const leasingMonth = document.getElementById('leasingMonth');
                // const lPar1 = leasingMonth.querySelector('.combo__selected').innerHTML;
                // const leasingFee = document.getElementById('leasingFee');
                // const lPar2 = leasingFee.querySelector('.combo__selected').innerHTML;
                // const leasingPurchase = document.getElementById('leasingPurchase');
                // const lPar3 = leasingPurchase.querySelector('.combo__selected').innerHTML;
                // const formTabs = document.querySelector('.form_tabs');
                
                // if (formTabs.querySelector('li.active_tab').innerHTML.indexOf("Leasing") !== -1) {
                //     var data = "leasing_par1=" + lPar1 + "&leasing_par2=" + lPar2 + "&leasing_par3=" + lPar3+"&type=leasing&price="+document.getElementById('final_price_leasing').dataset.price+'&income='+income+'&eurocode='+document.getElementById('eurocode').value+'&leasingId='+document.getElementById('leasing_id').value;
                //   }
          
                //   if (formTabs.querySelector('li.active_tab').innerHTML.indexOf("Najem") !== -1) {
                //     var data = "najem_par1=" + nPar1 + "&najem_par2=" + nPar2 + "&najem_par3=" + nPar3+'&income='+document.getElementById('income_najem').value+'&type=najem&price='+document.getElementById('final_price_leasing').dataset.price+'&eurocode='+document.getElementById('eurocode').value+'&najemId='+document.getElementById('najem_id').value;
                //   } // var data = "najem_par1="+nPar1+"&najem_par2="+nPar2+"&najem_par3="+nPar3+"&leasing_par1="+lPar1+"&leasing_par2="+lPar2+"&leasing_par3="+lPar3;
          
                // // var data = "najem_par1="+nPar1+"&najem_par2="+nPar2+"&najem_par3="+nPar3+"&leasing_par1="+lPar1+"&leasing_par2="+lPar2+"&leasing_par3="+lPar3;
                // xhr.open("POST", "/api/getCalculation");
                // xhr.setRequestHeader("Accept", "application/json");
                // xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                
                // xhr.onreadystatechange = () => {
                //     const leasingFinalPrice = document.getElementById('leasingFinalPrice');
                //     const hireFinalPrice = document.getElementById('hireFinalPrice');
                //     var response = JSON.parse(xhr.response);
                    
                //     leasingFinalPrice.innerHTML = response[0].car_price + ' zł';
                //     hireFinalPrice.innerHTML = response[1].car_price + ' zł';
                // }
                // xhr.send(data);
            })
        })

        this.extraFields.forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                // const hireMonth = document.getElementById('hireMonth');
                // const hireMonthField = document.querySelector('.js-form__hire-month-field');
                // const hireMonthActiveValue = hireMonth.querySelector('.combo__selected').innerHTML;
                // const hireFee = (document.getElementById('hireFee') ? document.getElementById('hireFee').length : null);
                // const hireFeeField = document.querySelector('.js-form__hire-fee-field');
                // const hireFeeActiveValue = hireFee.querySelector('.combo__selected').innerHTML;
                // const hireMileage = document.getElementById('hireMileage');
                // const hireMileageField = document.querySelector('.js-form__hire-mileage-field');
                // const hireMileageActiveValue = hireMileage.querySelector('.combo__selected').innerHTML;
                // const leasingMonth = document.getElementById('leasingMonth');
                // const leasingMonthField = document.querySelector('.js-form__leasing-month-field');
                // const leasingMonthActiveValue = leasingMonth.querySelector('.combo__selected').innerHTML;
                // const leasingFee = document.getElementById('leasingFee');
                // const leasingFeeField = document.querySelector('.js-form__leasing-fee-field');
                // const leasingFeeActiveValue = leasingFee.querySelector('.combo__selected').innerHTML;
                // const leasingPurchase = document.getElementById('leasingPurchase');
                // const leasingPurchaseField = document.querySelector('.js-form__leasing-purchase-field');
                // const leasingPurchaseActiveValue = leasingPurchase.querySelector('.combo__selected').innerHTML;
                // const hireMonthExtraFieldsContainer = document.querySelector('.js-form__hire-month');
                // const hireFeeExtraFieldsContainer = document.querySelector('.js-form__hire-fee');
                // const hireMileageExtraFieldsContainer = document.querySelector('.js-form__hire-mileage');
                // c Volvo robi to poprzez instalację wtyczki WordPress, którą wysłałam Panu wcześniej w załączniku.onst hireMonthParagraph = document.createElement('p');
                // const hireFeeParagraph = document.createElement('p');
                // const hireMileageParagraph = document.createElement('p');
                // const leasingMonthParagraph = document.createElement('p');
                // const leasingFeeParagraph = document.createElement('p');
                // const leasingPurchaseParagraph = document.createElement('p');
                // const hireAssistance = document.getElementById('hireAssistance');
                // const hireAssistanceField = document.querySelector('.js-form__hire-assistance-field');
                // const leasingAssistance = document.getElementById('leasingAssistance');
                // const leasingAssistanceField = document.querySelector('.js-form__leasing-assistance-field');
                // const hireRate = document.getElementById('hireFinalPrice');
                // const hireRateField = document.querySelector('.js-form__hire-rate-field');
                // const leasingRate = document.getElementById('leasingFinalPrice');
                // const leasingRateField = document.querySelector('.js-form__leasing-rate-field');
                // const formTabs = document.querySelector('.form_tabs');
                
                // if(formTabs.querySelector('li.active_tab').innerHTML.indexOf("Leasing") !== -1) {
                //     if(leasingMonthExtraFieldsContainer.querySelector('p')){
                //         leasingMonthExtraFieldsContainer.querySelector('p').innerHTML = 'Liczba miesięcy najmu: ' + leasingMonthActiveValue
                //     } else {
                //         leasingMonthParagraph.innerHTML = 'Liczba miesięcy najmu: ' + leasingMonthActiveValue;
                //         leasingMonthExtraFieldsContainer.appendChild(leasingMonthParagraph);
                //     }
                //     if(leasingFeeExtraFieldsContainer.querySelector('p')){
                //   Volvo robi to poprzez instalację wtyczki WordPress, którą wysłałam Panu wcześniej w załączniku.       leasingFeeExtraFieldsContainer.querySelector('p').innerHTML = 'Oplata wstepna: ' + leasingFeeActiveValue
                //     } else {
                //         leasingFeeParagraph.innerHTML = 'Oplata wstepna: ' + leasingFeeActiveValue;
                //         leasingFeeExtraFieldsContainer.appendChild(leasingFeeParagraph);
                //     }
                //     if(leasingPurchaseExtraFieldsContainer.querySelector('p')){
                //         leasingPurchaseExtraFieldsContainer.querySelector('p').innerHTML = 'Wykup: ' + leasingPurchaseActiveValue
                //     } else {
                //         leasingPurchaseParagraph.innerHTML = 'Wykup: ' + leasingPurchaseActiveValue;
                //         leasingPurchaseExtraFieldsContainer.appendChild(leasingPurchaseParagraph);
                //     }
                //     leasingMonthField.value = leasingMonthActiveValue;
                //     leasingFeeField.value = leasingFeeActiveValue;
                //     leasingPurchaseField.value = leasingPurchaseActiveValue;
                //     if(leasingAssistance.checked) {
                //         leasingAssistanceField.value = 'Tak';
                //     } else {
                //         leasingAssistanceField.value = 'Nie';
                //     }
                //     if(leasingRate){
                //         leasingRateField.value = leasingRate.innerHTML
                //     }
                // } else {
                //     leasingMonthField.value = '';
                //     leasingFeeField.value = '';
                //     leasingPurchaseField.value = '';
                //     leasingAssistanceField.value = '';
                //     leasingRateField.value = '';
                // }
                // if(formTabs.querySelector('li.active_tab').innerHTML.indexOf("Najem") !== -1) {
                //     if(hireMonthExtraFieldsContainer.querySelector('p')){
                //         hireMonthExtraFieldsContainer.querySelector('p').innerHTML = 'Liczba miesięcy najmu: ' + hireMonthActiveValue
                //     } else {
                //         hireMonthParagraph.innerHTML = 'Liczba miesięcy najmu: ' + hireMonthActiveValue;
                //         hireMonthExtraFieldsContainer.appendChild(hireMonthParagraph);
                //     }
                //     if(hireFeeExtraFieldsContainer.querySelector('p')){
                //         hireFeeExtraFieldsContainer.querySelector('p').innerHTML = 'Oplata wstepna: ' + hireFeeActiveValue
                //     } else {
                //         hireFeeParagraph.innerHTML = 'Oplata wstepna: ' + hireFeeActiveValue;
                //         hireFeeExtraFieldsContainer.appendChild(hireFeeParagraph);
                //     }
                //     if(hireMileageExtraFieldsContainer.querySelector('p')){
                //         hireMileageExtraFieldsContainer.querySelector('p').innerHTML = 'Przebieg roczny: ' + hireMileageActiveValue + ' 000 km'
                //     } else {
                //         hireMileageParagraph.innerHTML = 'Przebieg roczny: ' + hireMileageActiveValue + ' 000 km';
                //         hireMileageExtraFieldsContainer.appendChild(hireMileageParagraph);
                //   Volvo robi to poprzez instalację wtyczki WordPress, którą wysłałam Panu wcześniej w załączniku.   hireMileageField.value = hireMileageActiveValue + ' 000 km';
                //     if(hireAssistance.checked) {
                //         hireAssistanceField.value = 'Tak';
                //     } else {
                //         hireAssistanceField.value = 'Nie';
                //     }
                //     if(hireRate){
                //         hireRateField.value = hireRate.innerHTML
                //     }
                // } else {
                //     hireMonthField.value = '';
                //     hireFeeField.value = '';
                //     hireMileageField.value = '';
                //     hireAssistanceField.value = '';
                //     hireRateField.value = '';
                // }
                event.preventDefault();
                this.open();
            })
        })

        this.closeButtons.forEach((closeButton) => {
            closeButton.addEventListener('click', (event) => {
                const hireMonthExtraFieldsContainer = document.querySelector('.js-form__hire-month');
                const hireMonthExtraParagraphs = hireMonthExtraFieldsContainer.querySelector('p');
                const hireFeeExtraFieldsContainer = document.querySelector('.js-form__hire-fee');
                const hireFeeExtraParagraphs = hireFeeExtraFieldsContainer.querySelector('p');
                const hireMileageExtraFieldsContainer = document.querySelector('.js-form__hire-mileage');
                const hireMileageExtraParagraphs = hireMileageExtraFieldsContainer.querySelector('p');
                const leasingMonthExtraFieldsContainer = document.querySelector('.js-form__leasing-month');
                const leasingMonthExtraParagraphs = leasingMonthExtraFieldsContainer.querySelector('p');
                const leasingFeeExtraFieldsContainer = document.querySelector('.js-form__leasing-fee');
                const leasingFeeExtraParagraphs = leasingFeeExtraFieldsContainer.querySelector('p');
                const leasingPurchaseExtraFieldsContainer = document.querySelector('.js-form__leasing-purchase');
                const leasingPurchaseExtraParagraphs = leasingPurchaseExtraFieldsContainer.querySelector('p');
                event.preventDefault();
                this.close();
                if (hireMonthExtraParagraphs) {
                    hireMonthExtraParagraphs.remove();
                }
                if (hireFeeExtraParagraphs) {
                    hireFeeExtraParagraphs.remove();
                }
                if (hireMileageExtraParagraphs) {
                    hireMileageExtraParagraphs.remove();
                } 
                if (leasingFeeExtraParagraphs) {
                    leasingFeeExtraParagraphs.remove();
                }
                if (leasingPurchaseExtraParagraphs) {
                    leasingPurchaseExtraParagraphs.remove();
                }
            })
        })
        if (this.backdrop) {
        this.backdrop.addEventListener('click', (event) => {
                const hireMonthExtraFieldsContainer = document.querySelector('.js-form__hire-month');
                const hireMonthExtraParagraphs = hireMonthExtraFieldsContainer.querySelector('p');
                const hireFeeExtraFieldsContainer = document.querySelector('.js-form__hire-fee');
                const hireFeeExtraParagraphs = hireFeeExtraFieldsContainer.querySelector('p');
                const hireMileageExtraFieldsContainer = document.querySelector('.js-form__hire-mileage');
                const hireMileageExtraParagraphs = hireMileageExtraFieldsContainer.querySelector('p');
                const leasingMonthExtraFieldsContainer = document.querySelector('.js-form__leasing-month');
                const leasingMonthExtraParagraphs = leasingMonthExtraFieldsContainer.querySelector('p');
                const leasingFeeExtraFieldsContainer = document.querySelector('.js-form__leasing-fee');
                const leasingFeeExtraParagraphs = leasingFeeExtraFieldsContainer.querySelector('p');
                const leasingPurchaseExtraFieldsContainer = document.querySelector('.js-form__leasing-purchase');
                const leasingPurchaseExtraParagraphs = leasingPurchaseExtraFieldsContainer.querySelector('p');
                event.preventDefault();
            this.close();
                if (hireMonthExtraParagraphs) {
                    hireMonthExtraParagraphs.remove();
                }
                if (hireFeeExtraParagraphs) {
                    hireFeeExtraParagraphs.remove();
                }
                if (hireMileageExtraParagraphs) {
                    hireMileageExtraParagraphs.remove();
                }
                if (leasingMonthExtraParagraphs) {
                    leasingMonthExtraParagraphs.remove();
                }
                if (leasingFeeExtraParagraphs) {
                    leasingFeeExtraParagraphs.remove();
                }
                if (leasingPurchaseExtraParagraphs) {
                    leasingPurchaseExtraParagraphs.remove();
                }
        })
    }
    }

    open() {
        this.el.classList.add('is-opened');
    }

    close() {
        this.el.classList.remove('is-opened');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('.js-side-form');
    if (el) {
        new SideForm(el);
    }

    const migrate = document.getElementById('migrate');
    const mobileBox = document.getElementById('mobileFormBox');
    const migrateTarget = document.getElementById('migrate_target');
    const wrapperMain = document.querySelector('.l-wrapper__main'); 

    function updateMigrator() {
        const resizeWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;

        // if (document.getElementById('sideform_status').value == 'on') {
        //     resizeWidth = 4500;
        // }

        if (resizeWidth < 992) {
            if (migrate.parentNode !== wrapperMain) {
                wrapperMain.insertBefore(migrate, wrapperMain.firstChild);
                migrate.classList.remove('is-opened');
            }
        } else {
            if (migrate.parentNode !== migrateTarget) {
                migrateTarget.appendChild(migrate);
                migrate.classList.add('is-opened');
            }

            const resizeHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
            if (resizeHeight < 1140) {
                migrate.classList.remove('scrollme'); 
                document.getElementsByClassName('form__footer')[0].classList.add('shadow');
                document.getElementsByClassName('form__content')[0].classList.add('showscroller');
            } else {
                migrate.classList.add('scrollme'); 
                document.getElementsByClassName('form__footer')[0].classList.remove('shadow');
                document.getElementsByClassName('form__content')[0].classList.remove('showscroller');
            }
        }
    }
    const sideformStatusEl = document.getElementById('sideform_status');

        if (sideformStatusEl && sideformStatusEl.value == 'on') {
            const waitForWrapper = setInterval(() => {
                if (wrapperMain) {
                    updateMigrator();
                    window.addEventListener('resize', updateMigrator, true);
                    clearInterval(waitForWrapper);
                }
            }, 100);
        }


    if (document.querySelector('.form_tabs') && document.querySelector('.form_tabs').length > 0) {
        let activate_tabs = document.querySelector('.form_tabs');
        activate_tabs.querySelector('li.active_tab').click(); 
    }
   
    
});






