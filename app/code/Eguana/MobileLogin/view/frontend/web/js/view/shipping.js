/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 17/7/20
 * Time: 5:45 PM
 */
define(['jquery', 'mage/translate', 'Magento_Ui/js/modal/modal'], function ($, $t, modal) {
    'use strict';
    return function (Component) {
        return Component.extend({
            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                if(window.checkoutConfig.mobileLogin == 1) {
                    let loginFormSelector = 'form[data-role=email-with-possible-login]';
                    let emailValidationResult = $(loginFormSelector + ' input[name=username]').val();
                    let regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    if (regex.test(emailValidationResult)) {
                        this._super();
                    } else {
                        let options = {
                            type: 'popup',
                            responsive: true,
                            innerScroll: true,
                            buttons: [{
                                text: $.mage.__('Continue'),
                                class: '',
                                click: function () {
                                    this.closeModal();
                                }
                            }]
                        };
                        let popup = modal(options, $('#mobilelogin-popup-modal'));
                        $('#mobilelogin-popup-modal').modal('openModal');
                        return false;
                    }
                }
                this._super();
            }
        });
    }
});
