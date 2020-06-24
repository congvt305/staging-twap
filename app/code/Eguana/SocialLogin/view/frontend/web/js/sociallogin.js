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
        'mage/url'
    ], function($, url) {
    'use strict';
    var buttonDataRole = $('[data-role="eguana_sociallogin"]');
    buttonDataRole.click(function(){
        var loginurl = $(this).data("href");
        var returnUrl = url.build('sociallogin/login/createcustomer');
        var win = window.open(
            loginurl,
            "_blank",
            "toolbar=yes,scrollbars=yes,resizable=yes,top=200,left=400,width=500,height=440"
        );
        var timer = setInterval(function() {
            if(win.closed) {
                clearInterval(timer);
                window.location.href = returnUrl;
            }
        }, 1000);
    });
});
