/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            'js/plugins/slick/slick' : 'Magento_PageBuilder/js/resource/slick/slick'
        }
    },
    deps: [
        'Magento_Theme/js/modernizr.min',
        'Magento_Theme/js/sapt_sws_common',
        'Magento_Theme/js/spa.min'
    ],
    config: {
        mixins: {
            'mage/collapsible': {
                'js/mage/collapsible-mixin': true
            }
        }
    }
};
