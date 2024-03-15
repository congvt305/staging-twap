/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/address-converter',
    'Eguana_GWLogistics/js/model/cvs-location-service',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-billing-address'
], function (
    wrapper,
    checkoutData,
    selectShippingAddress,
    addressConverter,
    pickupLocationsService,
    quote,
    selectBillingAddress
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
        checkoutDataResolver.applyBillingAddress = wrapper.wrapSuper(
            checkoutDataResolver.applyBillingAddress,
            function() {
                var shippingAddress

                if (quote.billingAddress()) {
                    selectBillingAddress(quote.billingAddress());

                    return;
                }
                shippingAddress = quote.shippingAddress();
                if (shippingAddress &&
                    shippingAddress.canUseForBilling() &&
                    (shippingAddress.isDefaultShipping() || !quote.isVirtual())
                ) {
                    //set billing address same as shipping by default if it is not empty
                    selectBillingAddress(quote.shippingAddress());
                }
            }
        )
        return checkoutDataResolver;
    };
});
