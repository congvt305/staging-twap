/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 22/6/20
 * Time: 7:26 PM
 */
var config = {
    map: {
        '*': {
            mobileLoginRule: "Eguana_MobileLogin/js/mobileLoginValidationRule",
            'Magento_Checkout/template/form/element/email.html': 'Eguana_MobileLogin/template/form/element/email.html',
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/form/element/email': {
                'Eguana_MobileLogin/js/view/form/element/email': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Eguana_MobileLogin/js/view/shipping': true
            }
        }
    }
};
