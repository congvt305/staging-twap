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
], function ($, url) {
    /**
     * @param config.totalEvent
     * @param config.condition
     * @param config.eventConfigValue
     */
    function main(config) {


        $(document).ready(function($) {
            $("#quote_send").on("click", function(e){
               var datafile = $('form[class="messages"]').serialize();
               var message = $('#negotiation_comment').val();
               if(message.length != 0)
               {
                   var formData = new FormData();
                   var url = config.url;
                   var url = url;
                   // add assoc key values, this will be posts values
                   var files = jQuery('input[type="file"]');
                   var count = files.length;
                   for (var index = 0; index < files.length-1; index++)
                   {
                       formData.append("file[]", files[index].files[0]);
                   }
                   // formData.append("attachment", true);
                   formData.append('form_key',window.FORM_KEY);
                   formData.append('msg',message);
                   formData.append('formData', $('form[class="messages"]').serialize());
                   $.ajax({
                       showLoader: true,
                       data: formData,
                       type: "POST",
                       mimeTypes: 'multipart/form-data',
                       url: url,
                       processData: false,
                       contentType: false,
                   }).done(function(msg) {
                       location.reload();
                   });
               }
               else
               {
                   $('#negotiation_comment').css('border-color', ' #ff6666');
                   $('.note-error').css('display', 'block');
               }
            });
            $("#add_note").on("click", function(e){
                $('.note-panel').css('display', 'block');
            })
            $("#cancel").on("click", function(e){
                $('.note-panel').css('display', 'none');
            });
        });
    }
    return main;
});
