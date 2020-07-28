/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 27/7/20
 * Time: 3:40 PM
 */
require(
    [
        'jquery',
        'mage/url',
        'Magento_Ui/js/modal/confirm'
    ], function($, url, confirmation) {
        'use strict';
        let leaveBtn = $("#account-leave");
        leaveBtn.click(function(e){
            e.preventDefault();
            let accountLeaveUrl = url.build('pip/account/leave');
            confirmation({
                title: $.mage.__('Account Leave Confirmation'),
                content: $.mage.__('Are you sure you want to leave your account?'),
                actions: {
                    confirm: function () {
                        window.location.href = accountLeaveUrl;
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        });
    });
