/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */

var config = {
	config: {
		mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Eguana_RedInvoice/js/shipping-validation-mixin': true
            },
			'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
				'Eguana_RedInvoice/js/payload-extender-mixin': true
			}
		}
	}
};
