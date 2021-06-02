/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */

define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';
    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (proceed, payload) {
            payload = proceed(payload);
            var is_apply_identifier = "input[name='is_apply']:checked";
            var company_name_identifier = "input[name='company_name']";
            var tax_code_identifier = "input[name='tax_code']";
            var state_identifier = "select[name='state']";
            var city_identifier = "select[name='city']";
            var road_name_identifier = "input[name='road_name']";

            var is_apply_value = $(is_apply_identifier).val();
            var company_name_value = $(company_name_identifier).val();
            var tax_code_value = $(tax_code_identifier).val();
            var state_value = $(state_identifier).val();
            var city_value = $(city_identifier).val();
            var road_name_value = $(road_name_identifier).val();

            payload.addressInformation.extension_attributes.is_apply = is_apply_value;
            payload.addressInformation.extension_attributes.company_name = company_name_value;
            payload.addressInformation.extension_attributes.tax_code = tax_code_value;
            payload.addressInformation.extension_attributes.state = state_value;
            payload.addressInformation.extension_attributes.city = city_value;
            payload.addressInformation.extension_attributes.road_name = road_name_value;
            return payload;
        });
    };
});
