define([
    'underscore',
    'jquery',
    'uiRegistry',
    'mage/translate',
    'Amasty_CheckoutStyleSwitcher/js/model/amalert',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/checkout-data',
    'Eguana_GWLogistics/js/model/cvs-location-service',
    'mage/validation'
], function (_, $, registry, $t, alert, customer, quote, addressConverter, setShippingInformationAction, checkoutData, pickupLocationService) {
    'use strict';

    var mixin = {
        placeOrder: function (data, event) {
            if (event) {
                event.preventDefault();
            }
            let deliveryMethod = quote.shippingMethod();
            let validateShippingAddressResult = false;
            let message = '';
            if (deliveryMethod.carrier_code === 'blackcat') {
                let telephoneComponent = registry.get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.telephone");
                let telephoneValidateResult = telephoneComponent.validate();
                let firstnameComponent = registry.get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.firstname");
                let firstnameValidateResult = firstnameComponent.validate();
                let lastnameComponent = registry.get("checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.lastname");
                let lastnameValidateResult = lastnameComponent.validate();
                let streetValidateResult = false;
                if (customer.isLoggedIn()) {
                    streetValidateResult = quote.shippingAddress().street[0] != "";
                } else {
                    streetValidateResult = $("input[name='street[0]']").val() != "";
                }
                let regionIdValidateResult = $("select[name='region_id']").find(":selected").val() != "0";
                validateShippingAddressResult = false;
                if (streetValidateResult && regionIdValidateResult && telephoneValidateResult.valid && firstnameValidateResult.valid && lastnameValidateResult.valid) {
                    validateShippingAddressResult = true;
                }
                if (!validateShippingAddressResult) {
                    message = $t('Please check the shipping address information.');
                    if (!firstnameValidateResult.valid || !lastnameValidateResult.valid) {
                        message = $t('Last name + first name must be the most 5 Chinese alphabets or 10 English alphabets and should not contain spaces.');
                    } else if (!telephoneValidateResult.valid) {
                        message = $t('Please enter exactly proper mobile number. Start with 09 and 10 digit.');
                    }
                }

            } else if (deliveryMethod.carrier_code === 'gwlogistics') {
                //Move code from cvs-selector to here
                var emailValidationResult,
                    cvsStoreNameValidationResult,
                    cvsStoreAddressValidationResult,
                    mobileValidationResult,
                    firstnameValidationResult,
                    lastnameValidationResult,
                    cvsFormSelector = '#checkout-step-cvs-selector form[data-role=cvs-map-load-form]',
                    loginFormSelector = '#cvs-selector form[data-role=email-with-possible-login]',
                    firstname = $(cvsFormSelector + ' input[name=firstname]').val(),
                    lastname = $(cvsFormSelector + ' input[name=lastname]').val(),
                    mobile = $(cvsFormSelector + ' input[name=mobile_number]').val(),
                    shippingAddress,
                    paymentMethod = quote.paymentMethod(),
                    selectedLocation = pickupLocationService.selectedLocation();

                if (!selectedLocation) {
                    return;
                }
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = $(loginFormSelector + ' input[name=username]').valid() ? true : false;

                    if (!emailValidationResult) {
                        $(this.loginFormSelector + ' input[name=username]').focus();
                        return false;
                    }
                }

                $(cvsFormSelector).validation();
                cvsStoreNameValidationResult = $(cvsFormSelector + ' input[name=CVSStoreName]').valid() ? true : false;
                cvsStoreAddressValidationResult = $(cvsFormSelector + ' input[name=CVSAddress]').valid() ? true : false;
                firstnameValidationResult = $(cvsFormSelector + ' input[name=firstname]').valid() ? true : false;
                lastnameValidationResult = $(cvsFormSelector + ' input[name=lastname]').valid() ? true : false;
                mobileValidationResult = $(cvsFormSelector + ' input[name=mobile_number]').valid() ? true : false;
                _.extend(selectedLocation, {'firstname': firstname, 'lastname': lastname, 'mobileNumber': mobile});
                pickupLocationService.selectForShipping(selectedLocation);

                if (cvsStoreNameValidationResult && cvsStoreAddressValidationResult && firstnameValidationResult && lastnameValidationResult && mobileValidationResult) {
                    validateShippingAddressResult = true;
                    shippingAddress = addressConverter.quoteAddressToFormAddressData(quote.shippingAddress());
                    checkoutData.setShippingAddressFromData(shippingAddress);
                    if (paymentMethod.method != 'linepay_payment') {
                        setShippingInformationAction();
                    }                
                }
                if (!validateShippingAddressResult) {
                    message = $t('Please check the shipping address information.');
                    if (!firstnameValidationResult || !lastnameValidationResult) {
                        message = $t('Last name + first name must be the most 5 Chinese alphabets or 10 English alphabets and should not contain spaces.');
                    } else if (!mobileValidationResult) {
                        message = $t('Please enter exactly proper mobile number. Start with 09 and 10 digit.');
                    }
                }


            }

            if (validateShippingAddressResult) {
                return this._super();
            } else {
                alert({content: "<div class='checkout-error-message'>" + message + "</div>"});
            }
            return false;
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
