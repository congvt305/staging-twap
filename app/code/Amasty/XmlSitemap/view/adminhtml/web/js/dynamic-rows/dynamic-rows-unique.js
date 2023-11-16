define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'amxmlsitemap_helpers'
], function (_, dynamicRowsComponent, helpers) {
    'use strict';

    return dynamicRowsComponent.extend({
        defaults: {
            availableEntities: {},
            listens: {
                '${ $.provider }:availableEntities': 'onAvailableEntitiesChanged'
            },
            exports: {
                availableEntities: '${ $.provider }:availableEntities'
            },
            imports: {
                availableEntities: '${ $.provider }:availableEntities'
            },
            links: {
                parentEnabledFieldValue: '${ $.parentName }.is_additional_include:checked',
                separateEntityValue: 'index = is_separate_entity:value'
            },
            splitEntityName: 'filename'
        },

        /**
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('availableEntities')
                .observe('addButton')
                .observe('separateEntityValue')
                .observe('parentEnabledFieldValue');

            this.availableEntities.subscribe(this.onAvailableEntitiesChanged.bind(this));

            return this;
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            if (!_.isUndefined(this.source.data.is_additional_include)) {
                this.visible(helpers.toBoolean(this.source.data.is_additional_include));
            }

            this.parentEnabledFieldValue.subscribe(function (value) {
                this.visible(value);
            }.bind(this));

            return this;
        },

        /**
         * @inheritDoc
         */
        initHeader: function () {
            this._super();

            this.dynamicRowLabel = this._getDynamicRowLabel();

            this._separateEntityListener();
        },

        /**
         * OVERRIDE. Set visibility to dynamic-rows child
         *
         * @param {Boolean} state
         * @returns {void}
         */
        setVisible: function (state) {
            this.elems.each(function (record) {
                if (!_.isFunction(record.setVisible)) {
                    return;
                }

                record.setVisible(state);
            }, this);
        },

        /**
         * @param {Array} entities
         * @returns {void}
         */
        onAvailableEntitiesChanged: function (entities) {
            var hasAvailableItems = _.find(entities, function (entity) {
                return entity.allowed === true;
            });

            this.addButton(!_.isUndefined(hasAvailableItems));
        },

        /**
         * @param {String|Number} index
         * @param {Number} recordId
         * @returns {void}
         */
        processingDeleteRecord: function (index, recordId) {
            var entities,
                entityCode;

            helpers.updateRecordData(this.recordData(), this.elems());

            entities = this.availableEntities();
            entityCode = this.recordData()[recordId]['entity_code'];
            entities[entityCode].allowed = true;

            this._super(index, recordId);
            this.availableEntities(entities);
            this.changed(true);
        },

        /**
         * @returns {Object}
         */
        _getDynamicRowLabel: function () {
            return _.find(this.labels(), function (element) {
                return element.name === this.splitEntityName;
            }.bind(this));
        },

        /**
         * @returns {void}
         */
        _separateEntityListener: function () {
            if (!_.isUndefined(this.source.data.is_separate_entity)) {
                this._setRowLabelVisibility(helpers.toBoolean(this.source.data.is_additional_include));
            }

            this.separateEntityValue.subscribe(function (value) {
                if (!_.isUndefined(value)) {
                    this._setRowLabelVisibility(helpers.toBoolean(value));
                }
            }.bind(this));
        },

        /**
         * Set visibility for 'entity field' label
         *
         * @param {Boolean} value
         * @returns {void}
         */
        _setRowLabelVisibility: function (value) {
            this.dynamicRowLabel.visible(value);
        }
    });
});
