/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 9/2/21
 * Time: 6:00 PM
 */

define([
    'jquery'
], function ($) {
    "use strict";
    return function (config) {
        var id = config.id;
        var action = config.action;
        var productData = config.productData;
        var orderData = config.orderData;
        var pageView = config.pageView;
        var redemptionApplied = config.redemptionApplied;
        var redemptionConfirm = config.redemptionConfirm;

        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];
            t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window,
            document,'script','https://connect.facebook.net/en_US/fbevents.js');

        window.fb = function() {
            if (orderData.content_ids) {
                fbq('init', id, {
                    em : orderData.email,
                    ph : orderData.phone,
                    fn : orderData.firtname,
                    ln : orderData.lastname,
                    ct : orderData.city,
                    st : orderData.st,
                    country : orderData.country,
                    zp : orderData.zipcode
                });
            } else {
                fbq('init', id);
            }

            if (action == 'checkout_index_index' && pageView != 'enabled') {
                fbq.disablePushState = true;
            }

            if (pageView == 'enabled') {
                fbq('track', 'PageView');
            }

            if (action == 'catalog_product_view' && productData != 404) {
                fbq('track', 'ViewContent', {
                    content_name: productData.content_name ,
                    content_ids: productData.content_ids,
                    content_type: 'product',
                    value: productData.value,
                    currency: productData.currency
                });
            }

            if (orderData != 404) {
                fbq('track', 'Purchase', {
                    content_ids: orderData.content_ids,
                    content_type: 'product',
                    contents: orderData.contents,
                    value: orderData.value,
                    num_items : orderData.num_items,
                    currency: orderData.currency
                });
            }

            if (action == 'redemption_details_register' && redemptionConfirm == 'enabled') {
                $('.action-confirm').on('click', function () {
                    fbq('track', 'Foundation_check_finalcheck');
                })
            }
        };
        return window.fb();
    }
});
