<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 7/9/20
 * Time: 12:53 PM
 */
namespace Eguana\LinePay\Model;

use Eguana\LinePay\Model\Quote as LinePayModel;
use Magento\Framework\HTTP\Client\Curl;
use Eguana\LinePay\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Payment
 *
 * Get payment
 */
class Payment
{
    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var Data
     */
    private $linePayHelper;

    /**
     * @var Quote
     */
    private $quoteModel;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LinePayLogger
     */
    private $linePayLogger;

    /**
     * Payment constructor.
     * @param Curl $curl
     * @param Data $linePayHelper
     * @param Quote $quoteModel
     * @param UrlInterface $url
     * @param SerializerInterface $serializer
     * @param StoreRepositoryInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param LinePayLogger $linePayLogger
     */
    public function __construct(
        Curl $curl,
        Data $linePayHelper,
        LinePayModel $quoteModel,
        UrlInterface $url,
        SerializerInterface $serializer,
        StoreRepositoryInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        LinePayLogger $linePayLogger
    ) {
        $this->curlClient                        = $curl;
        $this->linePayHelper                     = $linePayHelper;
        $this->quoteModel                        = $quoteModel;
        $this->url                               = $url;
        $this->serializer                        = $serializer;
        $this->storeManager                      = $storeManager;
        $this->scopeConfig                       = $scopeConfig;
        $this->logger                            = $logger;
        $this->linePayLogger                     = $linePayLogger;
    }

    /**
     * Get redirect url
     * @return array
     */
    public function getRedirectUrl()
    {
        // Validate the quote id
        $quoteId = null;
        try {
            $quoteId = $this->quoteModel->getQuoteId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        if (!$quoteId) {
            return $this->setFailureStatus('Invalid Quote');
        }

        // Get the quote
        $quote = null;
        try {
            $quote = $this->quoteModel->getQuote();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        if (!$quote) {
            return $this->setFailureStatus('Quote not found');
        }

        // Validate currency code
        $baseCurrencyCode = $quote->getBaseCurrencyCode();
        if ($baseCurrencyCode !== 'TWD') {
            return $this->setFailureStatus('Invalid quote base currency');
        }

        //request payment api call
        $response = $this->requestPayment();
        if ($response['returnCode'] == '0000') {
            try {
                $quote->getPayment()->setAdditionalInformation(
                    'transaction_id',
                    $response['info']['transactionId']
                );
                $quote->getPayment()->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            return $this->setSuccessStatus($response['info']['paymentUrl']['web']);
        } else {
            return $this->setFailureStatus('LINE Pay : '.$response['returnMessage']);
        }
    }

    /**
     * Set failure status
     * @param $comment
     * @return array
     */
    private function setFailureStatus($comment)
    {
        return [
            'status' => 'Failure',
            'msg' => $comment
        ];
    }

    /**
     * Set success status
     * @param $url
     * @return array
     */
    private function setSuccessStatus($url)
    {
        return [
            'status' => 'Success',
            'url' => $url
        ];
    }

    /**
     * Request payment api
     * @return array|bool|float|int|string|null
     */
    private function requestPayment()
    {
        $priceInPoints = null;
        $apiUrl = $this->linePayHelper->getPaymentApiUrl();
        $channelId = $this->linePayHelper->getChannelId();
        $secretKey = $this->linePayHelper->getSecretKey();
        $apiUrl = $apiUrl."/v3/payments/request";
        try {
            $quoteId = $this->quoteModel->getReservedOrder();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        try {
            $quote = $this->quoteModel->getQuote();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        try {
            $quoteProducts = $this->quoteModel->getQuoteItemsPackage();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $priceInPoints = $quoteProducts['priceInPoints'];
        unset($quoteProducts['priceInPoints']);
        $packages[] = $quoteProducts;
        $shippingAmount = $this->addShippingInfo($quote);
        $priceInPoints = $priceInPoints + $shippingAmount['products'][0]['priceInPoints'];
        unset($shippingAmount['products'][0]['priceInPoints']);
        $pricePointsInfo = $this->addPriceInPointsInfo($priceInPoints);
        $packages[] = $shippingAmount;
        $packages[] =  $pricePointsInfo;
        $urls["confirmUrl"] = $this->url->getUrl('linepay/payment/authorize');
        $urls["cancelUrl"] = $this->url->getUrl('checkout/onepage/failure');
        $request["amount"] = (int)round($quote->getGrandTotal(), 0);
        $request["currency"] = "TWD";
        $request["orderId"] = $quoteId;
        $request["packages"] = $packages;
        $request["redirectUrls"] = $urls;
        //set true if capture amount
        $request["options"]["payment"]["capture"] = true;
        $request = $this->serializer->serialize($request);
        $uuid = uniqid();
        $data = $secretKey . '/v3/payments/request' . $request . $uuid;
        $signature = hash_hmac('sha256', $data, $secretKey, true);
        $signature = base64_encode($signature);
        $this->curlClient->setOptions(
            [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_HEADER => false,
                CURLOPT_VERBOSE => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-LINE-ChannelId: '.$channelId,
                    'X-LINE-Authorization-Nonce: '.$uuid,
                    'X-LINE-Authorization: '.$signature
                ]
            ]
        );
        $this->curlClient->post($apiUrl, []);
        return $this->serializer->unserialize($this->curlClient->getBody());
    }

    /**
     * Confirm payment api
     * @param $transactionId
     * @param $orderId
     * @param $amount
     * @return array|bool|float|int|string|null
     */
    public function confirmPayment($transactionId, $orderId, $amount)
    {
        $apiUrl = $this->linePayHelper->getPaymentApiUrl();
        $channelId = $this->linePayHelper->getChannelId();
        $secretKey = $this->linePayHelper->getSecretKey();
        $apiUrl = $apiUrl."/v3/payments/".$transactionId."/confirm";
        $request["amount"] = (int)round($amount, 0);
        $request["currency"] = "TWD";
        $uuid = uniqid();
        $request = $this->serializer->serialize($request);
        $data = $secretKey . "/v3/payments/".$transactionId."/confirm" . $request . $uuid;
        $signature = hash_hmac('sha256', $data, $secretKey, true);
        $signature = base64_encode($signature);
        $this->curlClient->setOptions(
            [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_HEADER => false,
                CURLOPT_VERBOSE => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-LINE-ChannelId: '.$channelId,
                    'X-LINE-Authorization-Nonce: '.$uuid,
                    'X-LINE-Authorization: '.$signature
                ]
            ]
        );
        $this->curlClient->post($apiUrl, []);
        $response = $this->serializer->unserialize($this->curlClient->getBody());
        return $response;
    }

    /**
     * Void payment api
     * @param $transactionId
     * @return array|bool|float|int|string|null
     */
    public function voidPayment($transactionId)
    {
        $apiUrl = $this->linePayHelper->getPaymentApiUrl();
        $channelId = $this->linePayHelper->getChannelId();
        $secretKey = $this->linePayHelper->getSecretKey();
        $apiUrl = $apiUrl."/v3/payments/authorizations/".$transactionId."/void";
        $uuid = uniqid();
        $data = $secretKey . "/v3/payments/authorizations/".$transactionId."/void" . $uuid;
        $signature = hash_hmac('sha256', $data, $secretKey, true);
        $signature = base64_encode($signature);
        $this->curlClient->setOptions(
            [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_VERBOSE => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-LINE-ChannelId: '.$channelId,
                    'X-LINE-Authorization-Nonce: '.$uuid,
                    'X-LINE-Authorization: '.$signature
                ]
            ]
        );
        $this->curlClient->post($apiUrl, []);
        $response = $this->serializer->unserialize($this->curlClient->getBody());
        return $response;
    }

    /**
     * Refund payment api
     * @param $transactionId
     * @param $amount
     * @param $storeId
     * @return array|bool|float|int|string|null
     */
    public function refundPayment($transactionId, $amount, $storeId)
    {
        try {
            $store = $this->storeManager->getById($storeId);
            $websiteId = $store->getWebsiteId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }
        if ($this->linePayHelper->getSandboxModeEnabled($websiteId)) {
            $apiUrl =  'https://sandbox-api-pay.line.me';
        } else {
            $apiUrl =  'https://api-pay.line.me';
        }
        $channelId = $this->linePayHelper->getChannelId($websiteId);
        $secretKey = $this->linePayHelper->getSecretKey($websiteId);
        $apiUrl = $apiUrl."/v3/payments/".$transactionId."/refund";
        $request["refundAmount"] = (int)round($amount, 0);
        $uuid = uniqid();
        $request = $this->serializer->serialize($request);
        $data = $secretKey . "/v3/payments/".$transactionId."/refund" . $request . $uuid;
        $signature = hash_hmac('sha256', $data, $secretKey, true);
        $signature = base64_encode($signature);
        $this->curlClient->setOptions(
            [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_VERBOSE => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-LINE-ChannelId: '.$channelId,
                    'X-LINE-Authorization-Nonce: '.$uuid,
                    'X-LINE-Authorization: '.$signature
                ]
            ]
        );
        $this->curlClient->post($apiUrl, []);
        $response = $this->serializer->unserialize($this->curlClient->getBody());
        $logParameters = [
            'request' => [
                'transactionId' => $transactionId,
                'apiUrl' => $apiUrl
            ],
            'response' => $response
        ];
        $message = 'Refund Payment API Call';
        $this->linePayLogger->addAPICallLog($message, $logParameters);

        return $response;
    }

    /**
     * Capture payment api
     * @param $transactionId
     * @return array|bool|float|int|string|null
     */
    public function capturePayment($transactionId, $orderId, $amount)
    {
        $apiUrl = $this->linePayHelper->getPaymentApiUrl();
        $channelId = $this->linePayHelper->getChannelId();
        $secretKey = $this->linePayHelper->getSecretKey();
        $apiUrl = $apiUrl."/v3/payments/authorizations/".$transactionId."/capture";
        $quote = $this->quoteModel->getQuote();
        $request["amount"] = (int)round($amount, 0);
        $request["currency"] = "TWD";
        $uuid = uniqid();
        $request = $this->serializer->serialize($request);
        $data = $secretKey . "/v3/payments/authorizations/".$transactionId."/capture" . $request . $uuid;
        $signature = hash_hmac('sha256', $data, $secretKey, true);
        $signature = base64_encode($signature);
        $this->curlClient->setOptions(
            [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_HEADER => false,
                CURLOPT_VERBOSE => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-LINE-ChannelId: '.$channelId,
                    'X-LINE-Authorization-Nonce: '.$uuid,
                    'X-LINE-Authorization: '.$signature
                ]
            ]
        );
        $this->curlClient->post($apiUrl, []);
        $response = $this->serializer->unserialize($this->curlClient->getBody());
        return $response;
    }

    /**
     * Add shipping info to payment request
     * @param $quote
     * @return array
     */
    private function addShippingInfo($quote)
    {
        $package = [];
        $shippingTax = $quote->getShippingAddress()->getTaxAmount() + $quote->getShippingAddress()->getShippingInclTax();
        $package['id'] = '1';
        $package['amount'] = (int)$shippingTax;
        $product['id'] = '1';
        $product['name'] = 'Shipping incl Tax';
        $product['imageUrl'] = $this->scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder');
        $product['quantity'] = 1;
        $product['priceInPoints'] = $shippingTax - (int)$shippingTax;
        $product['price'] = (int)$shippingTax;
        $products[] = $product;
        $package['products'] = $products;
        return $package;
    }

    /**
     * Add price in points info
     * @param $priceInPoints
     * @return array
     */
    private function addPriceInPointsInfo($priceInPoints)
    {
        $package = [];
        $package['id'] = '1';
        $package['amount'] = (int)round($priceInPoints, 0);
        $product['id'] = '1';
        $product['name'] = 'Products price';
        $product['imageUrl'] = $this->scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder');
        $product['quantity'] = 1;
        $product['price'] = (int)round($priceInPoints, 0);
        $products[] = $product;
        $package['products'] = $products;
        return $package;
    }
}
