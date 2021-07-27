/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'uiComponent',
    'Amore_GcrmBanner/js/action/generate-coupon',
    'Magento_Banner/js/model/banner',
    'underscore',
    'jquery',
    'Magento_Ui/js/lib/core/storage/local'
], function (ko, Component, generateCouponAction, Banner, _, $, storage) {
// ], function (ko, Component, Banner, _, $, storage) {
    'use strict';

    /**
     * Stores banners initialized in getItems() method
     *
     * Prevent series reinitialization on consecutive calls to getItems() method
     *
     * @type {Array}
     */
    var initializedItems = [];
    var initializedItemsSize = 0;

    /**
     * @param {Object} bannerConfig
     */
    function getItems () {
        var applicableBanners = [],
            displayMode = 'salesrule', // catalogrule, salesrule, fixed
            rotationType = null,
            blockId = 'gcrm-remote-banner';

        if (!initializedItems[blockId] && !_.isEmpty(Banner.get('data')().items)) {
            applicableBanners = _.toArray(Banner.get('data')().items[displayMode]);
            initializedItems[blockId] = [];

            if (!_.isEmpty(applicableBanners)) {
                _.each(applicableBanners, function (banner) {
                    initializedItems[blockId].push({
                        html: banner.content,
                        bannerId: banner.id,
                        salesRuleId: banner.sales_ruleId
                    });
                });

                initializedItemsSize = initializedItems[blockId].length;
            }
        }

        return initializedItems[blockId];
    }

    return Component.extend({
        defaults: {
            visible: false,
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.banner = Banner.get('data');

            // var target = $('.banner-item');
            // var button = target.find('.coupon-generate-button');

        },

        /**
         * Register method after element render to have access to banner items.
         *
         * @param {HTMLElement} el
         */
        registerBanner: function (el) {
            var banner = $(el.parentElement);

            // this['getItems' + banner.data('banner-id')] = getItems.bind(
            // this['getItems'] = getItems.bind(
            //     null,
            //     banner
            // );
            this.getItems = getItems.bind(
                null,
                banner
            );
        },

        generateCoupon: function (e) {
            var deferred = $.Deferred();

            generateCouponAction(deferred, e.salesRuleId);

            $.when(deferred).done(function (couponcode) {
                $('#coupon_code_' + e.salesRuleId).text(couponcode);
                $('#coupon_button_' + e.salesRuleId).hide();
            });
        },

        buttonVisible: function () {
            return initializedItemsSize > 0;
        },

        isPromoDrawerVisible: function () {
            this.container = $('[data-role="gcrm-banner-container"]');
            this.container.toggleClass('active');
            this.container.children('.gcrm-popup-wrapper').slideToggle();
        }
    });
});
