/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 2/7/20
 * Time: 2:00 PM
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/url',
    'jquery/ui',
    'mage/mage',
    'mage/translate',

], function ($, alert, url) {
    /**
     * @param config.totalEvent
     * @param config.condition
     */
    function main(config) {
        $(document).on("click","#previous-button", function() {
            count--;
            let pre_page = $("#nextButton").text();
            $("#nextButton").text(pre_page-1);
            $("#current-active-button").text(pre_page-2);
            $("#previous-button").text(pre_page-3);
            pre_page--;
            page--;
            if($("#previous-button").text() == 0){
                $("#previous-button").hide();
            }
            if (count*9 > totalEvent) {
                $("#nextButton").hide();
            } else {
                $("#nextButton").show();
            }
            let apiUrl = url.build('events/index/ajaxcall/');
            $.ajax({
                showLoader: true,
                url: apiUrl,
                type: "POST",
                data: {count:  count, condition:condition},
            }).done(function (data) {
                let valueHtml ='';
                if($.isEmptyObject(data)){
                    $(".event-list").html('');
                }else{
                    $(".event-list").html('');
                    $(".event-list").html(data);
                }
            });
        });
        let reversCount;
        let count = 1;
        let page = 2;
        let condition = config.condition;
        let totalEvent = config.totalEvent;
        $(document).on("click","#nextButton", function() {
            count++;
            $("#previous-button").show();
            $("#previous-button").text(page-1);
            $("#current-active-button").text(page);
            $("#nextButton").text(page+1);
            page++;
            let pre_page = page;
            if (count*9 > totalEvent) {
                $("#nextButton").hide();
            } else {
                $("#nextButton").show();
            }
            let apiUrl = url.build('events/index/ajaxcall/');
            $.ajax({
                showLoader: true,
                url: apiUrl,
                type: "POST",
                data: {count:  count, condition:condition},
            }).done(function (data) {
                let valueHtml ='';
                if($.isEmptyObject(data)){
                    $(".event-list").html('');
                }else{
                    $(".event-list").html('');
                    $(".event-list").html(data);
                }
            });
        });
    }
    return main;
});
