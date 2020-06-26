/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 23/6/20
 * Time: 10:01 PM
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
     * @param config.totalVideo
     */
    function main(config) {
        let count = 2;
        let totalVideo = config.totalVideo;
        $(document).on("click","#more-button", function() {
            if (count*6 > totalVideo) {
                $("#video-count").text(totalVideo);
                $("#more-button").prop('disabled', true);
            } else {
                $("#video-count").text(count * 6);
            }
            let apiUrl = url.build('videoboard/index/ajaxcall/');
            $.ajax({
                showLoader: true,
                url: apiUrl,
                type: "POST",
                data: {count:  count},
            }).done(function (data) {
                let valueHtml ='';
                if($.isEmptyObject(data)){
                    $(".video-board-list").html('');
                }else{
                    $(".video-board-list").html('');
                    $(".video-board-list").html(data);
                }
            });
            count++;
        });
    }
    return main;
});
