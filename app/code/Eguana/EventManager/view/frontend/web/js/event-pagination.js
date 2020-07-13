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
    'mage/url',
], function ($, url) {
    /**
     * @param config.totalEvent
     * @param config.condition
     * @param config.eventConfigValue
     */
    function main(config) {
        let count = 2;
        let totalEvent = config.totalEvent;
        let condition = config.condition;
        let eventConfigValue = config.eventConfigValue;
        $(document).on("click","#more-button", function () {
            if (count * eventConfigValue > totalEvent) {
                $("#event-count").text(totalEvent);
                $("#more-button").prop('disabled', true);
            } else {
                $("#event-count").text(count * eventConfigValue);
            }
            let apiUrl = url.build('events/index/ajaxcall/');
            $.ajax({
                url: apiUrl,
                type: "POST",
                data: {count:  count, condition:condition},
            }).done(function (data) {
                let valueHtml ='';
                if ($.isEmptyObject(data)) {
                    $(".event-list").html('');
                } else {
                    $(".event-list").html('');
                    $(".event-list").html(data);
                }
            });
            count++;
        });
    }
    return main;
});
