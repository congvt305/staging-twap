var config = {
    "map": {
        "*": {
            'popupDelete': "CJ_Checkout/js/cart/popup-delete"
        }
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
