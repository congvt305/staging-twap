/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 29/7/20
 * Time: 7:47 PM
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.ampromoPopup', widget, {
            /**
             *
             * @returns {void}
             */
            _loadItems: function () {
                let requestUrl = this.options.sourceUrl;
                let searchArr = '';
                if (requestUrl.indexOf('amp;') > -1)
                {
                    searchArr = requestUrl.split('&amp;');
                    requestUrl = searchArr.join('&');
                }
                this.options.sourceUrl = requestUrl;
                this._super();
            }
        });
        return $.mage.ampromoPopup;
    }
});
