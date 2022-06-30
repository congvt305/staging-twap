define([
  'jquery',
  'mage/translate',
  'Magento_Ui/js/model/messageList',
  'Magento_Checkout/js/model/quote'
], function (
  $,
  $t,
  MagentoMessageList,
  MagentoCheckoutQuote
) {
  'use strict'
  return {
    validate () {
      if (MagentoCheckoutQuote.paymentMethod() && MagentoCheckoutQuote.paymentMethod().method !== 'ipay88_payment') {
        return true
      }

      const isShowAvailablePaymentTypes = window.checkoutConfig.payment.ipay88_payment.showAvailablePaymentTypes
      if (!isShowAvailablePaymentTypes) {
        return true
      }

      const hasSelectedPaymentType = !!jQuery(':radio[name="ipay88_payment_id"]:checked').length
      if (hasSelectedPaymentType) {
        return true
      }

      MagentoMessageList.addErrorMessage({
        message: $t('Please select your preferred Ipay88 payment type before placing the order.')
      })

      return false
    }
  }
})