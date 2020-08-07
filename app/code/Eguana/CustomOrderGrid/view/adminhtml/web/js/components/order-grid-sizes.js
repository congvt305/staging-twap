/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/8/20
 * Time: 5:26 PM
 */
define([
    'Magento_Ui/js/grid/paging/sizes'
], function (Sizes) {
    'use strict';

    return Sizes.extend({
        defaults: {
            value: 20,
            minSize: 1,
            maxSize: 100
        },

        exports: {
            value: '${ $.provider }:params.paging.page',
            options: '${ $.provider }:params.paging.options'
        },

        sizes: {
            '20': {
                value: 20,
                label: 20
            },
            '30': {
                value: 30,
                label: 30
            },
            '50': {
                value: 50,
                label: 50
            },
            '100': {
                value: 100,
                label: 100
            }
        },

        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.options = this.sizes;
            this.updateArray();

            return this;
        }
    });
});
