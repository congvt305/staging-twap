/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko'
], function (
    $,
    _,
    Component,
    ko
) {
    'use strict';

    return function (Component) {
        return Component.extend({
            defaults: {
                shippingMethodItemTemplate: 'Eguana_GWLogistics/shipping-address/shipping-method-item'
            },

            /**
             * @return {exports}
             */
            initialize: function () {
                this._super();
            }
        });
    };
});
