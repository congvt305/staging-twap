/**
 * Grid data provider with disabled cache.
 */

define([
    'Magento_Ui/js/grid/provider'
], function (Provider) {
    'use strict';

    return Provider.extend({
        /**
         * Handles changes of 'params' object.
         *
         * @returns {void}
         */
        onParamsChange: function () {
            if (!this.firstLoad) {
                this.reload({ refresh: true });
            }
        }
    });
});
