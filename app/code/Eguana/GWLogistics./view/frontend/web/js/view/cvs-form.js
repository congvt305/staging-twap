define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'mage/url'
], function (ko, $, Component, urlBuilder) {
    'use strict';
    return Component.extend({
        defaults: {
            displayArea: 'shippingAdditional',
            template: 'Eguana_GWLogistics/cvs-form',
            cvsMapFormTemplate: 'Eguana_GWLogistics/cvs-map-form',
            MerchantTradeNo: null,
            ServerReplyURL: urlBuilder.build('eguana_gwlogistics/ReceiverServerReply'),
            LogisticsType: 'CVS',
            LogisticsSubType: 'FAMI',
            IsCollection: 'N',
            device: null //0: PC (default) 1: Mobile
        },
        initialize: function () {
            this.setCvsMapFormData();
            console.log('cvs initialized.');
            this._super();
        },
        openCvsMap: function () {
            var mapWin = window.open('', 'cvsMapFormGw');
            document.getElementsById('cvs-map-load-form').submit();
            var timer = setInterval(function() {
                if(mapWin.closed) {
                    clearInterval(timer);
                    alert('closed');
                }
            }, 1000);
        },
        getMerchantTradeNo: function () {
            return Date.now().toString();
        },
        setCvsMapFormData: function () {
            console.log('cvs initialized.');
            // this.device(this.isMobile());
            // this.ServerReplyURL('http://192.168.0.1/ReceiverServerReply');
        },
        isMobile: function () {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }
    });
});
