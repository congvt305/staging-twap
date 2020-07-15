define([
    'underscore',
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'Eguana_CustomerRefund/js/action/rma/refund-offline',
    'Eguana_CustomerRefund/js/action/rma/submit-rma',
    'mage/url',
    'mage/validation',
    'mage/translate',
], function (_, $, ModalComponent, refundAction, submitRmaAction, url) {
    'use strict';
    return ModalComponent.extend({
        defaults: {
            rmaForm: '#rma_create_form',
            options: {
                type: 'popup',
                responsive: true,
                modalClass: 'modal-slide bankinfo-modal',
                focus: '.bankinfo-modal .input-text:first',
                buttons: [
                    {
                        'class': 'action secondary cancel',
                        text: $.mage.__('Cancel'),
                        actions: ['actionCancel']
                    },
                    {
                        'class': 'action primary confirm',
                        text: $.mage.__('Request to Return and Refund.'),
                        actions: ['actionDone']
                    }
                ]
            }
        },
        initObservable: function () {
            this._super();
            $(this.rmaForm).on('submit', this.openPopup.bind(this));
            return this;
        },

        openPopup: function (e) {
            var submitUrl = $(this.rmaForm).attr('action');
            if ($(this.rmaForm).validation('isValid') === true) {
                e.preventDefault();
                return this.openModal().then(_.bind(this.saveBankinfo, this))
                    .then(_.bind(this.rmaSubmit, this))
                    .then( _.bind(this.redirect, this));
            }
        },

        /**
         * Set values
         *
         * @param {Object} data
         * @returns void
         */
        setValues: function (data) {
            this.elems().forEach(function (elem) {
                if (_.isFunction(elem.setValues)) {
                    elem.setValues(data);
                }
            });
        },

        /**
         * Get values
         *
         * @returns {Object}
         */
        getValues: function () {
            var values = {};

            this.elems().forEach(function (elem) {
                if (_.isFunction(elem.getValues)) {
                    _.extend(values, elem.getValues());
                }
            });

            return values;
        },

        /**
         * Open modal
         *
         * @return {Promise}
         */
        openModal: function () {
            this._super();
            this.dfd = $.Deferred();
            return this.dfd.promise();
        },

        /**
         * Action done
         */
        actionDone: function () {
            var form = $(this.modal).find('form').validation();

            if (form.valid()) {
                this.dfd.resolve(this.getValues());
                this.closeModal();
            }
        },

        /**
         * Action cancel
         */
        actionCancel: function () {
            this.dfd.reject();
            this.closeModal();
        },

        saveBankinfo: function () {
            var bankInfoData = _.extend(this.getValues(), {'order_id': this.orderId});
            $('body').trigger('processStart');
            refundAction(bankInfoData);
        },

        rmaSubmit: function () {
            var formData = $(this.rmaForm).serialize();
            var submitUrl = $(this.rmaForm).attr('action');
            submitRmaAction(formData, submitUrl);
        },

        redirect: function () {
            $('body').trigger('processStop');
            window.location.href = url.build('sales/order/history');
        }

    });
});
