/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Eguana_Dhl/js/model/shipping-rates-validator',
    'Eguana_Dhl/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    eguanaDhlShippingRatesValidator,
    eguanaDhlShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('eguanaDhl', eguanaDhlShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('eguanaDhl', eguanaDhlShippingRatesValidationRules);

    return Component;
});
