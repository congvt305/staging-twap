<?php

namespace Eghl\PaymentMethod\Model;

use Eghl\PaymentMethod\Controller\Index\ResponseHandler;
use Eghl\PaymentMethod\Helper\Refund;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Pay In Store payment method model
 */
class PaymentMethod extends AbstractMethod
{
    protected $cwriter = '';
    protected $clogger = '';
    protected $helperData;

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'eghlpayment';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var Refund
     */
    protected $helperRefund;

    /**
     * @var ZendClientFactory $clientFactory
     */
    protected $clientFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * PaymentMethod constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param Refund $helperRefund
     * @param ZendClientFactory $clientFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param SerializerInterface $serializer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Refund $helperRefund,
        ZendClientFactory $clientFactory,
        PriceCurrencyInterface $priceCurrency,
        SerializerInterface $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data, $directory);
        $this->helperRefund  = $helperRefund;
        $this->clientFactory = $clientFactory;
        $this->priceCurrency = $priceCurrency;
        $this->serializer    = $serializer;

    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @api
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function refund(InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }

        $order            = $payment->getOrder();
        $storeId          = $order->getStoreId();
        $merchantId       = $this->helperRefund->getGeneralConfig('mid', $storeId);
        $merchantPassword = $this->helperRefund->getGeneralConfig('hashpass', $storeId);
        $apiUrl           = $this->helperRefund->getGeneralConfig('payment_url', $storeId);

        $paymentMethod    = $payment->getAdditionalInformation(ResponseHandler::EGHL_PAYMENT_CODE);
        $serviceID        = $payment->getAdditionalInformation(ResponseHandler::EGHL_SERVICE_ID);
        $paymentID        = $payment->getAdditionalInformation(ResponseHandler::EGHL_PAYMENT_ID);
        $currencyCode     = $payment->getAdditionalInformation(ResponseHandler::EGHL_CURRENCY_CODE);
        $amountToRefund   = number_format($this->priceCurrency->convertAndRound($amount), 2, '.', '');

        $params = [
            'TransactionType' => 'REFUND',
            'PymtMethod'      => $paymentMethod,
            'ServiceID'       => $serviceID,
            'PaymentID'       => $paymentID,
            'Amount'          => $amountToRefund,
            'CurrencyCode'    => $currencyCode,
        ];

        $params['HashValue'] = $this->helperRefund->calculateHashValue($params, $storeId);

        $this->helperRefund->log("--- Start Refund Online For Order {$order->getIncrementId()} ---");

        $this->helperRefund->log("Refund order with params:" . $this->serializer->serialize($params));

        $tokenBase = base64_encode($merchantId . ':' . $merchantPassword);
        $headers[] = 'Authorization: Basic ' . $tokenBase;

        /** @var ZendClient $client */
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setMethod(ZendClient::POST);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setParameterPost($params);

        try {
            $response = $client->request();
        } catch (\Zend_Http_Client_Exception $e) {
            $this->helperRefund->log($e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        }

        if (!$response->getBody()) {
            $this->helperRefund->log('Response is empty');
            throw new LocalizedException(__('Payment refund failed, please try again.'));
        }

        parse_str($response->getBody(), $refundResponse);
        $this->helperRefund->log("Response: " . $this->serializer->serialize($refundResponse));

        if (!isset($refundResponse['TxnStatus']) || $refundResponse['TxnStatus'] != 0) {
            $this->helperRefund->log($refundResponse['TxnMessage']);
            throw new LocalizedException(__($refundResponse['TxnMessage']));
        }

        return $this;
    }
}
