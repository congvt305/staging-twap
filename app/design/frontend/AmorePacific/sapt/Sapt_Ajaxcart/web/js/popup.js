define([
    'jquery',
    ], function ($) {
        'use strict';

        $.widget('sapt.popup', {

            _create: function () {
                var self = this;
                var options = this.options;

                $('.content-ajaxcart .btn-continue').on('click', function() {
                    self._closePopup();
                });
            },

            _closePopup: function(){
                $('.modals-ajaxcart').find('.action-close').trigger('click');
            }
        });

        return $.sapt.popup;
    });
