<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Adapter;

use Atome\MagentoPayment\Model\Adapter\Http\ApiCall;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class ConfigApi
{
    protected $apiCall;
    protected $paymentGatewayConfig;
    protected $objectManager;

    public function __construct(
        ApiCall $apiCall,
        PaymentGatewayConfig $paymentGatewayConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->apiCall = $apiCall;
        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Zend_Http_Response $resp
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     */
    protected function httpResponseToPaymentResponse($resp)
    {
        $data = @json_decode($resp->getBody(), true) ?: null;
        if ($resp->getStatus() >= 400) {
            throw new LocalizedException(__('Atome API Error: HTTP=%1, Code=%2, Message=%3', $resp->getStatus(), $data['code'] ?? '', $data['message'] ?? $resp->getBody()));
        }
        /** @var \Atome\MagentoPayment\Model\PaymentResponse $paymentResponse */
        $paymentResponse = $this->objectManager->create('\Atome\MagentoPayment\Model\PaymentResponse');
        $paymentResponse->setData($data);
        return $paymentResponse;
    }

    /**
     * @param string $country
     * @return \Atome\MagentoPayment\Model\PaymentResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLocaleInfo($country)
    {
        $url = $this->paymentGatewayConfig->getApiUrl('variables/'. strtoupper($country));
        $resp = $this->apiCall->send($url, [], 'GET');
        return $this->httpResponseToPaymentResponse($resp);
    }
}