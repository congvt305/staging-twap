/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
	config: {
		mixins: {
			'Magento_Checkout/js/view/shipping': {
				'Eguana_OrderDeliveryMessage/js/shipping-validation-mixin': true
			},
			'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
				'Eguana_OrderDeliveryMessage/js/payload-extender-mixin': true
			}
		}
	}
};
