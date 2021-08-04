<?php

namespace Eguana\LinePay\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;

class PaymentInformationResetOrderId extends \Adyen\Payment\Plugin\PaymentInformationResetOrderId
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param \Adyen\Payment\Logger\AdyenLogger $adyenLogger
     * @param \Magento\Framework\Webapi\Rest\Request $request
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        \Adyen\Payment\Logger\AdyenLogger $adyenLogger,
        \Magento\Framework\Webapi\Rest\Request $request
    )
    {
        $this->request = $request;
        parent::__construct($quoteRepository, $adyenLogger);
    }

    /**
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param $cartId
     * @return null
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
        $cartId
    )
    {
        $params = $this->request->getBodyParams();
        if (isset($params['paymentMethod']['method']) && $params['paymentMethod']['method'] === 'linepay_payment') {
            return null;
        }
        return parent::beforeSavePaymentInformationAndPlaceOrder($subject, $cartId);
    }
}
