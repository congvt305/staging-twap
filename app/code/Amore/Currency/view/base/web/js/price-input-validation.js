define([
    'jquery',
    'Magento_Ui/js/lib/validation/utils'
], function ($, utils) {
    return function (validator) {
        validator.addRule(
            'validate-number-integer',
            function (value) {
                if (value) {
                    return Number.isInteger(utils.parseNumber(value))
                }
                return true;
            },
            $.mage.__('Please enter a integer number in this field.')
        )

        return validator;
    }
})
