define([
    'jquery',
    'Amasty_Xsearch/js/utils/helpers'
], function ($, helpers) {
    'use strict';

    $.widget('mage.amsearchProductItemInit', {

        /**
         * @inheritDoc
         */
        _create: function () {
            helpers.updateFormKey(this.element);
            helpers.initProductAddToCart(this.element);
            $('body').trigger('amsearch.popup.contentUpdated');

            return this;
        }
    });

    return $.mage.amsearchProductItemInit;
});
