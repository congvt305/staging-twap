<?php

namespace Ecpay\Ecpaypayment\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as MagentoPaymentHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\StoreManagerInterface;

class Payment extends AbstractMethod
{
    protected $_code  = 'ecpay_ecpaypayment';

    protected $_formBlockType = 'Magento\Payment\Block\Form';
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';

    protected $_isGateway                   = true;
    protected $_isInitializeNeeded          = true;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canFetchTransactionInfo     = true;

    protected $_order;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_urlBuilder;

    private $prefix = 'ecpay_';
    private $libraryList = array('ECPayPaymentHelper.php');
    /**
     * @var Transaction\BuilderInterface
     */
    private $transactionBuilder;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $directoryList;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice
     */
    private $ecpayInvoice;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;
    /**
     * @var \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue
     */
    private $ECPayInvoiceCheckMacValue;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        MagentoPaymentHelper $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpayInvoice,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $ECPayInvoiceCheckMacValue,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_urlInterface = $urlInterface;
        $this->_storeManager = $storeManager;
        $this->transactionBuilder = $transactionBuilder;
        $this->curl = $curl;
        $this->directoryList = $directoryList;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->orderRepository = $orderRepository;
        $this->ecpayInvoice = $ecpayInvoice;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->ECPayInvoiceCheckMacValue = $ECPayInvoiceCheckMacValue;
    }

    public function getValidPayments()
    {
        $payments = $this->getEcpayConfig('payment_methods', true);

        if (empty($payments)) {
            return [];
        }

        $trimed = trim($payments);
        return explode(',', $trimed);
    }

    public function isValidPayment($choosenPayment)
    {
        $payments = $this->getValidPayments();
        return (in_array($choosenPayment, $payments));
    }

    public function isPaymentAvailable(CartInterface $quote = null)
    {
        $result = 0;
        $baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();

        if ($this->checkModuleSetting() === false) {
            return 5;
        }

        if (empty($this->getValidPayments())) {
            return 4;
        }

        if ($baseCurrencyCode !== 'TWD') {
            $result += 1;
        }

        if ($currentCurrencyCode !== 'TWD') {
            $result += 2;
        }

        return $result;
    }

    public function getEcpayConfig($id)
    {
        return $this->getMagentoConfig($this->prefix . $id);
    }

    public function getMagentoConfig($id)
    {
        return $this->getConfigData($id);
    }

    public function getHelper() {
        $merchant_id = $this->getEcpayConfig('merchant_id');
        $helper = new \ECPayPaymentHelper();
        $helper->setMerchantId($merchant_id);
        return $helper;
    }

    public function getModuleUrl($action = '')
    {
        if ($action !== '') {
            $route = $this->_code . '/payment/' . $action;
        } else {
            $route = '';
        }
        return $this->getMagentoUrl($route);
    }

    public function getMagentoUrl($route)
    {
        return $this->_urlInterface->getUrl($route);
    }

    public function checkModuleSetting()
    {
        $merchantId = $this->getEcpayConfig('merchant_id');
        $hashKey = $this->getEcpayConfig('hash_key');
        $hashIv = $this->getEcpayConfig('hash_iv');

        if (empty($merchantId) || empty($hashKey) || empty($hashIv)) {
            return false;
        }
        return true;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($this->getMagentoConfig("test_flag")) {
            return $this;
        }

        $additionalInfo = $payment->getAdditionalInformation();
        $rawDetailsInfo = $additionalInfo["raw_details_info"];

        if ($amount != $rawDetailsInfo["TradeAmt"]) {
            throw new LocalizedException(__($rawDetailsInfo["RtnMsg"]));
        }

        $tradeNo = $rawDetailsInfo["TradeNo"];
        $merchantId = $rawDetailsInfo["MerchantID"];
        $merchantTradeNo = $rawDetailsInfo["MerchantTradeNo"];

        $url = "https://payment.ecpay.com.tw/CreditDetail/DoAction";
        $params = $this->getCancellingCaptureParams($merchantId, $merchantTradeNo, $tradeNo, $amount);

        $checkMacValue = $this->ECPayInvoiceCheckMacValue->generate($params, $this->getEcpayConfig('hash_key'), $this->getEcpayConfig('hash_iv'));
        $params["CheckMacValue"] = $checkMacValue;

        $this->curl->post($url, $params);
        $result = $this->curl->getBody();

        $stringToArray = $this->ecpayResponse($result);

        if ($stringToArray["RtnCode"] != 1) {
            $this->_logger->critical(__($stringToArray["RtnMsg"]));
            throw new LocalizedException(__($stringToArray["RtnMsg"]));
        }

        $params = $this->getAbandoningTransactionParams($merchantId, $merchantTradeNo, $tradeNo, $amount);

        $checkMacValue = $this->ECPayInvoiceCheckMacValue->generate($params, $this->getEcpayConfig('hash_key'), $this->getEcpayConfig('hash_iv'));
        $params["CheckMacValue"] = $checkMacValue;

        $this->curl->post($url, $params);
        $result = $this->curl->getBody();

        $stringToArray = $this->ecpayResponse($result);

        if ($stringToArray["RtnCode"] != 1) {
            $this->_logger->critical(__($stringToArray["RtnMsg"]));
            throw new LocalizedException(__($stringToArray["RtnMsg"]));
        }

        $this->createRefundTransaction($payment, $tradeNo, $rawDetailsInfo);

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $tradeNo
     * @param $rawDetailsInfo
     */
    private function createRefundTransaction(\Magento\Payment\Model\InfoInterface $payment, $tradeNo, $rawDetailsInfo): void
    {
        $payment->setTransactionId($tradeNo . "_" . $rawDetailsInfo["process_date"])
            ->setParentTransactionId($tradeNo)
            ->setIsTransactionClosed(true);

        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($payment->getOrder())
            ->setTransactionId($tradeNo . "_" . $rawDetailsInfo["process_date"])
            ->setAdditionalInformation(
                [Transaction::RAW_DETAILS => (array)$rawDetailsInfo]
            )
            ->setFailSafe(true)
            ->build(Transaction::TYPE_REFUND);
        $transaction->save();
        $payment->save();
    }

    /**
     * @param string $result
     * @return array
     */
    private function ecpayResponse(string $result): array
    {
        $resultExplode = explode("&", $result);
        $stringToArray = [];

        foreach ($resultExplode as $key => $value) {
            $resultExplode = explode("=", $value);
            $stringToArray[$resultExplode[0]] = $resultExplode[1];
        }
        return $stringToArray;
    }

    /**
     * @param $merchantId
     * @param $merchantTradeNo
     * @param $tradeNo
     * @param float $amount
     * @return array
     */
    private function getRefundParams($merchantId, $merchantTradeNo, $tradeNo, float $amount): array
    {
        $params = [
            "MerchantID" => $merchantId,
            "MerchantTradeNo" => $merchantTradeNo,
            "TradeNo" => $tradeNo,
            "Action" => "R",
            "TotalAmount" => $amount
        ];
        return $params;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $paymentData
     * @return \Magento\Sales\Api\Data\TransactionInterface
     * @throws Exception
     */
    public function createTransaction(\Magento\Sales\Model\Order $order, $paymentData): \Magento\Sales\Api\Data\TransactionInterface
    {
        $payment = $order->getPayment();
        $originAdditionalInfo = $payment->getAdditionalInformation();
        $mergedData = array_merge($originAdditionalInfo, $paymentData);
        $payment->setLastTransId($paymentData["TradeNo"]);
        $payment->setTransactionId($paymentData["TradeNo"]);
        $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$mergedData]);

        // Formatted price
        $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

        // Prepare transaction
        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($paymentData['TradeNo'])
            ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$mergedData])
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        // Add transaction to payment
        $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
        $payment->setParentTransactionId(null);

        // Save payment, transaction and order
        $payment->save();
        $order->save();
        $transaction->save();
        return $transaction;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Api\Data\TransactionInterface $transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice(\Magento\Sales\Model\Order $order, \Magento\Sales\Api\Data\TransactionInterface $transaction): void
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();

            if ($invoice->canCapture()) {
                $invoice->capture();
            }

            $invoice->setTransactionId($transaction->getTransactionId());
            $invoice->save();

            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );

            $transactionSave->save();
            $order->save();
        }
    }

    public function createEInvoice($orderId, $storeId)
    {
        try
        {
            $sMsg = '';
            // 1.載入SDK程式
            $ecpay_invoice = $this->ecpayInvoice;

            // 2.寫入基本介接參數
            $this->initEInvoice($ecpay_invoice, $storeId);

            $order = $this->orderRepository->get($orderId);
            $payment = $order->getPayment();
            $additionalInfo = $payment->getAdditionalInformation();
            $rawDetailsInfo = $additionalInfo["raw_details_info"];
            $donationValue = $rawDetailsInfo["ecpay_einvoice_donation"];
            $donationCode = $this->getEInvoiceConfig("invoice/ecpay_invoice_love_code", $storeId);

            // 3.寫入發票相關資訊
            $aItems = array();
            // 商品資訊
            $this->initOrderItems($order, $ecpay_invoice);

            $RelateNumber = $this->initEInvoiceInfo($ecpay_invoice, $order, $donationValue, $donationCode);

            // 4.送出
            $aReturn_Info = $ecpay_invoice->Check_Out();

            // 5.返回
            $aReturn_Info["RelateNumber"] = $RelateNumber;
            $payment->setAdditionalData(json_encode($aReturn_Info));
            $payment->save();
            return [
                "RtnCode" => $aReturn_Info["RtnCode"],
                "RtnMsg" => $aReturn_Info["RtnMsg"]
            ];
        } catch (\Exception $e) {
            // 例外錯誤處理。
            $sMsg = $e->getMessage();
            throw new LocalizedException(__($sMsg));
        }
    }

    /**
     * @param \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice
     */
    private function initEInvoice(\Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice, $storeId): void
    {
        $ecpay_invoice->Invoice_Method = 'INVOICE';
        $ecpay_invoice->Invoice_Url = $this->getInvoiceApiUrl($storeId) . 'Issue';
        $ecpay_invoice->MerchantID = $this->getEInvoiceConfig("merchant_id", $storeId);
        $ecpay_invoice->HashKey = $this->getEInvoiceConfig("invoice/ecpay_invoice_hash_key", $storeId);
        $ecpay_invoice->HashIV = $this->getEInvoiceConfig("invoice/ecpay_invoice_hash_iv", $storeId);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice
     */
    private function initOrderItems(\Magento\Sales\Api\Data\OrderInterface $order, \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice): void
    {
        $orderItems = $order->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            array_push(
                $ecpay_invoice->Send['Items'],
                array(
                    'ItemName' => __($orderItem->getData('name')),
                    'ItemCount' => (int)$orderItem->getData('qty_ordered'),
                    'ItemWord' => '批',
                    'ItemPrice' => $orderItem->getData('price'),
                    'ItemTaxType' => 1,
                    'ItemAmount' => $orderItem->getData('price'),
                    'ItemRemark' => $orderItem->getData('sku')
                )
            );
        }
    }

    /**
     * @param \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $donationValue
     * @param $donationCode
     * @return string
     */
    private function initEInvoiceInfo(\Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice, \Magento\Sales\Api\Data\OrderInterface $order, string $donationValue, $donationCode): string
    {
        $dataTime = $this->dateTimeFactory->create();
        $RelateNumber = 'ECPAY' . $dataTime->date('YmdHis') . rand(1000000000, 2147483647); // 產生測試用自訂訂單編號
        $ecpay_invoice->Send['RelateNumber'] = $RelateNumber;
        $ecpay_invoice->Send['CustomerID'] = $order->getCustomerId();
        $ecpay_invoice->Send['CustomerIdentifier'] = '';
        $ecpay_invoice->Send['CustomerName'] = $order->getCustomerFirstname() . $order->getCustomerLastname();
        $ecpay_invoice->Send['CustomerAddr'] = $order->getBillingAddress()->getCity();
        $ecpay_invoice->Send['CustomerPhone'] = '';
        $ecpay_invoice->Send['CustomerEmail'] = $order->getCustomerEmail();
        $ecpay_invoice->Send['ClearanceMark'] = '';
        $ecpay_invoice->Send['Print'] = '0';
        $ecpay_invoice->Send['Donation'] = ($donationValue == "true") ? 1 : 0;
        $ecpay_invoice->Send['LoveCode'] = ($donationValue == "true") ? $donationCode : '';
        $ecpay_invoice->Send['CarruerType'] = '';
        $ecpay_invoice->Send['CarruerNum'] = '';
        $ecpay_invoice->Send['TaxType'] = 1;
        $ecpay_invoice->Send['SalesAmount'] = intval($order->getGrandTotal()) - intval($order->getShippingAmount());
        $ecpay_invoice->Send['InvoiceRemark'] = 'v1.0.190822';
        $ecpay_invoice->Send['InvType'] = '07';
        $ecpay_invoice->Send['vat'] = '';
        return $RelateNumber;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function invalidateEInvoice(\Magento\Payment\Model\InfoInterface $payment, $storeId): void
    {
        try {
            $sMsg = '';

            // 1.載入SDK
            $ecpay_invoice = $this->ecpayInvoice;

            // 2.寫入基本介接參數
            $ecpay_invoice->Invoice_Method = 'INVOICE_VOID';
            $ecpay_invoice->Invoice_Url = $this->getInvoiceApiUrl($storeId) . 'IssueInvalid';
            $ecpay_invoice->MerchantID = $this->getEcpayConfig("merchant_id");
            $ecpay_invoice->HashKey = $this->getEcpayConfig("invoice/ecpay_invoice_hash_key");
            $ecpay_invoice->HashIV = $this->getEcpayConfig("invoice/ecpay_invoice_hash_iv");

            // 3.寫入發票相關資訊
            $additionalData = $payment->getAdditionalData();
            $invalidateInvoiceData = json_decode($additionalData, true);
            $ecpay_invoice->Send['InvoiceNumber'] = $invalidateInvoiceData["InvoiceNumber"];
            $ecpay_invoice->Send['Reason'] = 'ISSUE INVALID';

            // 4.送出
            $aReturn_Info = $ecpay_invoice->Check_Out();

            // 5.返回
            $payment->setData("ecpay_invoice_invalidate_data", json_encode($aReturn_Info));
        } catch (Exception $e) {
            // 例外錯誤處理。
            $sMsg = $e->getMessage();
            throw new LocalizedException(__($sMsg));
        }
    }

    public function getInvoiceApiUrl($storeId)
    {
        $apiUrl = "";
        if ($this->getEInvoiceConfig("invoice/ecpay_invoice_test_flag", $storeId)) {
            $apiUrl = $this->getEInvoiceConfig("invoice/ecpay_invoice_stage_url", $storeId);
        } else {
            $apiUrl = $this->getEInvoiceConfig("invoice/ecpay_invoice_production_url", $storeId);
        }

        return $apiUrl;
    }

    /**
     * @param $merchantId
     * @param $merchantTradeNo
     * @param $tradeNo
     * @param float $amount
     * @return array
     */
    private function getCancellingCaptureParams($merchantId, $merchantTradeNo, $tradeNo, float $amount): array
    {
        $params = [
            "MerchantID" => $merchantId,
            "MerchantTradeNo" => $merchantTradeNo,
            "TradeNo" => $tradeNo,
            "Action" => "E",
            "TotalAmount" => $amount
        ];
        return $params;
    }

    /**
     * @param $merchantId
     * @param $merchantTradeNo
     * @param $tradeNo
     * @param float $amount
     * @return array
     */
    private function getAbandoningTransactionParams($merchantId, $merchantTradeNo, $tradeNo, float $amount): array
    {
        $params = [
            "MerchantID" => $merchantId,
            "MerchantTradeNo" => $merchantTradeNo,
            "TradeNo" => $tradeNo,
            "Action" => "N",
            "TotalAmount" => $amount
        ];
        return $params;
    }

    public function getEInvoiceConfig($id, $storeId)
    {
        $prefix = "payment/ecpay_ecpaypayment/ecpay_";
        $path = $prefix . $id;
        return $this->_scopeConfig->getValue($path, 'store', $storeId);
    }
}