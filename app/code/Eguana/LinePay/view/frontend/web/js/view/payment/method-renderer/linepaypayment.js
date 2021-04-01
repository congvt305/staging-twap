/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 3/9/20
 * Time: 7:43 PM
 */
define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'mage/url',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/redirect-on-success',
        'domReady!'
    ],
    function ($, ko, $t, url, Component, placeOrderAction, additionalValidators, quote, customerData, redirectOnSuccessAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Eguana_LinePay/payment/payment',
                invoiceChecked: ko.observable('greenworld-invoice')
            },

            /** @inheritdoc */
            initObservable: function () {

                this._super()
                    .observe([
                        'paymentMethod'
                    ]);
                return this;
            },

            /**
             * @return {String}
             */
            getCode: function () {
                return 'linepay_payment';
            },

            /**
             * @return {String}
             */
            getTitle: function () {
                return 'LINE Pay';
            },

            /**
             * @return {String}
             */
            getLinePayLogoSrc: function () {
                return window.checkoutConfig.payment.linepay_payment.linePayLogoSrc;
            },

            /**
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'ecpay_einvoice_type': $("input:radio[name=linepay_einvoice_type]:checked").val(),
                        'ecpay_einvoice_triplicate_title': $("input:text[id=line_pay_triplicate_title]").val(),
                        'ecpay_einvoice_tax_id_number': $("input:text[id=line_pay_tax_id_number]").val(),
                        'ecpay_einvoice_cellphone_barcode': $("input:text[id=line_pay_ecpay_cellphone_barcode]").val(),
                    }
                };
                return data;
            },
            isMobile: function (){
                let check = false;
                (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
                return check;
            },
            replacePlus: function (data) {
                if (typeof data.additional_data.ecpay_einvoice_cellphone_barcode !== 'undefined') {
                    var barcode = data.additional_data.ecpay_einvoice_cellphone_barcode;
                    barcode = barcode.replace('+', '%2B');
                    data.additional_data.ecpay_einvoice_cellphone_barcode = barcode;
                }
                return data;
            },
            checkoutToLinePay: function (){
                var self = this;
                if (event) {
                    event.preventDefault();
                }
                if (additionalValidators.validate()) {
                    self.isPlaceOrderActionAllowed(false);
                    customerData.invalidate(['cart']);
                    let linepay = url.build('linepay/payment/redirect');
                    if (self.isMobile()) {
                        var jsonData = self.replacePlus(this.getData());
                        linepay = url.build('linepay/payment/redirect'+'?data='+JSON.stringify(jsonData));
                        $.mage.redirect(linepay);
                    } else {
                        let body = $('body').loader();
                        let win = window.open(
                            linepay,
                            "_blank",
                            "toolbar=yes,scrollbars=yes,resizable=yes,top=200,left=400,width=500,height=440"
                        );
                        body.loader('show');
                        let timer = setInterval(function() {
                            if(win.closed) {
                                clearInterval(timer);
                                body.loader('hide');
                                self.getPlaceOrderDeferredObject()
                                    .fail(
                                        function () {
                                            self.isPlaceOrderActionAllowed(true);
                                        }
                                    )
                                    .done(
                                        function () {
                                            $.mage.redirect(url.build('checkout/onepage/success'));
                                        }
                                    );
                                return false;
                            }
                        }, 500);
                    }
                }
            },
            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), this.messageContainer)
                );
            }
        });
    }
);
