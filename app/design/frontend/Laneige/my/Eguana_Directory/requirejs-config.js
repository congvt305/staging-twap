var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Eguana_Directory/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/model/address-converter': {
                'Eguana_Directory/js/model/address-converter-mixin': true
            }
        }
    }
};
