/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 27/7/20
 * Time: 3:40 PM
 */
require([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/confirm'
], function ($, url, confirmation) {
    'use strict';
    let leaveBtn = $("#account-leave");
    leaveBtn.click(function (e) {
        e.preventDefault();
        let accountLeaveUrl = url.build('pip/account/leave');
        let line2 = $.mage.__('When you press OK, all your member information in Laneige ');
        let line3 = $.mage.__('(including: membership, accumulated spending and membership points) will be deleted ');
        let line4 = $.mage.__('(details will be deleted simultaneously at the department store and the official website) ');
        let line5 = $.mage.__('Confirm delete → select "OK"; leave → select "Cancel" ');
        confirmation({
            title: $.mage.__('Cancel Member Account'),
            content: line2 + line3 + line4 + "<br>" + line5,
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
