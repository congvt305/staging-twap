define([
    'underscore',
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'Eguana_CustomerRefund/js/action/refund-offline',
    'mage/validation',
    'mage/translate',
], function (_, $, ModalComponent, refundAction) {
    'use strict';
    return ModalComponent.extend({
        defaults: {
            button: '[data-role=refund-offline-button]',
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
                        text: $.mage.__('Request to Refund'),
                        actions: ['actionDone']
                    }
                ]
            }
        },
        initObservable: function () {
            this._super();
            $(this.button).on('click', this.openModal.bind(this));
            return this;
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
                this.refund();
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

        refund: function () {
            var data = _.extend(this.getValues(), {'order_id': this.orderId});
            $('body').trigger('processStart');
            refundAction(data);
        },

    });
});
