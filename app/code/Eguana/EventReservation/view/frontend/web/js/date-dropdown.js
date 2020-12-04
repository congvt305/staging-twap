/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 03/11/20
 * Time: 6:00 PM
 */
define([
    'jquery',
    'mage/url',
    'mage/translate'
], function ($, url) {

    function main() {
        $(document).on('change', '#counter_id', function () {

            let id          = this.value;
            let datesApiUrl = url.build('event/reservation/ajaxdates/');

            let option = '<option value="" selected="true">-- ' + $.mage.__('Select Time Slot') + ' --</option>';
            $('#time_slot').html(option);
            $('#date').prop('disabled', true);

            $.ajax({
                url: datesApiUrl,
                type: 'POST',
                data: { 'counter_id': id },
            }).done(function (data) {
                $('#date').prop('disabled', false);
                if (typeof data.output == 'undefined' || $.isEmptyObject(data.output)) {
                    let option = '<option value="">-- ' + $.mage.__('Select Date') + ' --</option>';
                    $('#date').html(option);
                } else {
                    $('#date').html(data.output);
                }
            });
        });
    }
    return main;
});
