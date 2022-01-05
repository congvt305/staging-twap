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
        $(document).ready(function($) {
            $("button").on("click", function(e) {
                var couponCode = this.id;
                var buttonId = this.id;
                var input = document.createElement('input');
                input.setAttribute('value', couponCode);
                document.body.appendChild(input);
                input.select();
                var result = document.execCommand('copy');
                document.body.removeChild(input);
                //change name of button
                document.getElementById(buttonId).innerText = "copied";
                return result;
            });
        });
    }
    return main;
});
