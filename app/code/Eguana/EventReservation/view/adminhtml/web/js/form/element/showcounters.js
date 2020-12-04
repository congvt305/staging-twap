/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 20/11/20
 * Time: 12:00 PM
 */
define([
    'jquery',
    'mage/url',
    "reloadGrid"
], function ($, url, reloadGrid) {
    'use strict';
    function main() {
        let storeViewByName = "select[name='store_id']";
        $(document).on('change', storeViewByName, function () {
            let storeId = $(storeViewByName).val();
            let requestUrl = window.BASE_URL;
            let splitUrl = requestUrl.split('event');
            if (storeId) {
                $.ajax({
                    url: splitUrl[0] + 'event/reservation/ajaxavailablestores',
                    type: "POST",
                    showLoader: true,
                    data: { store_id:  storeId, form_key: window.FORM_KEY },
                }).done(function (result) {
                    if (result.success)
                        reloadGrid.reloadUIComponent("event_counter_listing.event_counter_listing_data_source");
                });
            }
        });
    };
    return main;
});
