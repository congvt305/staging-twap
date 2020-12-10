/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 11/11/20
 * Time: 9:00 PM
 */
define([
    'jquery',
    'mage/url',
    'mage/translate'
], function ($, url) {

    function main() {
        $(document).on('change', '#date', function () {

            let date        = this.value;
            let id          = $('#counter_id').val();
            let slotsApiUrl = url.build('event/reservation/ajaxtimeslots/');

            $('#time_slot').prop('disabled', true);

            $.ajax({
                url: slotsApiUrl,
                type: 'POST',
                data: { 'counter_id': id, 'date': date },
            }).done(function (data) {
                $('#time_slot').prop('disabled', false);
                if (typeof data.output == 'undefined' || $.isEmptyObject(data.output)) {
                    let option = '<option value="">-- ' + $.mage.__('Select Time Slot') +' --</option>';
                    $('#time_slot').html(option);
                } else {
                    $('#time_slot').html(data.output);
                }
            });
        });
    }
    return main;
});
