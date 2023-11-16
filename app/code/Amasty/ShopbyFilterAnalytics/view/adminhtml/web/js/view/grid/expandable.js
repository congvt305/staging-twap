/**
 * Grid row view model with expandable detailed rows (options)
 */

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/grid/columns/column'
], function ($, _, registry, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Amasty_ShopbyFilterAnalytics/grid/cells/expandable',
            expandControl: false,
            detailedDataIndex: 'options_data',
            detailedDataMap: [],
            listens: {
                '${ $.provider }:reloaded': 'onReloaded'
            }
        },
        classes: {
            toggle: '-expanded',
            hover: '-hovered'
        },
        selectors: {
            row: "tr[data-repeat-index='{rowIndex}']",
            col: "[data-amshopbyfa-id='{id}']",
            cell: '[data-amshopbyfa-js="toggle-element"]',
            checkbox: 'input#idscheck{id}'
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            registry.get('index = ids', function (component) {
                this['component_multiselect'] = component;
            }.bind(this));
        },

        /**
         * @param {Object} record
         * @return {Array}
         */
        getDetailedItems: function (record) {
            return record[this.detailedDataIndex] || [];
        },

        resolveExpandControl: function (row) {
            var details = this.getDetailedItems(row);

            if (!details.length) {
                this.expandControl = false;

                return false;
            }

            return this.expandControl;
        },

        /**
         * Expand/collapse row
         *
         * @param {Number} rowIndex
         * @returns {void}
         */
        toggleRow: function (rowIndex) {
            if (!this.expandControl) {
                return;
            }

            $(this.selectors.row.replace('{rowIndex}', rowIndex))
                .find(this.selectors.cell)
                .toggleClass(this.classes.toggle);
        },

        /**
         * add/remove hover class to the hovered option in a parent row
         *
         * @param {Object} data
         * @returns {void}
         */
        toggleHover: function (data) {
            $(this.selectors.col.replace('{id}', data.option_id))
                .toggleClass(this.classes.hover);
        },

        /**
         * check/uncheck checkbox by id
         *
         * @param {Object} data
         * @returns {void}
         */
        toggleCheckbox: function (data) {
            var checkbox = $(this.selectors.checkbox.replace('{id}', data.attribute_id));

            this.component_multiselect._setSelection(data.attribute_id, false, !checkbox[0].checked);
        },

        /**
         * Reset cells state to collapsed after grid reloading
         * @returns {void}
         */
        onReloaded: function () {
            $(this.selectors.cell).removeClass(this.classes.toggle);
        },

        /**
         * @param {*} toCheck
         * @return {Boolean}
         */
        hasData: function (toCheck) {
            return !_.isEmpty(toCheck);
        }
    });
});
