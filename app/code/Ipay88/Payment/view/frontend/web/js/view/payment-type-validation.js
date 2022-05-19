define([
  'uiComponent',
  'Magento_Checkout/js/model/payment/additional-validators',
  'Ipay88_Payment/js/model/payment-type-validator'
], function (
  MagentoComponent,
  MagentoCheckoutPaymentAdditionalValidators,
  Ipay88PaymentTypeValidator
) {
  MagentoCheckoutPaymentAdditionalValidators.registerValidator(Ipay88PaymentTypeValidator)

  return MagentoComponent.extend({})
})
