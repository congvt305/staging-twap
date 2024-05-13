/**
 * Created by magenest on 15/01/2019.
 */
var config = {
    map: {
        '*': {
            bioEp: 'Magenest_Popup/js/lib/bioep',
            slick: 'Magento_PageBuilder/js/resource/slick/slick'
        }
    },
    shim: {
        'slick': {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            "mage/sticky": {
               "js/mage/sticky-mixin": true
           }
        }
    },
};