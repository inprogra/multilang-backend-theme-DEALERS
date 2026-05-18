(function($) {
    'use strict';

    $(document).ready(function() {
        if (typeof findcarAdmin === 'undefined') {
            return;
        }

        var $body = $('body');
        var dealerKey = findcarAdmin.dealerKey;
        var nonce = findcarAdmin.nonce;
        var ajaxUrl = findcarAdmin.ajaxUrl;
        var i18n = findcarAdmin.i18n;

        $body.on('click', '#findcar-test-connection', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            $btn.prop('disabled', true).text(i18n.testing);

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'findcar_test_connection',
                    nonce: nonce,
                    dealer_key: dealerKey,
                    blog_id: findcarAdmin.blogId
                },
                success: function(response) {
                    if (response.success) {
                        $btn.text(i18n.testSuccess).addClass('button-primary');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $btn.text(i18n.testError).addClass('button-secondary');
                        alert(response.data.message || i18n.testError);
                    }
                },
                error: function() {
                    $btn.text(i18n.testError).addClass('button-secondary');
                },
                complete: function() {
                    setTimeout(function() {
                        $btn.prop('disabled', false);
                    }, 2000);
                }
            });
        });

        $body.on('click', '#findcar-preview-sync', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var showroomId = $btn.data('showroom-id') || 0;
            $btn.prop('disabled', true).text(i18n.loading || 'Ładowanie...');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'findcar_preview_sync',
                    nonce: nonce,
                    showroom_id: showroomId,
                    dealer_key: dealerKey,
                    blog_id: findcarAdmin.blogId
                },
                success: function(response) {
                    if (response.success && window.FindcarSyncModal) {
                        window.FindcarSyncModal.showPreview(response.data, {
                            onSync: function() {
                                performSync(showroomId);
                            }
                        });
                    } else if (response.data && response.data.message) {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(i18n.syncError || 'Błąd pobierania podglądu');
                },
                complete: function() {
                    $btn.prop('disabled', false).text(i18n.preview || 'Podgląd');
                }
            });
        });

        $body.on('click', '#findcar-list-preview-btn', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('Ładowanie...');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'findcar_preview_sync',
                    nonce: nonce,
                    showroom_id: 0,
                    dealer_key: dealerKey,
                    blog_id: findcarAdmin.blogId
                },
                success: function(response) {
                    if (response.success && window.FindcarSyncModal) {
                        window.FindcarSyncModal.showPreview(response.data, {
                            onSync: function() {
                                window.location.href = 'edit.php?post_type=stock-car&findcar_quick_sync=1';
                            }
                        });
                    } else if (response.data && response.data.message) {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('Błąd pobierania podglądu');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Podgląd FindCar');
                }
            });
        });

        if (window.FindcarSyncModal && window.findcarPreviewData) {
            window.FindcarSyncModal.showPreview(window.findcarPreviewData, {
                onSync: function() {
                    window.location.href = 'edit.php?post_type=stock-car&findcar_quick_sync=1';
                }
            });
        }

        function performSync(showroomId) {
            var $btn = $('#findcar-sync-all');
            if (window.FindcarSyncModal) {
                window.FindcarSyncModal.showLoading('Trwa synchronizacja samochodów z FindCar...');
            }
            
            $btn.prop('disabled', true).text(i18n.syncing || 'Synchronizacja...');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'findcar_sync_all',
                    nonce: nonce,
                    showroom_id: showroomId || 0,
                    dealer_key: dealerKey,
                    blog_id: findcarAdmin.blogId
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $btn.text(i18n.syncSuccess + ' (' + data.synced + '/' + data.total + ')').addClass('button-primary');
                        
                        if (window.FindcarSyncModal) {
                            window.FindcarSyncModal.showResults(data);
                        } else if (data.errors > 0) {
                            alert('Synchronizacja zakończona. Błędów: ' + data.errors);
                        }
                        
                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    } else {
                        $btn.text(i18n.syncError).addClass('button-secondary');
                        alert(response.data.message || i18n.syncError);
                    }
                },
                error: function() {
                    $btn.text(i18n.syncError).addClass('button-secondary');
                    if (window.FindcarSyncModal) {
                        window.FindcarSyncModal.showResults({
                            synced: 0,
                            errors: 1,
                            error_details: [{ car_id: 0, error: 'Błąd połączenia z serwerem' }],
                            total: 0
                        });
                    } else {
                        alert(i18n.syncError || 'Błąd synchronizacji');
                    }
                },
                complete: function() {
                    setTimeout(function() {
                        $btn.prop('disabled', false);
                    }, 3000);
                }
            });
        }

        $body.on('click', '#findcar-sync-all', function(e) {
            e.preventDefault();
            var showroomId = $(this).data('showroom-id') || 0;
            performSync(showroomId);
        });

        var $enableField = $('[data-key*="findcar_enabled"]');
        if ($enableField.length) {
            var $syncAllSection = $('#findcar-sync-all').closest('.acf-message');
            
            if ($syncAllSection.length) {
                $syncAllSection.hide();
            }

            var checkEnabled = function() {
                var isEnabled = $('[data-key*="findcar_enabled"] input[type="checkbox"]').prop('checked');
                if (isEnabled && $syncAllSection.length) {
                    $syncAllSection.show();
                }
            };

            checkEnabled();
            
            $('[data-key*="findcar_enabled"]').on('change', 'input[type="checkbox"]', checkEnabled);
        }
    });

})(jQuery);
