/*
 *  @author Eguana Team
 *  @copyriht Copyright (c) ${YEAR} Eguana {http://eguanacommerce.com}
 *  Created byPhpStorm
 *  User:  kashif
 *  Date: 5/03/20
 *  Time: 7:30 am
 */

define([
    'jquery',
    'moment',
    'jquery/validate',
    'mage/translate'
], function ($, moment) {
    'use strict';

    return function() {
        $.validator.addMethod(
            'validate-dob-custom',
            function (value, element, param) {
                let isKoreanDateFormate = false;
                if (value === '') {
                    return true;
                }
                var dob = $(element).parents('.customer-dob'),
                    dayVal, monthVal, yearVal, dobLength, day, month, year, curYear,
                    validYearMessage, validateDayInMonth, validDateMessage, today, dateEntered;

                $(dob).find('.' + this.settings.errorClass).removeClass(this.settings.errorClass);
                var params, dob_arr;
                if (param.dateFormat.indexOf(".") > 0) {
                    params = param.dateFormat.split('.');
                    dob_arr = value.split('.');
                    isKoreanDateFormate = true

                } else if (param.dateFormat.indexOf("/") > 0) {
                    params = param.dateFormat.split('/');
                    dob_arr = value.split('/');
                } else {
                    params = param.dateFormat.split('-');
                    dob_arr = value.split('-');
                }
                if (isKoreanDateFormate && dob_arr.length !== 4) {
                    this.dobErrorMessage = $.mage.__('Please enter a valid full date.');
                    return false;
                } else if(dob_arr.length !== 3 && !isKoreanDateFormate) {
                    this.dobErrorMessage = $.mage.__('Please enter a valid full date.');
                    return false;
                }

                for (var i=0; i<3; i++) {
                    var currentValue = params[i];
                    if (currentValue.indexOf("y") >= 0 || currentValue.indexOf("Y") >= 0) {
                        yearVal = dob_arr[i];
                    } else if (currentValue.indexOf("m") >= 0 || currentValue.indexOf("M") >= 0) {
                        monthVal = dob_arr[i];
                    } else if (currentValue.indexOf("d") >= 0 || currentValue.indexOf("D") >= 0) {
                        dayVal = dob_arr[i];
                    }
                }
                dobLength = dayVal.length + monthVal.length + yearVal.length;

                day = parseInt(dayVal, 10) || 0;
                month = parseInt(monthVal, 10) || 0;
                year = parseInt(yearVal, 10) || 0;
                curYear = (new Date()).getFullYear();

                if (!day || !month || !year) {
                    this.dobErrorMessage = $.mage.__('Please enter a valid full date.');

                    return false;
                }

                if (month < 1 || month > 12) {
                    this.dobErrorMessage = $.mage.__('Please enter a valid month (1-12).');

                    return false;
                }

                if (year < 1900 || year > curYear) {
                    validYearMessage = $.mage.__('Please enter a valid year (1900-%1).');
                    this.dobErrorMessage = validYearMessage.replace('%1', curYear.toString());

                    return false;
                }
                validateDayInMonth = new Date(year, month, 0).getDate();

                if (day < 1 || day > validateDayInMonth) {
                    validDateMessage = $.mage.__('Please enter a valid day (1-%1).');
                    this.dobErrorMessage = validDateMessage.replace('%1', validateDayInMonth.toString());

                    return false;
                }
                today = new Date();
                dateEntered = new Date();
                dateEntered.setFullYear(year, month - 1, day);

                if (dateEntered > today) {
                    this.dobErrorMessage = $.mage.__('Please enter a date from the past.');

                    return false;
                }
                return true;
            },
            function () {
                return this.dobErrorMessage;
            }
        );
    }
});
