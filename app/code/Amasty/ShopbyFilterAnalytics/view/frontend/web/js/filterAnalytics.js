define([
    'jquery'
], function ($) {
    $.widget('mage.amShopbyFilterAnalytics', {
        options: {
            filterData: [],
            requestUrl: [],
            isFirstLoad: true
        },

        _create: function () {
            if ('requestIdleCallback' in window) {
                requestIdleCallback(this.sendAjaxCallback.bind(this));
            } else {
                this.sendAjax();
            }
        },

        sendAjaxCallback: function(deadline) {
            while (deadline.timeRemaining() > 0) {
                this.sendAjax();
            }
        },

        sendAjax: function() {
            if (this.options.isFirstLoad === true) {
                jQuery.ajax({
                    type: "POST",
                    data: this.options.filterData,
                    url: this.options.requestUrl
                });
                this.options.isFirstLoad = false;
            }
        }
    });

    return $.mage.amShopbyFilterAnalytics;
});
