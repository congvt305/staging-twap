/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
	config: {
		mixins: {
			'Magento_Checkout/js/view/shipping': {
				'Eguana_OrderDeliveryMessage/js/shipping-validation-mixin': false,
				'Eguana_OrderDeliveryMessage/js/shipping-validation-mixin-overriden': true
			}
		}
	}
};
