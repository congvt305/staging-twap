/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
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
        let storeViewByName = "select[name='store_id_name']";
        $(document).on('change', storeViewByName, function () {
            let storeIds = $(storeViewByName).val();
            let requestUrl = window.BASE_URL;
            let splitUrl = requestUrl.split('redemption');
            let offlineStoreByName = "select[name='offline_store_id']";
            if (storeIds != "") {
                $.ajax({
                    url: splitUrl[0] + 'redemption/redemption/ajaxcall',
                    type: "POST",
                    showLoader: true,
                    data: {store_id: storeIds, form_key: window.FORM_KEY},
                }).done(function (result) {
                    $(offlineStoreByName).html('');
                    $(offlineStoreByName).html(result.storelist);
                });
            }
        });

        $(document).on('change', 'select[name="offline_store_id"]', function () {
            let counterIds = $(this).val();
            let storeId = $('select[name="store_id_name"]').val();
            let requestUrl = window.BASE_URL;
            let splitUrl = requestUrl.split('redemption');
            if (counterIds) {
                $.ajax({
                    url: splitUrl[0] + 'redemption/redemption/ajaxcounterseats',
                    type: "POST",
                    data: {'counterIds': counterIds, 'storeId': storeId, 'form_key': window.FORM_KEY},
                    beforeSend: function () {
                        $('body').loader('show');
                    },
                }).done(function (result) {
                    $('body').loader('hide');
                    if (result.success && result.counterSeats) {
                        $('.admin__scope-old').html(result.counterSeats);
                    } else {
                        $('.admin__scope-old').html('');
                    }
                });
            }
        });
    };
    return main;
});
