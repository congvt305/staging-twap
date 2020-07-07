/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Eguana_GWLogistics/js/model/cvs-location-address',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/shipping-rate-service',
    'Magento_GiftRegistry/js/model/shipping-rate-processor/gift-registry',
    'Magento_Checkout/js/model/shipping-save-processor',
    'Eguana_GWLogistics/js/model/shipping-save-processor/cvs-location'
], function (
    Component,
    CvsLocationAddress,
    addressList,
    shippingRateService,
    shippingRateProcessor,
    shippingSaveProcessor,
    cvsLocationShippingSaveProcessor
) {
    'use strict';

    //Register gift registry address provider
    if (window.checkoutConfig.activeCarriers.indexOf('gwlogistics') !== -1) {

        var address = new CvsLocationAddress(null);
        // var currentStore = customerData.get('current-store');
        // if (currentStore() && currentStore().entity_id && currentStore().address_data) {
        //     var addressData = currentStore().address_data;
        //     if ((addressData.company === undefined) && currentStore().name) {
        //         addressData.company = currentStore().name;
        //     }
        //     address = new storeDeliveryAddress(currentStore().entity_id, addressData);
        // }
        addressList.push(address);
    }


    //Register gist registry save shipping address processor
    shippingSaveProcessor.registerProcessor('cvs-location-address', cvsLocationShippingSaveProcessor);

    /** Add view logic here if needed */
    return Component.extend({});
});
