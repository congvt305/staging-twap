define([
    "jquery",
    "mage/cookies",
    "domReady!"
], function($) {
    "use strict";
    $.widget('mage.lineShopping', {
        options: {
            cookieEcidName: 'line_ecid',
            cookieLineInfoName: 'line-information',
            cookieLifeTime: 1,
            lineEcid: '',
            lineInfo: ''
        },
        _init: function() {
            let self = this;
            if (this._isCookieSet ('line_ecid') !== "1" )  {
                    self._setCookie(self.options.cookieEcidName, self.options.lineEcid, self.options.cookieLifeTime);
                    self._setCookie(self.options.cookieLineInfoName, self.options.lineInfo, self.options.cookieLifeTime);
            }
        },

        /**
         * Set cookie
         * @param name
         * @param value
         * @param days
         * @returns {*|string}
         * @private
         */
        _setCookie: function (name, value, days) {
            let d = new Date();
            d.setTime(d.getTime() + (days*24*60*60*1000));
            let expires = "expires=" + d.toGMTString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        },

        /**
         * Get cookie
         * @param name
         * @returns {*|string}
         * @private
         */
        _getCookie: function(name) {
            let cookie_name = name + "=";
            let ca = document.cookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(cookie_name) === 0) {
                    return c.substring(cookie_name.length, c.length);
                }
            }
            return "";
        },

        /**
         * Check if cookie is set
         *
         * @param cname
         * @returns {*|string}
         * @private
         */
        _isCookieSet: function (cname) {
            let self = this;
            return self._getCookie(cname);
        },

    });
    return $.mage.lineShopping;
});
