define([
    'ko',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/lib/validation/validator',
    'amxmlsitemap_helpers',
    'Magento_Ui/js/form/element/abstract'
], function (ko, _, $t, validator, helpers, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            label: $t('Sitemap Entity Filename'),
            notice: $t('Value will be included in the sitemap filename. Example - sitemap_products.xml'),
            validation: {
                'alphanumeric': true,
                'no-whitespace': true
            },
            links: {
                separateEntityValue: 'index = is_separate_entity:value',
                dynamicRowLabels: 'index = entities:labels',
                parentEnabledFieldValue: '${ $.parentName }.enabled:checked'
            },
            isDynamicRowField: false
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            this._super()
                .observe('separateEntityValue dynamicRowElems dynamicRowLabels parentEnabledFieldValue');

            return this;
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            this.visible = ko.pureComputed(function () {
                return this._checkVisibilityByType() && helpers.toBoolean(this.separateEntityValue());
            }, this);
        },

        /**
         * OVERRIDE. Change error message
         *
         * @inheritDoc
         */
        validate: function () {
            var value = this.value(),
                result = validator(this.validation, value, this.validationParams),
                message = !this.disabled() && this.visible() ? result.message.replace('spaces ', '') : '',
                isValid = this.disabled() || !this.visible() || result.passed;

            this.error(message);
            this.error.valueHasMutated();
            this.bubble('error', message);

            if (this.source && !isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        },

        /**
         * Return true if field component is in the dynamic rows,
         * otherwise returns value from parent 'enabled' checkbox component
         *
         * @returns {Boolean}
         */
        _checkVisibilityByType: function () {
            return this.isDynamicRowField ? true : this.parentEnabledFieldValue();
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
