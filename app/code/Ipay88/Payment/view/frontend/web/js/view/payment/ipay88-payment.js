define(
  [
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
  ],
  function (
    MagentoUiComponent,
    MagentoCheckoutPaymentRendererList
  ) {
    'use strict';
    MagentoCheckoutPaymentRendererList.push(
      {
        type: 'ipay88_payment',
        component: 'Ipay88_Payment/js/view/payment/method-renderer/ipay88-payment'
      }
    );

    return MagentoUiComponent.extend({});
  }
);
