var config = {
    config: {
        mixins: {
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
