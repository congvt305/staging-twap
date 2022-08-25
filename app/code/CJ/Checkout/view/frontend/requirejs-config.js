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
            }
        }
    }
}
