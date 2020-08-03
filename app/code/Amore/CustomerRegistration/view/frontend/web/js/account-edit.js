/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/mage'
], function($){

    $('.customer-submit').on('click', function (event) {

        if ($('#region_id option:selected').val()) {
            var selectedRegion = $('#region_id option:selected').text();
            $('#dm_state').val(selectedRegion);
        }
    });
});