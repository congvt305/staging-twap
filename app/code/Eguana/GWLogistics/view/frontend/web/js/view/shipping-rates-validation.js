/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Eguana_GWLogistics/js/model/shipping-rates-validator',
    'Eguana_GWLogistics/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    gwlShippingRatesValidator,
    gwlShippingRatesValidationRules
) {
    'use strict';
    console.log('kk');

    defaultShippingRatesValidator.registerValidator('gwlogistics', gwlShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('gwlogistics', gwlShippingRatesValidationRules);

    return Component;
});
