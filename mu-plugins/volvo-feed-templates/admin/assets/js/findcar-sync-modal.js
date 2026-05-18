(function($) {
    'use strict';

    var FindcarSyncModal = {
        modalTemplate: null,
        modalId: 'findcar-sync-modal',

        init: function() {
            this.createModalTemplate();
            this.bindEvents();
        },

        createModalTemplate: function() {
            var template = `
                <div id="${this.modalId}" class="findcar-modal" style="display:none;">
                    <div class="findcar-modal-backdrop"></div>
                    <div class="findcar-modal-content">
                        <div class="findcar-modal-header">
                            <h2 class="findcar-modal-title"></h2>
                            <button type="button" class="findcar-modal-close">&times;</button>
                        </div>
                        <div class="findcar-modal-body">
                            <div class="findcar-modal-stats"></div>
                            <div class="findcar-modal-list"></div>
                        </div>
                        <div class="findcar-modal-footer">
                            <button type="button" class="button findcar-modal-secondary">Zamknij</button>
                            <button type="button" class="button button-primary findcar-modal-primary"></button>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(template);
            this.$modal = $('#' + this.modalId);
            this.$title = this.$modal.find('.findcar-modal-title');
            this.$stats = this.$modal.find('.findcar-modal-stats');
            this.$list = this.$modal.find('.findcar-modal-list');
            this.$footer = this.$modal.find('.findcar-modal-footer');
        },

        bindEvents: function() {
            var self = this;

            this.$modal.on('click', '.findcar-modal-close, .findcar-modal-backdrop, .findcar-modal-secondary', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.$modal.is(':visible')) {
                    self.closeModal();
                }
            });
        },

        openModal: function() {
            this.$modal.show();
            this.$modal.addClass('active');
            $('body').addClass('findcar-modal-open');
        },

        closeModal: function() {
            this.$modal.hide();
            this.$modal.removeClass('active');
            $('body').removeClass('findcar-modal-open');
        },

        showPreview: function(data, options) {
            this.$title.text('Podgląd synchronizacji FindCar');

            var html = '<div class="findcar-stats-grid">';
            html += '<div class="findcar-stat findcar-stat-ready">';
            html += '<span class="findcar-stat-icon dashicons dashicons-yes-alt"></span>';
            html += '<span class="findcar-stat-number">' + data.ready_to_sync + '</span>';
            html += '<span class="findcar-stat-label">Gotowe do synchronizacji</span>';
            html += '</div>';
            html += '<div class="findcar-stat findcar-stat-missing">';
            html += '<span class="findcar-stat-icon dashicons dashicons-warning"></span>';
            html += '<span class="findcar-stat-number">' + data.missing_fields + '</span>';
            html += '<span class="findcar-stat-label">Brakuje danych</span>';
            html += '</div>';
            html += '<div class="findcar-stat findcar-stat-total">';
            html += '<span class="findcar-stat-icon dashicons dashicons-car"></span>';
            html += '<span class="findcar-stat-number">' + data.total_enabled + '</span>';
            html += '<span class="findcar-stat-label">Wszystkie włączone</span>';
            html += '</div>';
            html += '</div>';

            this.$stats.html(html);

            if (data.cars_missing_info && data.cars_missing_info.length > 0) {
                var listHtml = '<h4>Samochody wymagające uzupełnienia danych:</h4>';
                listHtml += '<ul class="findcar-cars-list">';
                $.each(data.cars_missing_info, function(i, car) {
                    listHtml += '<li class="findcar-car-item">';
                    listHtml += '<strong>' + car.car_title + '</strong>';
                    listHtml += '<span class="findcar-car-id">#' + car.car_id + '</span>';
                    listHtml += '<ul class="findcar-missing-fields">';
                    $.each(car.missing, function(j, field) {
                        listHtml += '<li>' + field + '</li>';
                    });
                    listHtml += '</ul></li>';
                });
                listHtml += '</ul>';
                this.$list.html(listHtml);
            } else {
                this.$list.html('<p class="findcar-all-ready">Wszystkie samochody są gotowe do synchronizacji!</p>');
            }

            var self = this;
            this.$footer.find('.findcar-modal-secondary').text('Zamknij').show();
            this.$footer.find('.findcar-modal-primary').text('Synchronizuj wszystkie').off('click').on('click', function() {
                self.closeModal();
                if (options && options.onSync) {
                    options.onSync();
                }
            });

            this.openModal();
        },

        showResults: function(data) {
            this.$title.text('Wyniki synchronizacji FindCar');

            var syncedClass = data.errors > 0 ? 'findcar-stat-warning' : 'findcar-stat-success';
            var html = '<div class="findcar-stats-grid">';
            html += '<div class="findcar-stat ' + syncedClass + '">';
            html += '<span class="findcar-stat-icon dashicons dashicons-yes-alt"></span>';
            html += '<span class="findcar-stat-number">' + data.synced + '</span>';
            html += '<span class="findcar-stat-label">Zsynchronizowano</span>';
            html += '</div>';
            html += '<div class="findcar-stat ' + (data.errors > 0 ? 'findcar-stat-error' : 'findcar-stat-muted') + '">';
            html += '<span class="findcar-stat-icon dashicons dashicons-no-alt"></span>';
            html += '<span class="findcar-stat-number">' + data.errors + '</span>';
            html += '<span class="findcar-stat-label">Błędów</span>';
            html += '</div>';
            html += '<div class="findcar-stat findcar-stat-total">';
            html += '<span class="findcar-stat-icon dashicons dashicons-car"></span>';
            html += '<span class="findcar-stat-number">' + data.total + '</span>';
            html += '<span class="findcar-stat-label">Wszystkie</span>';
            html += '</div>';
            html += '</div>';

            this.$stats.html(html);

            if (data.error_details && data.error_details.length > 0) {
                var listHtml = '<h4>Szczegóły błędów:</h4>';
                listHtml += '<ul class="findcar-cars-list findcar-errors-list">';
                $.each(data.error_details, function(i, error) {
                    listHtml += '<li class="findcar-car-item findcar-error-item">';
                    listHtml += '<span class="findcar-car-id">#' + error.car_id + '</span>';
                    listHtml += '<span class="findcar-error-msg">' + error.error + '</span>';
                    listHtml += '</li>';
                });
                listHtml += '</ul>';
                this.$list.html(listHtml);
            } else {
                this.$list.html('<p class="findcar-all-success">Wszystkie samochody zostały pomyślnie zsynchronizowane!</p>');
            }

            this.$footer.find('.findcar-modal-secondary').text('Zamknij').show();
            this.$footer.find('.findcar-modal-primary').text('OK').off('click').on('click', function() {
                self.closeModal();
            });

            this.openModal();
        },

        showLoading: function(message) {
            this.$title.text('Trwa synchronizacja...');
            this.$stats.html('<div class="findcar-loading"><span class="findcar-spinner"></span><p>' + (message || 'Proszę czekać...') + '</p></div>');
            this.$list.html('');
            this.$footer.find('.findcar-modal-secondary').hide();
            this.$footer.find('.findcar-modal-primary').hide();
            this.openModal();
        }
    };

    $(document).ready(function() {
        FindcarSyncModal.init();
        window.FindcarSyncModal = FindcarSyncModal;
    });

})(jQuery);
