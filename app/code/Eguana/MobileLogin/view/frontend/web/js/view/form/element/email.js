/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/7/20
 * Time: 6:41 PM
 */
define([], function () {
    'use strict';
    return function (Component) {
        return Component.extend({
            /**
             * Local email validation.
             *
             * @param {Boolean} focused - input focus.
             * @returns {Boolean} - validation result.
             */
            validateEmail: function () {
                if(window.checkoutConfig.mobileLogin == 1){
                    return true;
                }
                this._super();
            },
            /**
             * Get mobile login config.
             */
            getMobileLoginConfig: function () {
                if(window.checkoutConfig.mobileLogin == 1){
                    return true;
                }
                return false;
            }
        });
    }

});
