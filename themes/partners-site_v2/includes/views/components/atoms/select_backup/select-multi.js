const Diacritics = require('diacritic');
import tippy, { followCursor } from 'tippy.js';
let elems = [];
class SelectMulti {

    constructor(el) {
        this.el = el;
        this.instances = document.querySelectorAll('.dinamic_tooltip');
        this.el_index = this.index;
        this.selectOptions = el.querySelectorAll('select option');
        this.input = el.querySelector('.js-select-multi__input');
        this.field = el.querySelector('.js-select-multi__field');
        this.list = el.querySelector('.js-select-multi__list');
        this.expandable = el.querySelector('.js-select-multi__expandable');
        this.itemsEl = el.querySelector('.js-select-multi__items');
        this.items = el.querySelectorAll('.js-select-multi__item');
        this.search = el.querySelector('.js-select-multi__search-input');

        this.updateSelectedText();
        let temp = [];
        this.items.forEach((item) => {
            Array.from(this.selectOptions)
                .filter(option => option.value === item.dataset.value)
                .map(option => {
                    if (option.selected) {
                        var o = 0;
                        item.classList.toggle('is-active')

                        if (document.querySelectorAll('[data-element="' + option.value + '"]').length == 0) {
                            temp.push(option.value);
                            var elem = document.createElement('li');
                            elem.setAttribute('data-element', option.value);
                            elem.appendChild(document.createTextNode(option.value));
                            var remove_elem = '<span onclick="initDelete(this)" class="remove_filter"><img src="/app/themes/stock-cars-aggregator/assets/public/close_x.svg"/></span>';
                            elem.insertAdjacentHTML('beforeend', remove_elem);
                            document.getElementById('active_filters').appendChild(elem);
                            o++;
                        }

                    }
                });

        });
        if (temp.length > 0) {
            this.el.parentNode.setAttribute('data-tooltip-content', temp.join());
            let init_tipper = this.el.parentNode.getAttribute('id');

            let index_number = this.el.parentNode.getAttribute('data-id-el').replace('instance_', '');
            var inst = new tippy(init_tipper);
            if (index_number == 2) {
                elems[index_number].setContent(temp.join(', \r\n'));
                elems[index_number].enable();
            } else {
                elems[index_number].setContent(temp.join(', '));
                elems[index_number].enable();
            }
            //this.el.parentNode.tippy.setContent(this.el.parentNode.dataset.tooltipContent);

        }
        this.input.addEventListener('click', () => {
            this.toggle();
        });

        this.search.addEventListener('keyup', e => this.handleSearchChange(e));

        document.addEventListener('click', (event) => {
            if (event.target.closest('.js-select-multi') !== this.el) {
                if (this.close()) {
                    this.dispatchEvent('field-blur');
                }
            }
        });

        this.items.forEach((item) => {

            if (item.classList.contains('is-active')) {
                // var elem = document.createElement('li');
                // elem.setAttribute('data-elem',item.getAttribute('data-value'));
                // elem.appendChild(document.createTextNode(item.getAttribute('data-value')));                        
                // document.getElementById('active_filters').appendChild(elem);

            }

            item.addEventListener('click', (event) => {
                let target = event.target.closest('.js-select-multi__item');

                target.classList.toggle('is-active');
                let temp = [];
                Array.from(this.selectOptions)
                    .filter(option => option.value === target.dataset.value)
                    .map(option => {
                        option.selected = !option.selected;
                        this.dispatchEvent('field-change');

                        if (option.selected) {
                            var elem = document.createElement('li');
                            elem.setAttribute('data-element', option.value);
                            elem.appendChild(document.createTextNode(option.value));
                            var remove_elem = '<span onclick="initDelete(this)" class="remove_filter"><img src="/app/themes/stock-cars-aggregator/assets/public/close_x.svg"/></span>';
                            elem.insertAdjacentHTML('beforeend', remove_elem);
                            if (document.getElementById('active_filters')) {
                                document.getElementById('active_filters').appendChild(elem);
                            }
                        } else {
                            document.querySelectorAll('[data-elem="' + option.value + '"]').forEach(e => e.remove());
                            document.querySelectorAll('[data-element="' + option.value + '"]').forEach(e => e.remove());

                        }
                        this.updateSelectedText();
                    });
                if (this.el.dataset.fieldMultiple != false) {
                    Array.from(this.selectOptions)
                        .filter(option => option.value !== target.dataset.value)
                        .map(option => {
                            option.selected = false;
                            document.querySelectorAll('[data-elem="' + option.value + '"]').forEach(e => e.remove());
                            document.querySelectorAll('[data-element="' + option.value + '"]').forEach(e => e.remove());
                            this.updateSelectedText();
                        });

                    $(event.target.closest('ul').children).each(function (index, elem) {
                        if (target.dataset.value !== elem.dataset.value) {
                            elem.classList.remove('is-active');
                        }
                    });
                }
                Array.from(target.parentNode.getElementsByClassName('is-active')).forEach((e) => {
                    temp.push(e.getAttribute('data-value'));
                });
            });
        });
    }

    dispatchEvent(name) {
        let event = new CustomEvent(name, {
            detail: {
                value: this.getSelectedOptions(),
            },
        });
        this.el.dispatchEvent(event);
    }

    handleSearchChange(e) {
        const searchValue = e.target.value;
        this.filterFieldsByLabel(searchValue);
    }

    filterFieldsByLabel(value) {
        const formattedValue = this.formatString(value);


        this.items.forEach(item => {
            const label = this.formatString(item.innerText.trim());

            if (label.includes(formattedValue)) {
                item.classList.remove('is-hidden');
            } else {
                item.classList.add('is-hidden');
            }
        });


    }

    formatString(text) {
        text = text.toString().toLowerCase();
        return Diacritics.clean(text.replace(/ /g, ''));
    }

    toggle() {
        if (this.el.classList.contains('is-expanded')) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.el.classList.add('is-expanded');
        this.expandable.style.maxHeight = this.itemsEl.offsetHeight + 'px';
        if (this.search) {
            setTimeout(() => {
                this.search.focus();
            }, 200);
        }
    }

    close() {
        let closed = false;
        if (this.el.classList.contains('is-expanded')) {
            closed = true;
        }

        this.el.classList.remove('is-expanded');
        this.expandable.style.removeProperty('max-height');

        setTimeout(() => {
            this.search.value = '';
            this.filterFieldsByLabel('');
            this.itemsEl.scrollTop = 0;
        }, 200);

        return closed;
    }

    updateSelectedText() {
        let labels = this.getSelectedOptionLabels();
        if (labels.length) {
            this.input.classList.add('is-active');
        } else {
            this.input.classList.remove('is-active');
        }
        this.field.innerText = labels.join(', ');

    }

    getSelectedOptions() {
        return Array.from(this.selectOptions)
            .filter(option => !!option.selected)
            .map(option => ({ value: option.value, label: option.innerText }));
    }

    getSelectedOptionValues() {
        return this.getSelectedOptions()
            .map(option => option.value);
    }

    getSelectedOptionLabels() {
        return this.getSelectedOptions()
            .map(option => option.label);
    }
}

export const selects = {};

document.addEventListener('DOMContentLoaded', () => {
    let x = 0;
    document.querySelectorAll('.dinamic_tooltip').forEach(s => {

        s.setAttribute('data-tooltip-content', ' ');
        if (s.dataset.tooltipContent !== '') {
            elems[x] = tippy(s, {
                content: s.dataset.tooltipContent,
                followCursor: true,
                offset: (x == 2 ? [0, 25] : [0, 35]),
                placement: (x == 2 ? 'right-start' : 'bottom'),
                plugins: [followCursor]
            });
            elems[x].disable();

        }
        x++;


    });
    let elements = document.querySelectorAll('.js-select-multi');
    elements.forEach((el) => {
        selects[el.dataset.fieldName] = new SelectMulti(el);
    });
    if (document.getElementById('reset__filters')) {

        document.getElementById('reset__filters').addEventListener('click', function () {

            if (window.location.href.includes('?')) {
                const url = window.location.href.split('?')[0];
                top.location.href = url;
            } else {
                var list = document.getElementById("active_filters").getElementsByTagName("li");
                Array.from(list).forEach(function (e) {
                    var event = new Event('click');
                    document.querySelector('[data-value="' + e.getAttribute('data-element') + '"]').dispatchEvent(event);
                });

            }
        })
    }

    if (document.getElementById('form_block_8')) {
        var block_8 = document.getElementById('form_block_8')
        var block_6 = document.getElementById('form_block_6');
        var block_0 = document.getElementById('form_block_0');
        var block_1 = document.getElementById('form_block_1');
        var block_2 = document.getElementById('form_block_2');
        var block_3 = document.getElementById('form_block_3');
        var block_7 = document.getElementById('form_block_7');
        var block_5 = document.getElementById('form_block_5');


        if (window.innerWidth < 992) {

            // var block_1 = document.getElementById('form_block_1');

            document.getElementsByClassName('js-stock__filters-more-inner')[0].prepend(block_1);
            document.getElementsByClassName('js-stock__filters-more-inner')[0].prepend(block_2);
            document.getElementsByClassName('js-stock__filters-more-inner')[0].prepend(block_3);
            document.getElementById('mobile_fields').appendChild(block_8);
            document.getElementById('mobile_fields').appendChild(block_6);

        } else {
            document.getElementsByClassName('filters__content')[0].prepend(block_3);
            document.getElementsByClassName('filters__content')[0].prepend(block_2);
            document.getElementsByClassName('filters__content')[0].prepend(block_1);
            document.getElementsByClassName('filters__content')[0].prepend(block_0);
            block_5.after(block_6);
            block_7.after(block_8);

        }

        window.addEventListener('resize', function (event) {
            if (window.innerWidth < 992) {


                // var block_1 = document.getElementById('form_block_1');

                document.getElementsByClassName('js-stock__filters-more-inner')[0].prepend(block_1);
                document.getElementsByClassName('js-stock__filters-more-inner')[0].prepend(block_2);
                document.getElementsByClassName('js-stock__filters-more-inner')[0].prepend(block_3);
                document.getElementById('mobile_fields').appendChild(block_8);
                document.getElementById('mobile_fields').appendChild(block_6);

            } else {
                document.getElementsByClassName('filters__content')[0].prepend(block_3);
                document.getElementsByClassName('filters__content')[0].prepend(block_2);
                document.getElementsByClassName('filters__content')[0].prepend(block_1);
                document.getElementsByClassName('filters__content')[0].prepend(block_0);
                block_5.after(block_6);
                block_7.after(block_8);

            }
        });

    }





});

