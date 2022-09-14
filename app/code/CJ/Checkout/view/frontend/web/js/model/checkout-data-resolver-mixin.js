/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/address-converter',
    'Eguana_GWLogistics/js/model/cvs-location-service'
], function (
    wrapper,
    checkoutData,
    selectShippingAddress,
    addressConverter,
    pickupLocationsService
) {
    'use strict';

    return function (checkoutDataResolver) {
        checkoutDataResolver.applyShippingAddress = wrapper.wrapSuper(
            checkoutDataResolver.applyShippingAddress,
            function () {
                var address;

                if (checkoutData.getSelectedShippingAddress() === 'cvs-pickup-address') {
                    address = addressConverter.formAddressDataToQuoteAddress(
                        checkoutData.getShippingAddressFromData()
                    );
                    address.CVSStoreName = checkoutData.getStoreName();
                    address.CVSAddress = checkoutData.getCVSAddress();
                    address.mobileNumber = address.telephone;
                    pickupLocationsService.selectForShipping(address);
                }
                this._super();
            });

        return checkoutDataResolver;
    };
});
