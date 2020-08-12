/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    "map": {
        '*': {
            posform: 'Amore_CustomerRegistration/js/pos',
            checkboxfield: 'Amore_CustomerRegistration/js/checkboxfield',
            editform: 'Amore_CustomerRegistration/js/account-edit',
            createaccount: 'Amore_CustomerRegistration/js/account-create',
            verifymobile: 'Amore_CustomerRegistration/js/verifymobile',
            validateName: 'Amore_CustomerRegistration/js/validateNameRule',
        }
    },
    config: {
        mixins: {
            "Magento_Customer/js/validation": {
                "Amore_CustomerRegistration/js/ui-validation-mixin": true
            }
        }
    }
};
