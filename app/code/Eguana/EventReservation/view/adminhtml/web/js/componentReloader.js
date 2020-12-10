/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/11/20
 * Time: 7:41 PM
 */
define([
    "uiRegistry"
], function (registry) {
    'use strict';
    return {
        reloadUIComponent: function (gridName) {
            if (gridName) {
                var params = [];
                var target = registry.get(gridName);
                if (target && typeof target === 'object') {
                    target.set('params.t ', Date.now());
                }
            }
        }
    };
});
