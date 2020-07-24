define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Eguana_GWLogistics/js/view/checkout/shipping/cvs-location-form',
    'mage/translate',
], function ($, quote, cvsForm, $t) {
    'use strict'

    return function (Shipping) {
        return Shipping.extend({
            validateShippingInformation: function (updatedShipping) {
                var self = this,
                    logisticTypeSelector= 'input#LogisticsSubType.input-text';
                if (quote.shippingMethod() === null) {
                    return self._super(updatedShipping);
                }
                if ((quote.shippingMethod().method_code + '_' + quote.shippingMethod().carrier_code === 'CVS_gwlogistics')
                    && ($(logisticTypeSelector)[0].value === '')
                )
                {
                    cvsForm().errorMessage('Please select a cvs store');
                    return false;
                }
                return self._super(updatedShipping);
            }
        });
    };
});


