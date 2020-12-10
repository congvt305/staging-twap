define(
    [
        'jquery',
        'mage/url',
        'mage/mage'
    ], function ($, url) {
        'use strict';

        function pointsHistory() {
            $(document).on("click", ".history-pager", function () {
                let requestedPage = $(this).data("page");
                let ajaxUrl = url.build('pointsintegration/points/historyajax/');
                $(this).addClass('active');

                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {page:  requestedPage},
                }).done(function (data) {
                    if ($.isEmptyObject(data)) {
                        $(".points-history-list").html('');
                    } else {
                        $(".points-history-list").html('');
                        $(".points-history-list").html(data);
                    }
                }).fail(function (jqXHR, testStatus, errorThrown) {
                    console.log(jqXHR);
                    console.log(testStatus);
                    console.log(errorThrown);
                });
            });
        }
        return pointsHistory;
    }
)
