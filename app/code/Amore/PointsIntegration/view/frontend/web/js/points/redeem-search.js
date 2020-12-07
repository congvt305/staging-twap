define(
    [
        'jquery',
        'mage/url',
        'mage/mage'
    ], function ($, url) {
        'use strict';

        function redeemPoints() {
            $(document).on("click", ".redeem-pager", function () {
                let requestedPage = $(this).data("page");
                let ajaxUrl = url.build('pointsintegration/points/ajax/');
                $(this).addClass('active');

                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {page:  requestedPage},
                }).done(function (data) {
                    if ($.isEmptyObject(data)) {
                        $(".redeem-points-list").html('');
                    } else {
                        $(".redeem-points-list").html('');
                        $(".redeem-points-list").html(data);
                    }
                }).fail(function (jqXHR, testStatus, errorThrown) {
                    console.log(jqXHR);
                    console.log(testStatus);
                    console.log(errorThrown);
                });
            });
        }
        return redeemPoints;
    }
)
