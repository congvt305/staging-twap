define([
    'jquery',
    'mage/template',
    'underscore',
    'jquery-ui-modules/widget',
    'mage/validation',
    'regionUpdater'
], function ($, mageTemplate, _) {
    'use strict';

    $.widget('mage.cityUpdater', {
        options: {
            cityTemplate:
                '<option value="<%- data.value %>" <% if (data.isSelected) { %>selected="selected"<% } %>>' +
                '<%- data.title %>' +
                '</option>',
            isCityRequired: true,
            currentCity: null,
        },

        /**
         *
         * @private
         */
        _create: function () {
            this._initRegionElement();
            this.currentCityOption = this.options.currentCity;
            this.cityTmpl = mageTemplate(this.options.cityTemplate);
            this._updateCity(this.element.find('option:selected').val());
            this._initZipcodeElement();

            $(this.options.cityListId).on('change', $.proxy(function (e) {
                this.setOption = false;
                this.currentCityOption = $(e.target).val(); //city id
                $(this.options.cityInputId).val($(e.target).find('option:selected').text());
                this._updateZipcode(this.currentCityOption);
            }, this));

            $(this.options.cityInputId).on('focusout', $.proxy(function () {
                this.setOption = true;
            }, this));

            this.element.on('change', $.proxy(function () {
                $(this.options.cityListId).val('');
            }, this));
        },

        /**
         *
         * @private
         */
        _initRegionElement: function () {
            this.element.on('change', $.proxy(function (e) {
                this._updateCity($(e.target).val());
            }, this));
        },

        _initZipcodeElement: function () {
            $(this.options.cityListId).on('change', $.proxy(function (e) {
                this.currentCityOption = $(e.target).val(); //city id
                this._updateZipcode(this.currentCityOption);
            }, this));
        },

        /**
         * Remove options from dropdown list
         *
         * @param {Object} selectElement - jQuery object for dropdown list
         * @private
         */
        _removeSelectOptions: function (selectElement) {
            selectElement.find('option').each(function (index) {
                if (index) {
                    $(this).remove();
                }
            });
        },

        /**
         * Render dropdown list
         * @param {Object} selectElement - jQuery object for dropdown list
         * @param {String} key - region id
         * @param {Object} value - city object
         * @private
         */
        _renderSelectOption: function (selectElement, key, value) {
            selectElement.append($.proxy(function () {
                var name = value.name.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&'),
                    tmplData,
                    tmpl;

                if (value.code && $(name).is('span')) { //todo check here??
                    key = value.code;
                    value.name = $(name).text();
                }

                tmplData = {
                    // value: key,
                    value: value.code,
                    title: value.name,
                    isSelected: false
                };

                if (this.options.defaultCity === value.name) {
                    tmplData.isSelected = true;
                }

                tmpl = this.cityTmpl({
                    data: tmplData
                });

                return $(tmpl);
            }, this));
        },

        /**
         * Takes clearError callback function as first option
         * If no form is passed as option, look up the closest form and call clearError method.
         * @private
         */
        _clearError: function () {
            var args = ['clearError', this.options.cityListId, this.options.cityInputId, this.options.postcodeId];

            if (this.options.clearError && typeof this.options.clearError === 'function') {
                this.options.clearError.call(this);
            } else {
                if (!this.options.form) {
                    this.options.form = this.element.closest('form').length ? $(this.element.closest('form')[0]) : null;
                }

                this.options.form = $(this.options.form);

                this.options.form && this.options.form.data('validator') &&
                this.options.form.validation.apply(this.options.form, _.compact(args));

                // Clean up errors on region & zip fix
                $(this.options.cityInputId).removeClass('mage-error').parent().find('[generated]').remove();
                $(this.options.cityListId).removeClass('mage-error').parent().find('[generated]').remove();
                $(this.options.postcodeId).removeClass('mage-error').parent().find('[generated]').remove();
            }
        },

        /**
         * Update dropdown list based on the region selected
         *
         * @param {String} region - 2 uppercase letter for region code|id
         * @private
         */
        _updateCity: function (region) {
            // Clear validation error messages
            var cityList = $(this.options.cityListId),
                cityInput = $(this.options.cityInputId),
                postcode = $(this.options.postcodeId),
                label = cityList.parent().siblings('label'),
                container = cityList.parents('div.field');


            this._clearError();
            this._checkCityRequired(region);

            $(cityList).find('option:selected').removeAttr('selected');
            $(this.options.cityListId).val('');

            // Populate state/province dropdown list if available or use input box
            if (this.options.cityJson[region]) { //executed when optional city is enabled.
                this._removeSelectOptions(cityList);
                $.each(this.options.cityJson[region], $.proxy(function (key, value) {
                    this._renderSelectOption(cityList, key, value);
                }, this));

                if (this.currentCityOption) {
                    cityList.val(this.currentCityOption);
                    postcode.val('');
                }

                if (this.setOption) {
                    cityList.find('option').filter(function () {
                        return this.text === cityInput.val();
                    }).attr('selected', true);
                }

                if (this.options.isCityRequired) {
                    cityList.addClass('required-entry').removeAttr('disabled');
                    container.addClass('required').show();

                    cityInput.hide();
                    cityList.show();
                    label.attr('for', cityList.attr('id'));
                } else {
                    cityList.removeClass('required-entry validate-select').removeAttr('data-validate');
                    container.removeClass('required');

                    if (!this.options.optionalCityAllowed) { //eslint-disable-line max-depth //todo system config to be implement
                        cityList.hide();
                        container.hide();
                    } else {
                        cityList.show();
                    }
                }
            } else { //not executed when optional city is enabled.
                this._removeSelectOptions(cityList);

                if (this.options.isCityRequired) {
                    cityInput.addClass('required-entry').removeAttr('disabled');
                    container.addClass('required').show();
                } else {
                    if (!this.options.optionalCityAllowed) {
                        cityInput.attr('disabled', 'disabled');
                        container.hide();
                    }
                    container.removeClass('required');
                    cityInput.removeClass('required-entry');
                }

                cityList.removeClass('required-entry').prop('disabled', 'disabled').hide();
                cityInput.show();
                label.attr('for', cityInput.attr('id'));
            }

            // Add defaultvalue attribute to city select element
            cityInput.attr('defaultvalue', this.options.defaultCity);
            cityList.find('option').filter(function () {
                return this.text === cityInput.val();
            }).attr('selected', true);
        },

        _updateZipcode: function (cityId) {
            $(this.options.postcodeId).val(cityId);
        },

        /**
         * Check if the selected country has a mandatory city selection
         * temporarily use mandatary region country value...
         *
         * @param {String} country - Code of the country - 2 uppercase letter for country code
         * @private
         */
        _checkCityRequired: function (region) {
            var self = this;

            this.options.isCitynRequired = false;
            $.each(this.options.cityJson.config['cities_required'], function (index, elem) {
                if (elem === region) {
                    self.options.isCitynRequired = true;
                }
            });
        }
    });

    return $.mage.cityUpdater;

});
