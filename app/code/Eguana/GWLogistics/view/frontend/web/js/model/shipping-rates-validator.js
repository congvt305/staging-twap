/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mageUtils',
    'Eguana_GWLogistics/js/model/shipping-rates-validation-rules',
    'mage/translate'
], function ($, utils, validationRules, $t) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return {
        validationErrors: [],

        /**
         * @param {Object} address
         * @return {Boolean}
         */
        validate: function (address) {
            console.log('puhaha');
            var self = this;

            this.validationErrors = [];
            $.each(validationRules.getRules(), function (field, rule) {
                var message;

                if (rule.required && utils.isEmpty(address[field])) {
                    message = $t('Field ') + field + $t(' is required.');

                    self.validationErrors.push(message);
                }
            });

            return !this.validationErrors.length;
        }
    };
});
