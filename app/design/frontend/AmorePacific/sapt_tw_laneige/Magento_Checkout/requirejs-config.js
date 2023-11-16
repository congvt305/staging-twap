/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Magento_Checkout/js/model/shipping-save-processor/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/payment/default': {
                'Magento_Checkout/js/view/payment/default-mixin': true
            }
        }
    }
};
