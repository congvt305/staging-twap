define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Eguana_GWLogistics/js/action/get-selected-cvs-data',
    'mage/url',
], function (ko, $, Component, quote, getSelectedCvsData, urlBuilder) {
    'use strict';
    return Component.extend({
        defaults: {
            isVisible: false,
            isMapVisible : true,
            displayArea: 'after-shipping-method-item',
            template: 'Eguana_GWLogistics/cvs-form',
            cvsMapFormTemplate: 'Eguana_GWLogistics/cvs-map-form',
            MerchantTradeNo: null,
            ServerReplyURL: urlBuilder.build('eguana_gwlogistics/ReceiverServerReply'),
            LogisticsType: 'CVS',
            LogisticsSubType: 'UNIMART',
            IsCollection: 'N',
            CVSStoreID: null,
            CVSStoreName: null,
            CVSTelephone: null,
            CVSAddress: null,
            device: null, //0: PC (default) 1: Mobile,
            tracks: {
                isVisible: true,
                isMapVisible: true,
                CVSStoreID: true,
                CVSStoreName: true,
                CVSTelephone: true,
                CVSAddress: true,
            }
        },
        windowActivateCount: 0,

        initialize: function () {
            this._super();
            this.windowActivateCount = 0;
            return this;
        },

        initObservable: function () {
            this._super();
            quote.shippingMethod.subscribe(function (data) {
                this.isVisible = (data.method_code + '_' + data.carrier_code === 'CVS_gwlogistics') ? true : false ;
            }, this)
            $(document).on('visibilitychange', $.proxy(this.onWindowActivated, this));
            return this;
        },

        onWindowActivated: function () {
            this.windowActivateCount++;
            if (this.windowActivateCount % 2 === 0) {
                console.log("Visibility of page has changed!");
                this.getCvsStoreData();
            }
        },

        getCvsStoreData: function () {
            var quoteId = quote.getQuoteId();
            getSelectedCvsData(quoteId).done(function (response) {
                this.CVSStoreID = response.CVSStoreID;
                this.CVSStoreName = response.CVSStoreName;
                this.CVSTelephone = response.CVSTelephone;
                this.CVSAddress = response.CVSAddress;
            }.bind(this));
        },

        openCvsMap: function () {
            console.log('openCvsMap');
            var mapWin = window.open('', 'cvsMapFormGw');
            // $.proxy($('#cvs-map-load-form').submit(), this);
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
