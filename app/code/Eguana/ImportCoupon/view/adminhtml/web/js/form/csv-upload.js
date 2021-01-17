/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 10/1/21
 * Time: 12:29 PM
 */
define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    function main() {
        /**
         * this function is used to enable/disable csv file upload button
         */
        $(document).on('change', 'select[name="coupon_type"], input[name="use_auto_generation"]', function () {
            var ruleId = $('input[name="rule_id"]').val();
            var couponType = $('select[name="coupon_type"]').val();
            var autoGenerationCheck = $('input[name="use_auto_generation"]').is(":checked");

            if (couponType == 2 && autoGenerationCheck && ruleId) {
                $('input[name="coupon_csv_file"]').attr('disabled', false);
            } else {
                $('input[name="coupon_csv_file"]').attr('disabled', true);
            }
        });
    }
    return main;
});
