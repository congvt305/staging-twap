define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'mage/translate',
        'jquery-ui-modules/widget',
    ],
    function ($, modal, url, $t) {
        'use strict';

        $.widget('cj.popupDelete', {
            options: {
                quote_id: null,
                item_id: {}
            },

            /**
             * Create popup.
             * @private
             */
            _create: function () {
                var self = this;
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: [{
                        text: $t('I confirm to delete'),
                        class: '',
                        click: function (data, event) {
                            if (event) {
                                event.preventDefault();
                            }
                            $.ajax({
                                url: url.build('cj_checkout/quote/deleteitems'),
                                type: 'post',
                                showLoader: true,
                                dataType: 'json',
                                context: this,
                                cache: false,
                                data: {
                                    'quote_id': self.options.quote_id,
                                    'item_id': self.options.item_id
                                },

                                /**
                                 * @param {Object} response
                                 */
                                success: function (response) {
                                    window.location.href = response['backUrl'];
                                },
                            });
                        }
                    }]
                };
                var popup = modal(options, $('#popup-modal-delete'));
                $('#popup-modal-delete').modal('openModal');
            }
        });

        return $.cj.popupDelete;
    });
