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
    'CJ_SFLocker/js/view/home-delivery-selector',
    'mage/translate',
], function ($, ko, Component, _, selectShippingAddressAction, quote, formPopUpState, checkoutData, customerData, homeDeliverySelector, $t) {
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
         * @param {String} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
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
            homeDeliverySelector().getRegionValue();
            if($('#address-type-1').prop('checked')) {
                homeDeliverySelector().fillShippingAddressInfo(this.address());
            }
        },

        /**
         * Edit address.
         */
        editAddress: function () {
            formPopUpState.isVisible(true);
            this.showPopup();
            $('#shipping-new-address-form div[name="shippingAddress.telephone"]').hide();
            $('#shipping-new-address-form div[name="shippingAddress.firstname"]').hide();
            $('#shipping-new-address-form div[name="shippingAddress.lastname"]').hide();
            if ($('#shipping-new-address-form div[name="shippingAddress.country_id"] .note').length === 0) {
                $('#shipping-new-address-form div[name="shippingAddress.country_id"]').append('<span class="note"><span>' + $t('This delivery service only available in hongkong and macao.') + '</span></span>');
            }
        },

        /**
         * Show popup.
         */
        showPopup: function () {
            $('[data-open-modal="opc-new-shipping-address"]').trigger('click');
        },

        checkIsVnWebsite: function () {
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
