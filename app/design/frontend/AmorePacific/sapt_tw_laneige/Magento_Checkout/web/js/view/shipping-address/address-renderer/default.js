/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

 define([
     'jquery',
     'ko',
     'uiComponent',
     'underscore',
     'Magento_Checkout/js/action/select-shipping-address',
     'Magento_Checkout/js/model/quote',
     'Magento_Checkout/js/model/shipping-address/form-popup-state',
     'Magento_Checkout/js/checkout-data',
     'Magento_Customer/js/customer-data',
     'mage/translate',
     'Magento_Customer/js/model/customer'
 ], function ($, ko, Component, _, selectShippingAddressAction, quote, formPopUpState, checkoutData, customerData, $t, customer) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/address-renderer/default',
            isPickupMethod: ko.observable(false),
            enableMacau: ko.observable(parseInt(window.checkoutConfig.enable_macau))
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            if (quote.shippingMethod()) {
                this.isPickupMethod(quote.shippingMethod().method_code === 'pickup');
            }
            this.isSelected = ko.computed(function () {
                var isSelected = false,
                    shippingAddress = quote.shippingAddress();

                if (shippingAddress) {
                    isSelected = shippingAddress.getKey() == this.address().getKey(); //eslint-disable-line eqeqeq
                }

                return isSelected;
            }, this);

            return this;
        },

        /**
         * @param {String} text
         * @return {String}
         */
        translate: function (text) {
            return $t(text.trim());
        },

        /**
         * @param {String} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * @returns {boolean}
         */
        isNewAddressSelected: function (){
            return this.address().getKey() == "new-customer-address";
        },
        /**
         * Get customer attribute label
         *
         * @param {*} attribute
         * @returns {*}
         */
        getCustomAttributeLabel: function (attribute) {
            var resultAttribute;

            if (typeof attribute === 'string') {
                return attribute;
            }

            if (attribute.label) {
                return attribute.label;
            }

            if (typeof this.source.get('customAttributes') !== 'undefined') {
                resultAttribute = _.findWhere(this.source.get('customAttributes')[attribute['attribute_code']], {
                    value: attribute.value
                });
            }

            return resultAttribute && resultAttribute.label || attribute.value;
        },

        /** Set selected customer shipping address  */
        selectAddress: function () {
            let message = $('.validation-shipping-address');
            if (this.validationAddress(this.address())) {
                message.addClass('no-display');
                selectShippingAddressAction(this.address());
                checkoutData.setSelectedShippingAddress(this.address().getKey());
            } else {
                message.removeClass('no-display');
            }
        },

        /** validation first name and last name */
        validationAddress: function (address) {
            var value = address.firstname + address.lastname,
                telephone = address.telephone;
            return (/^[a-zA-Z]{4,10}$/.test(value) || /^[\u4e00-\u9fa5]{2,5}$/.test(value)) && /^[0]{1}[9]{1}\d{8}$/.test(telephone);
        },

        /**
         * Edit address.
         */
        editAddress: function () {
            formPopUpState.isVisible(true);
            this.showPopup();
        },

        /**
         * Show popup.
         */
        showPopup: function () {
            $('[data-open-modal="opc-new-shipping-address"]').trigger('click');
        },

        checkIsVnWebsite: function () {
            var count = $(".shipping-address-items").find(".shipping-address-item").length;
            if (!customer.isLoggedIn() && count){
                $(".action.action-show-popup.amcheckout-button").prop("disabled", true);
            }
            let websiteId = window.checkout.websiteId;
            if (websiteId == 8) {
                return 1;
            } else {
                return 0;
            }
        },

        /**
         *
         * @param address
         * @returns {boolean}
         */
        allowSelectMacauAddress(address) {
           return !(!this.enableMacau() && address().region == 'Macau');
        }
    });
});
