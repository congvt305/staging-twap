/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 9/11/20
 * Time: 6:01 PM
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Eguana_LinePay/js/model/payment-method-validator'
    ],
    function (Component, additionalValidators, paymentMethodValidator) {
        'use strict';
        additionalValidators.registerValidator(paymentMethodValidator);
        return Component.extend({});
    }
);
