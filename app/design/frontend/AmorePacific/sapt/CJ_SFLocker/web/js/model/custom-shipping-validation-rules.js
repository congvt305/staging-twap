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
                'region_id': {
                    'required': true
                },
                'lastname': {
                    'required': true
                },
                'firstname': {
                    'required': true
                },
                'telephone': {
                    'required': true
                },
                'city': {
                    'required': true
                },
                'street': {
                    'required': true
                },
            };
        }
    };
});
