/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    deps: [
        'Magento_Theme/js/modernizr.min',
        'Magento_Theme/js/sapt_sws_common',
        'Magento_Theme/js/spa.min'
    ],
	config: {
		mixins: {
			'Magento_Checkout/js/view/shipping': {
				'Eguana_OrderDeliveryMessage/js/shipping-validation-mixin': false,
				'Eguana_OrderDeliveryMessage/js/shipping-validation-mixin-overriden': true
			},
            'mage/collapsible': {
                'js/mage/collapsible-mixin': true
            }
		}
	}
};
