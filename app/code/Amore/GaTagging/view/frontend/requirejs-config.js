var config = {
    map: {
        '*': {
            'Magento_Checkout/js/proceed-to-checkout': 'Amore_GaTagging/js/checkout/proceed-to-checkout',
        },
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Amore_GaTagging/js/model/place-order-mixin': true
            },
            'Magento_Customer/js/customer-data': {
                'Amore_GaTagging/js/view/customer-data-mixin': true
            }
        }
    }
};

