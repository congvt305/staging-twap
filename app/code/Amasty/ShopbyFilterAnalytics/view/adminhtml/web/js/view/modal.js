/**
 * Extended Modal component for filters collector
 */
define([
    'underscore',
    'Amasty_ShopByQuickConfig/js/view/modal'
], function (_, Modal) {
    'use strict';

    return Modal.extend({
        defaults: {
            modules: {
                listing: '${ $.name }.analytics_list'
            }
        },

        /**
         * Return data for save action.
         *
         * @returns {Object}
         * @protected
         */
        _collectData: function () {
            return this.getFiltering();
        },

        /**
         * Extracts filtering data from data provider.
         *
         * @returns {Object} Current filters state.
         */
        getFiltering: function () {
            var source = this.listing().externalSource(),
                keys = ['filters', 'search', 'namespace'];

            if (!source) {
                return {};
            }

            return _.pick(source.get('params'), keys);
        }
    });
});
