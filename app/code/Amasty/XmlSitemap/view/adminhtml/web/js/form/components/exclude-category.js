define([
    'underscore',
    'Magento_Ui/js/form/element/ui-select'
], function (_, Select) {
    'use strict';

    return Select.extend({
        /**
         * @inheritDoc
         */
        initConfig: function (config) {
            this.excludeDisabledCategories(config.options);

            this._super();

            return this;
        },

        /**
         * Override
         *
         * Determinate root category by the 'path' attribute
         *
         * @param {Object} data
         * @returns {Boolean}
         */
        isLabelDecoration: function (data) {
            return _.isEmpty(data.path);
        },

        /**
         * Remove 'disabled' categories from options data
         *
         * @param {Object} item
         * @returns {void}
         */
        excludeDisabledCategories: function (item) {
            _.each(item, function (element, i) {
                if (_.isUndefined(element)) {
                    return;
                }

                if (element.is_active === '0') {
                    item.splice(i, 1);

                    return;
                }

                if (element.optgroup && element.optgroup.length) {
                    this.excludeDisabledCategories(element.optgroup);
                }
            }.bind(this));
        }
    });
});
