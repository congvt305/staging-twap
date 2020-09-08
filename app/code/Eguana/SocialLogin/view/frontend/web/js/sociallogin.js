/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 19/6/20
 * Time: 2:51 PM
 */
require(
    [
        'jquery',
        'mage/url',
        'Magento_Customer/js/customer-data'
    ], function($, url, customerData) {
    'use strict';
    let buttonDataRole = $('[data-role="eguana_sociallogin"]');
    buttonDataRole.click(function(){
        let loginurl = $(this).data("href");
        let returnUrl = url.build('sociallogin/login/createcustomer');
        let win = window.open(
            loginurl,
            "_blank",
            "toolbar=yes,scrollbars=yes,resizable=yes,top=200,left=400,width=500,height=440"
        );
        let timer = setInterval(function() {
            if(win.closed) {
                clearInterval(timer);
                let form_data = null;
                let ajaxUrl = url.build('sociallogin/login/validatelogin');
                $.ajax({
                    url: ajaxUrl,
                    data: form_data,
                    type: "POST",
                    async:true
                }).done(function (data) {
                    customerData.invalidate(['customer']);
                    window.location.href = data['url']
                });
            }
        }, 500);
    });
});
