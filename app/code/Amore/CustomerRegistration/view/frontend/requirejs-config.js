/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    "map": {
        '*': {
            posform: 'Amore_CustomerRegistration/js/pos',
            checkboxfield: 'Amore_CustomerRegistration/js/checkboxfield',
            privacypolicypopup: 'Amore_CustomerRegistration/js/newsletter-privacy-policy-popup'
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
