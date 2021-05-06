/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 3/9/20
 * Time: 7:42 PM
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'linepay_payment', // payment_method_code
                component: 'Eguana_LinePay/js/view/payment/method-renderer/linepaypayment' // js_renderer_component
            },
            // other payment method renderers if required
        );
        /** Add view logic here if needed */
        return Component.extend({
            checkWebsiteId: function () {
                return window.checkout.websiteId;
            },
        });
    }
);
