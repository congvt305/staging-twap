/**
 * XML Sitemap helpers
 */

define([
    'underscore'
], function (_) {
    'use strict';

    return {
        /**
         * Update an entityCodes in a RecordData
         *
         * @param {Array} recordData - uiForm record data of custom items
         * @param {Array} rows - dynamic rows uiClass elements
         * @returns {void}
         */
        updateRecordData: function (recordData, rows) {
            var code = 'entity_code';

            _.each(recordData, function (item, index) {
                item[code] = this.getUiClassByIndex(rows[index].elems(), code).value();
            }.bind(this));
        },

        /**
         * @param {Array} target - array of uiClass elements
         * @param {String} index - an index property of uiClass
         * @returns {Object} - uiClass
         */
        getUiClassByIndex: function (target, index) {
            return target.find(function (object) {
                return object.index === index;
            });
        },

        /**
         * Make product fields data compatible with backend, for load and save
         * @param {Object} data - uiForm submit data
         * @returns {void}
         */
        prepareProductFieldData: function (data) {
            if (_.isUndefined(data) || _.isUndefined(data.product)) {
                return;
            }

            if (_.isUndefined(data.product.products_config)) {
                data.product.products_config = data.product;

                return;
            }

            if (!_.isUndefined(data.product.products_config)) {
                _.extend(data.product, data.product.products_config);

                delete data.product.products_config;
            }
        },

        /**
         * Converts string to boolean
         *
         * @param {String} value
         * @returns {Boolean}
         */
        toBoolean: function (value) {
            return !!+value;
        }
    };
});
