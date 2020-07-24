/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'firstname': {
                    'required': true
                },
                'lastname': {
                    'required': true
                },
                'telephone': {
                    'required': true
                }
            };
        }
    };
});
