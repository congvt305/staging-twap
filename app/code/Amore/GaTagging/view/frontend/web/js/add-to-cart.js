define([
    'jquery'
], function ($) {
    'use strict';

    function notify(eventName) {
        if (window.dataLayer) {
            //console.log('dataLayer.push ', eventName);
            window.dataLayer.push({'event': eventName});
        }
    }

    function addActionAddToCart() {
        let btnAddToCart = $('div.product.details.product-item-details div.product-item-inner form button.action.tocart.primary');
        if (btnAddToCart) {
            btnAddToCart.click(function () {
                notify('product');
            });
        }
    }

    return function (config) {
        addActionAddToCart();
    };
});
