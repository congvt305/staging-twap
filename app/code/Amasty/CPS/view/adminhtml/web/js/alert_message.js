define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, modalConfirm) {
    'use strict';

    $.widget('mage.amConfigChecker', {
        options: {
            fieldsSelector: '#amshopby_brand_general_attribute_code',
            prevValue: null
        },

        _create: function () {
            var $element = $(this.options.fieldsSelector),
                self = this;

            self.prevValue = $element.val();
            $element.on('change', function (e) {
                if (self.prevValue) {
                    modalConfirm({
                        title: $.mage.__('Warning message'),
                        content: $.mage.__('The order of products on brand pages (configured in Brand Management tab) will be lost.')
                    });
                }
            }.bind(this));
        }

    });

    return $.mage.amConfigChecker;
});
