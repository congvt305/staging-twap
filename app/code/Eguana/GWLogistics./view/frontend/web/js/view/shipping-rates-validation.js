/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../model/shipping-rates-validator',
    '../model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    gwlShippingRatesValidator,
    gwlShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('eguana_gwlogistics', gwlShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('eguana_gwlogistics', gwlShippingRatesValidationRules);

    return Component;
});
