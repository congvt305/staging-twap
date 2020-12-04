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
    'mage/url',
    'domReady!'
], function ($, url) {
    'use strict';
    function main() {
        let storeViewByName = "select[name='store_id']";
        $(document).on('change', storeViewByName, function (){
            let storeIds = $(storeViewByName).val();
            let requestUrl = window.BASE_URL;
            let splitUrl = requestUrl.split('news');
            let category = "select[name='category']";
            $.ajax({
                url: splitUrl[0]+'news/manage/ajaxcall',
                type: "POST",
                showLoader: true,
                data: {store_id:  storeIds, form_key: window.FORM_KEY},
            }).done(function (result) {
                $(category).html('');
                $(category).html(result.category);
            });
        });
    };
    return main;
});
