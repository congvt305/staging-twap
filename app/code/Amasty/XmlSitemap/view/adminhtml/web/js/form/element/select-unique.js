define([
    'uiRegistry',
    'ko',
    'underscore',
    'Magento_Ui/js/form/element/select'
], function (registry, ko, _, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            links: {
                availableEntities: '${ $.provider }:availableEntities'
            },
            exports: {
                availableEntities: '${ $.provider }:availableEntities'
            },
            imports: {
                availableEntities: '${ $.provider }:availableEntities'
            },
            listens: {
                '${ $.provider }:availableEntities': 'onAvailableEntitiesChanged'
            },
            deferredEntitiesInit: []
        },

        /**
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('availableEntities');

            this.value.subscribe(this.onValueChange.bind(this));
            this.availableEntities.subscribe(this.onAvailableEntitiesChanged.bind(this));
            this.skip = null;

            return this;
        },

        /**
         * @param {String} value
         * @returns {void}
         */
        onValueChange: function (value) {
            var entities;

            if (this.skip === true) {
                return;
            }

            if (value) {
                entities = this.availableEntities();

                if (!entities) {
                    this.deferredEntitiesInit.push(value);

                    return;
                }

                if (entities[value].allowed === false) {
                    this.onValueChange(this.getAvailableValue(entities));

                    return;
                }

                entities[value].allowed = false;

                if (!_.isUndefined(this.initialValue) && this.initialValue !== value) {
                    entities[this.initialValue].allowed = true;

                    // Skip options change on value change in self components
                    this.skipOptionsReset = true;
                }

                // Skip onValueChange if entities[value]['allowed'] === false to escape loop
                this.skip = true;
                this.value(value);
                this.skip = false;

                // Set initial value to compare previous and current values
                this.initialValue = value;
                this.syncEntities(entities);
            }
        },

        /**
         * @param {Array} entities
         * @returns {void}
         */
        initDeferredEntities: function (entities) {
            _.each(this.deferredEntitiesInit, function (entityCode) {
                entities[entityCode].allowed = false;
            });

            this.syncEntities(entities);
        },

        /**
        * @param {String} currentValue
        * @param {Array} entities
        *
        * @returns {Array}
        */
        getOptionsFromAvailableEntities: function (currentValue, entities) {
            var options = [];

            _.each(entities, function (entity) {
                if (entity.allowed === true || entity.value === currentValue) {
                    options.push({
                        value: entity.value,
                        label: entity.label,
                        labeltitle: entity.label
                    });
                }
            });

            return options;
        },

        /**
        * @param {Array} entities
        *
        * @returns {String}
        */
        getAvailableValue: function (entities) {
            var availableEntity = _.find(entities, function (option) {
                return option.allowed === true;
            });

            return availableEntity.value;
        },

        /**
        * @param {Array} entities
        * @returns {void}
        */
        syncEntities: function (entities) {
            this.availableEntities(entities);
        },

        /**
        * @param {Array} entities
        * @returns {void}
        */
        onAvailableEntitiesChanged: function (entities) {
            var entityCode = this.value();

            // Dont do anything on initialize. Block onValueChange on value change/other value change
            this.skip = this.skip === null ? null : true;

            if (!this.skipOptionsReset) {
                this.setOptions(this.getOptionsFromAvailableEntities(entityCode, entities));

                // Should reset current value. setOptions reset value to first available option
                this.value(entityCode);
            } else {
                this.skipOptionsReset = false;
            }

            this.skip = false;
        }
    });
});
