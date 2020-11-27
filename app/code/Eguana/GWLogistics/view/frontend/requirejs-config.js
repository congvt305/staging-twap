var config = {
    config: {
        mixins: {
            // 'Magento_Checkout/js/view/shipping': {
            //     'Eguana_GWLogistics/js/view/checkout/shipping/shipping-mixin': true
            // },
            'Magento_Checkout/js/model/quote': {
                'Eguana_GWLogistics/js/model/quote-ext': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Eguana_GWLogistics/js/view/shipping-mixin': true
            },
            // 'Magento_Checkout/js/view/shipping-information': {
            //     'Eguana_GWLogistics/js/view/shipping-information-ext': true
            // },
            'mage/validation': {
                'Eguana_GWLogistics/js/model/validation-mixin': true
            }
        }
    }
};
