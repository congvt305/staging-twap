define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/vlogic',
    '../../model/shipping-rates-validation-rules/vlogic'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    vlogicShippingRatesValidator,
    vlogicShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('vlogic', vlogicShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('vlogic', vlogicShippingRatesValidationRules);

    return Component;
});
