/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    "map": {
        "*": {
            'CartQtyUpdate': "Magento_Checkout/js/cart/ajax-qty-update",
            'popupDelete': "CJ_Checkout/js/cart/popup-delete"
        },
    },
    'config': {
        'mixins': {
            'Magento_Checkout/js/view/shipping-information': {
                'Magento_Checkout/js/view/shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Magento_Checkout/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/payment/default': {
                'Magento_Checkout/js/view/payment/default-mixin': true
            }
        }
    }
};
