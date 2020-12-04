/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 2/7/20
 * Time: 2:00 PM
 */
define([
    'jquery',
    'mage/url',
    'mage/translate',
], function ($, url) {
    /**
     * @param config.totalEvent
     * @param config.condition
     * @param config.eventConfigValue
     */
    function main(config) {
        var ticket_id = $('#ticket_id').val();
        $(document).ready(function($) {
            $("#add-msg").on("click", function(e){
                var data = $('form[class="messages"]').serialize();
                var message = $('#note').val();
                if(message.length != 0)
                {
                    var formData = new FormData();
                    var files = jQuery('input[type="file"]');
                    var count = files.length;
                    for (var index = 0; index < files.length-1; index++)
                    {
                        formData.append("file[]", files[index].files[0]);
                    }
                    formData.append("attachment", true);
                    formData.append("message", message);
                    formData.append('formData', $('form[class="messages"]').serialize());
                    $.ajax({
                        showLoader: true,
                        data: formData,
                        type: "POST",
                        enctype: 'multipart/form-data',
                        processData: false,
                        contentType: false,
                        url: url.build('ticket/note/save')
                    }).done(function(msg) {
                        location.reload();
                    });
                }
                else
                {
                    $('#note').css('border-color', ' #ff6666');
                    $('.note-error').css('display', 'block');
                }
            });
            $("#add_note").on("click", function(e){
                $('.note-panel').css('display', 'block');
            });
            $("#cancel").on("click", function(e){
                $('.note-panel').css('display', 'none');
            });
        });
    }
    return main;
});
