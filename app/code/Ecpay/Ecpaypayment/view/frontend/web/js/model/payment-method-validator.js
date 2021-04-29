define(
    [
        'jquery',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'mage/url'
    ],
    function ($, $t, messageList, quote, url) {
        'use strict';
        return {
            validate: function () {

                let isValid = true;
                const paymentMethod = quote.paymentMethod().method;

                const dataForm = $('#ecpay_payment_form');
                const choosenPayment = dataForm.find('select[name="ecpay_choosen_payment"]').val();
                const paymentMethods = window.checkoutConfig.payment.ecpay_ecpaypayment.ecpayPaymentMethods;

                let barCodeValid = true;
                const eInvoiceForm = $('#custom-checkout-form');
                const choosenEInvoice = eInvoiceForm.find('input:radio[name="ecpay_einvoice_type"]:checked').val();
                $('.triplicate-title-error-msg').hide();
                $('.tax-id-number-error-msg').hide();

                if (choosenEInvoice == 'cellphone-barcode-invoice') {
                    if ($("input:text[id=cellphone_barcode]").val() != ' ') {
                        var barCode = $("input:text[id=cellphone_barcode]").val();
                        barCode = barCode.replace(/\s+/g, "+");
                        $("input:text[id=cellphone_barcode]").val(barCode);
                    }
                }
                if (choosenEInvoice == 'triplicate-invoice') {

                    let requiredFieldEntered = true;
                    let ecPayTriplicateTitle = $("input:text[id=triplicate_title]").val();
                    if (ecPayTriplicateTitle == '') {
                        $('.triplicate-title-error-msg').show();
                        requiredFieldEntered = false;
                    }
                    let ecPayTaxIdNumber = $("input:text[id=tax_id_number]").val();
                    if (ecPayTaxIdNumber == '') {
                        $('.tax-id-number-error-msg').show();
                        requiredFieldEntered = false;
                    }
                    if (requiredFieldEntered == false) {
                        return requiredFieldEntered;
                    }
                }
                const barCodeValue = $("input:text[id=cellphone_barcode]").val();

                if (paymentMethod === 'ecpay_ecpaypayment') {
                    if (paymentMethods.indexOf(choosenPayment) === -1) {
                        isValid = false;
                    }

                    if (choosenEInvoice === 'cellphone-barcode-invoice') {
                        $.ajax({
                            dataType: 'json',
                            url: url.build('ecpay_ecpaypayment/payment/paymentcontroller'),
                            data: {barCodeValue: barCodeValue},
                            async: false,
                            cache: false,
                            type: 'post',
                            success: function (result) {
                                if (typeof result.IsExist === 'undefined' || result.IsExist !== 'Y') {
                                    isValid = false;
                                    barCodeValid = false;
                                }
                            },
                            error: function () {
                                isValid = false;
                                barCodeValid = false;
                            }
                        });
                    }
                }

                if (!isValid) {
                    messageList.addErrorMessage({ message: $t('Invalid payment method.') });

                    if (!barCodeValid) {
                        messageList.addErrorMessage({ message: $t('Invalid BarCode.') });
                    }
                    $("html, body").animate({scrollTop: 0}, "slow");
                }

                return isValid;
            }
        }
    }
);
