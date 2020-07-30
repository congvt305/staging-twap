define([
    'uiComponent',
    'Eguana_CustomerRefund/js/model/bank-options',
], function (UiComponent, BankOptions) {
    'use strict';

    return UiComponent.extend({
        defaults: {
            value: {}
        },

        /**
         * Init observable properties
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super()
                .observe('value');

            return this;
        },

        /**
         * Set values
         *
         * @param {Object} data
         * @return void
         */
        setValues: function (data) {
            this.value(data);
        },

        /**
         * Get values
         *
         * @returns {Object}
         */
        getValues: function () {
            return this.value();
        },

        getBankOptions: function () {
            return BankOptions;
        }
    });
});
