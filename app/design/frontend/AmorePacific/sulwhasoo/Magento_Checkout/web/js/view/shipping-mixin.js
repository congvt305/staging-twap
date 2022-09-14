define([
    'ko',
    'jquery',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/new-customer-address',
], function (ko, $, selectShippingAddressAction, newCustomerAddress) {
    'use strict';

    var mixin = {
        /**
         * @inheritdoc
         */
        isLoggedIn: ko.observable(window.isCustomerLoggedIn),
        address: {},

        setPreviewShipping: function () {
            var self = this;
            if (!this.isLoggedIn()) {
                var isUpdateAddress = false;

                $("form.form-shipping-address :input:visible").each(function(index, field) {
                    if (typeof self.address[field.name] == 'undefined' ||
                        field.value &&  self.address[field.name] != field.value
                    ) {
                        if (field.name == 'region_id') {
                            self.address['region'] =
                                {
                                    'region':field.options[field.selectedIndex].innerHTML,
                                    'region_id':field.value
                                };
                        }
                        if (field.name == 'city_id') {
                            self.address['city'] = field.options[field.selectedIndex].innerHTML;
                        }

                        if (field.name == 'street[0]') {
                            self.address['street'] = [field.value];
                        }
                        self.address[field.name] = field.value;
                        isUpdateAddress = true;
                    }
                });
                //Check if change value in form then set address again
                if (isUpdateAddress) {
                    selectShippingAddressAction(newCustomerAddress(self.address));
                }
            }
        },
    };

    return function (shippingInformation) {
        return shippingInformation.extend(mixin);
    };
});
