<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Adapter;

use Atome\MagentoPayment\Helper\PaymentHelper;
use Atome\MagentoPayment\Model\Adapter\Http\ApiCall;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class PaymentApi
{
    const PAYMENT_STATUS_PROCESSING = 'PROCESSING';
    const PAYMENT_STATUS_PAID = 'PAID';
    const PAYMENT_STATUS_FAILED = 'FAILED';
    const PAYMENT_STATUS_REFUNDED = 'REFUNDED';
    const PAYMENT_STATUS_CANCELLED = 'CANCELLED';

    protected $apiCall;
    protected $paymentGatewayConfig;
    protected $paymentRequestHelper;
    protected $objectManager;

    public function __construct(
        ApiCall $apiCall,
        PaymentGatewayConfig $paymentGatewayConfig,
        PaymentHelper $paymentHelper,
        ObjectManagerInterface $objectManager
    ) {
        $this->apiCall = $apiCall;
        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->paymentRequestHelper = $paymentHelper;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Zend_Http_Response $resp
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     */
    protected function httpResponseToPaymentResponse($resp, $referenceId = '')
    {
        $data = @json_decode($resp->getBody(), true) ?: null;
        if ($resp->getStatus() >= 400) {
            throw new LocalizedException(__('Atome API Error: HTTP=%1, Code=%2, Message=%3', $resp->getStatus(), $data['code'] ?? '', ($data['message'] ?? $resp->getBody()) . '(' . $referenceId . ')' ));
        }
        /** @var \Atome\MagentoPayment\Model\PaymentResponse $paymentResponse */
        $paymentResponse = $this->objectManager->create('\Atome\MagentoPayment\Model\PaymentResponse');
        $paymentResponse->setData($data);
        return $paymentResponse;
    }

    /**
     * @param Quote $quote
     * @param array $reqData
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function preparePayment(Quote $quote, &$reqData)
    {
        $reqData = $reqData ?: [];
        $reqData = array_merge($reqData, $this->paymentRequestHelper->build($quote));
        $url = $this->paymentGatewayConfig->getApiUrl('payments');
        $resp = $this->apiCall->send($url, $reqData, 'POST');
        return $this->httpResponseToPaymentResponse($resp);
    }

    /**
     * @param Order $order
     * @param array $reqData
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function preparePaymentFromOrder(Order $order, &$reqData)
    {
        $reqData = $reqData ?: [];
        $reqData = array_merge($reqData, $this->paymentRequestHelper->buildFromOrder($order));
        $url = $this->paymentGatewayConfig->getApiUrl('payments');
        $resp = $this->apiCall->send($url, $reqData, 'POST');
        return $this->httpResponseToPaymentResponse($resp);
    }

    /**
     * @param string $referenceId
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPaymentInfo($referenceId)
    {
        $url = $this->paymentGatewayConfig->getApiUrl("payments/$referenceId");
        $resp = $this->apiCall->send($url, [], 'GET');
        return $this->httpResponseToPaymentResponse($resp);
    }

    /**
     * @param string $referenceId
     * @param float $amount
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refundPayment($referenceId, $amount)
    {
        $url = $this->paymentGatewayConfig->getApiUrl("payments/$referenceId/refund");
        $resp = $this->apiCall->send($url, ['refundAmount'=>$this->paymentRequestHelper->formatAmount($amount)], 'POST');
        return $this->httpResponseToPaymentResponse($resp);
    }

    /**
     * @param string $referenceId
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelPayment($referenceId)
    {
        $url = $this->paymentGatewayConfig->getApiUrl("payments/$referenceId/cancel");
        $resp = $this->apiCall->send($url, [], 'POST');
        return $this->httpResponseToPaymentResponse($resp, $referenceId);
    }
}
