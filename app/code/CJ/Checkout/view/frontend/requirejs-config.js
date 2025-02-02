var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart' : {
                'CJ_Checkout/js/minicart-content-mixin': true
            },
            'Magento_Checkout/js/view/payment': {
                'CJ_Checkout/js/view/payment-mixin': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'CJ_Checkout/js/validator-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'CJ_Checkout/js/model/checkout-data-resolver-mixin': true
            },
            'Magento_Checkout/js/checkout-data': {
                'CJ_Checkout/js/checkout-data-mixin': true
            }
        }
    }
}
