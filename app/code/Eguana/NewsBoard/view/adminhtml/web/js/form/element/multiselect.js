/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 10/11/20
 * Time: 6:10 PM
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/multiselect',
    'mage/url',
    'Magento_Ui/js/modal/modal'
], function ($, _, uiRegistry, multiselect, url) {
    'use strict';
    return multiselect.extend({
        hasChanged: function () {
            let requestUrl = window.BASE_URL;
            let splitUrl = requestUrl.split('news');
            let elem = this.inputName;
            url.setBaseUrl(BASE_URL);
            let apiUrl =  splitUrl[0]+'news/manage/ajaxcall';
            let category = "select[name='category']";
            if(elem == 'store_id') {
                $.ajax({
                    url: apiUrl,
                    type: "POST",
                    showLoader: true,
                    data: {store_id:  this.value()},
                }).done(function (result) {
                    $(category).html('');
                    $(category).html(result.category);
                });
            }
            return this._super();
        }
    });
});
