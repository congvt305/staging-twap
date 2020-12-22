/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/12/20
 * Time: 10:32 PM
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
            let splitUrl = requestUrl.split('eguana_faq');
            let category = "select[name='category']";
            $.ajax({
                url: splitUrl[0] + 'eguana_faq/faq/ajaxcall',
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
