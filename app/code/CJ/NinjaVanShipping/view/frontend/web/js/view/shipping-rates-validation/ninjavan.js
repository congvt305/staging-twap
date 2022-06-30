/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/ninjavan',
    '../../model/shipping-rates-validation-rules/ninjavan'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    ninjavanShippingRatesValidator,
    ninjavanShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('ninjavan', ninjavanShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('ninjavan', ninjavanShippingRatesValidationRules);

    return Component;
});
