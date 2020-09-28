<?php

namespace Ecpay\Ecpaypayment\Model;

use Ecpay\Ecpaypayment\Exception\EcpayException;
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
    /**
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    private $productLinkManagement;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    private $invoiceSender;

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
        \Magento\Bundle\Api\ProductLinkManagementInterface $productLinkManagement,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
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
        $this->productLinkManagement = $productLinkManagement;
        $this->productRepository = $productRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->invoiceSender = $invoiceSender;
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

        $url = "https://payment.ecPay.com.tw/CreditDetail/QueryTrade/V2";
        $params = [
            "MerchantID" => $merchantId,
            "CreditRefundId" => $rawDetailsInfo["gwsr"],
            "CreditAmount" => $rawDetailsInfo["amount"],
            "CreditCheckCode" => 88975791
        ];

        $checkMacValue = $this->ECPayInvoiceCheckMacValue->generate(
            $params,
            $this->getEcpayConfigFromStore('hash_key', $payment->getOrder()->getStoreId()),
            $this->getEcpayConfigFromStore('hash_iv', $payment->getOrder()->getStoreId())
        );
        $params["CheckMacValue"] = $checkMacValue;

        $this->_logger->info('ecpay-payment | params for ecpay search transaction', $params);
        $this->_logger->info('ecpay-payment | HashKey for ecpay search transaction', [$this->getEcpayConfigFromStore('hash_key', $payment->getOrder()->getStoreId())]);
        $this->_logger->info('ecpay-payment | HashIV for ecpay search transaction', [$this->getEcpayConfigFromStore('hash_iv', $payment->getOrder()->getStoreId())]);

        $this->curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->curl->post($url, $params);
        $result = $this->curl->getBody();

        $this->_logger->info('ecpay-payment | result for ecpay search transaction', [$result]);

        $resultArray = json_decode($result, true);
        if (count($resultArray) > 0 || !empty($resultArray['RtnValue'])) {
            if (array_key_exists('status', $resultArray['RtnValue'])) {
                $transactionStatus = $resultArray["RtnValue"]["status"];
            } else {
                $this->_logger->critical(__('Status does not exist in Credit Card transaction search result.'));
                throw new EcpayException(__('Status does not exist in Credit Card transaction search result.'));
            }
        } else {
            $this->_logger->critical(__('ecpay search transaction result is null.'));
            throw new EcpayException(__('ecpay search transaction result is null.'));
        }

        if (trim($transactionStatus) == '要關帳') {
            $actionEResult = $this->sendRefundRequest($merchantId, $merchantTradeNo, $tradeNo, $amount, $payment, "E");
            $this->_logger->info("E action result : ", $actionEResult);
            $actionNResult = $this->sendRefundRequest($merchantId, $merchantTradeNo, $tradeNo, $amount, $payment, "N");
            $this->_logger->info("N action result : ", $actionNResult);
            if ($actionNResult["RtnCode"] != 1) {
                $this->_logger->critical(__("Ecpay EN action Error Response : " . $actionNResult["RtnMsg"]));
                $actionRResult = $this->sendRefundRequest($merchantId, $merchantTradeNo, $tradeNo, $amount, $payment, "R");
                $this->_logger->info("R action result : ", $actionRResult);
            }
        } elseif (trim($transactionStatus) == '已關帳') {
            $actionRResult = $this->sendRefundRequest($merchantId, $merchantTradeNo, $tradeNo, $amount, $payment, "R");
            $this->_logger->info("R action result : ", $actionRResult);
            if ($actionRResult["RtnCode"] != 1) {
                $this->_logger->critical(__("Ecpay R action Error Response : " . $actionRResult["RtnMsg"]));
            }
        } elseif (trim($transactionStatus) == '已授權') {
            $actionNResult = $this->sendRefundRequest($merchantId, $merchantTradeNo, $tradeNo, $amount, $payment, "N");
            $this->_logger->info("N action result : ", $actionNResult);
            if ($actionNResult["RtnCode"] != 1) {
                $this->_logger->critical(__("Ecpay N action Error Response : " . $actionNResult["RtnMsg"]));
                $actionRResult = $this->sendRefundRequest($merchantId, $merchantTradeNo, $tradeNo, $amount, $payment, "R");
                $this->_logger->info("R action result : ", $actionRResult);
            }
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
     * @param $amount
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $action
     * @return array
     */
    public function sendRefundRequest($merchantId, $merchantTradeNo, $tradeNo, $amount, \Magento\Payment\Model\InfoInterface $payment, $action = "N")
    {
        $url = "https://payment.ecpay.com.tw/CreditDetail/DoAction";
        $params = [
            "MerchantID" => $merchantId,
            "MerchantTradeNo" => $merchantTradeNo,
            "TradeNo" => $tradeNo,
            "Action" => $action,
            "TotalAmount" => $amount
        ];

        $checkMacValue = $this->ECPayInvoiceCheckMacValue->generate(
            $params,
            $this->getEcpayConfigFromStore('hash_key', $payment->getOrder()->getStoreId()),
            $this->getEcpayConfigFromStore('hash_iv', $payment->getOrder()->getStoreId())
        );
        $params["CheckMacValue"] = $checkMacValue;

        $this->_logger->info('ECPAY | REFUND PARAM ACTION : ' . $action, $params);
        $this->_logger->info('ECPAY | REFUND HASHKEY ACTION : ' . $action, [$this->getEcpayConfigFromStore('hash_key', $payment->getOrder()->getStoreId())]);
        $this->_logger->info('ECPAY | REFUND HASHVI ACTION : ' . $action, [$this->getEcpayConfigFromStore('hash_iv', $payment->getOrder()->getStoreId())]);

        $this->curl->post($url, $params);
        $result = $this->curl->getBody();

        $this->_logger->info('ECPAY | RESULT ACTION : ' .$action, [$result]);

        $stringToArray = $this->ecpayResponse($result);

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
            $this->invoiceSender->send($invoice);
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
            $addtionalData = json_decode($payment->getAdditionalData(), true);

            if (isset($addtionalData["RtnCode"]) && $addtionalData["RtnCode"] == 1) {
                return [
                    "RtnCode" => $addtionalData["RtnCode"],
                    "RtnMsg" => $addtionalData["RtnMsg"]
                ];
            } else {
                // 3.寫入發票相關資訊
                $aItems = array();
                // 商品資訊
                $this->initOrderItems($order, $ecpay_invoice);

                $RelateNumber = $this->initEInvoiceInfo($ecpay_invoice, $order, $rawDetailsInfo);

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
            }
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
        $ecpay_invoice->MerchantID = $this->getEcpayConfigFromStore("merchant_id", $storeId);
        $ecpay_invoice->HashKey = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_key", $storeId);
        $ecpay_invoice->HashIV = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_iv", $storeId);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice
     */
    private function initOrderItems(\Magento\Sales\Model\Order $order, \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice): void
    {
        $ecpay_invoice->Send['Items'] = [];

        $orderItems = $order->getAllVisibleItems();
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $mileageUsedAmount = $order->getRewardPointsBalance();

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $mileagePerItem = $this->mileageSpentRateByItem(
                $orderTotal,
                $orderItem->getRowTotalInclTax(),
                $orderItem->getDiscountAmount(),
                $mileageUsedAmount
            );
            $itemGrandTotalInclTax = $orderItem->getRowTotalInclTax()
                - $orderItem->getDiscountAmount()
                - $mileagePerItem;

            if ($orderItem->getOriginalPrice() == 0) {
                continue;
            }

            array_push(
                $ecpay_invoice->Send['Items'],
                array(
                    'ItemName' => $orderItem->getData('name'),
                    'ItemCount' => (int)$orderItem->getData('qty_ordered'),
                    'ItemWord' => '批',
                    'ItemPrice' => $itemGrandTotalInclTax / (int)$orderItem->getData('qty_ordered'),
                    'ItemTaxType' => 1,
                    'ItemAmount' => $itemGrandTotalInclTax,
                    'ItemRemark' => '商品備註'
                )
            );
        }

        if ($order->getShippingAmount() > 0) {
            array_push(
                $ecpay_invoice->Send['Items'],
                array(
                    'ItemName' => $order->getShippingDescription(),
                    'ItemCount' => 1,
                    'ItemWord' => '批',
                    'ItemPrice' => $order->getShippingAmount(),
                    'ItemTaxType' => 1,
                    'ItemAmount' => $order->getShippingAmount(),
                    'ItemRemark' => '商品備註'
                )
            );
        }
    }

    public function getProportionOfBundleChild($bundleAmount, $childAmount, $valueToCalculate)
    {
        $rate = ($childAmount / $bundleAmount);

        return $valueToCalculate * $rate;
    }

    public function getBundleChildFromOrder($itemId, $bundleChildSku)
    {
        $bundleChild = null;
        /** @var \Magento\Sales\Model\Order\Item $itemOrdered */
        $itemOrdered = $this->orderItemRepository->get($itemId);
        $childrenItems = $itemOrdered->getChildrenItems();
        /** @var \Magento\Sales\Model\Order\Item $childItem */
        foreach ($childrenItems as $childItem) {
            if ($childItem->getSku() == $bundleChildSku) {
                $bundleChild = $childItem;
                break;
            }
        }
        return $bundleChild;
    }

    public function getBundleChildren($bundleDynamicSku)
    {
        $bundleSku = explode("-", $bundleDynamicSku);
        try {
            return $this->productLinkManagement->getChildren($bundleSku[0]);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param $rawDetailsInfo
     * @return string
     */
    private function initEInvoiceInfo($ecpay_invoice, $order, $rawDetailsInfo): string
    {
        $dataTime = $this->dateTimeFactory->create();

        $triplicateTaxId = $rawDetailsInfo["ecpay_einvoice_tax_id_number"];
        $cellphoneBarcode = $rawDetailsInfo["ecpay_einvoice_cellphone_barcode"];
        $eInvoiceType = $rawDetailsInfo["ecpay_einvoice_type"];
        $carruerType = $this->getCarruerType($eInvoiceType);

        $donationCode = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_love_code", $order->getStoreId());
        $customerName = $order->getCustomerLastname() . $order->getCustomerFirstname();

        $donationValue = '';
        $carruerNum = '';
        switch ($carruerType) {
            case '':
                if (empty($triplicateTaxId)) {
                    $donationValue = "true";
                } else {
                    $donationValue = "false";
                    $customerName = $rawDetailsInfo['ecpay_einvoice_triplicate_title'];
                }
                $carruerNum = '';
                break;
            case '1':
                $donationValue = "false";
                $carruerNum = '';
                break;
            case '3':
                $donationValue = "false";
                $carruerNum = $cellphoneBarcode;
                break;
        }

        $RelateNumber = $order->getIncrementId();
        $ecpay_invoice->Send['RelateNumber'] = $RelateNumber;
        $ecpay_invoice->Send['CustomerID'] = $order->getCustomerId();
        $ecpay_invoice->Send['CustomerIdentifier'] = $triplicateTaxId;
        $ecpay_invoice->Send['CustomerName'] = $customerName;
        $ecpay_invoice->Send['CustomerAddr'] = $order->getBillingAddress()->getCity();
        $ecpay_invoice->Send['CustomerPhone'] = '';
        $ecpay_invoice->Send['CustomerEmail'] = $order->getCustomerEmail();
        $ecpay_invoice->Send['ClearanceMark'] = '';
        $ecpay_invoice->Send['Print'] = (!empty($triplicateTaxId)) ? '1' : '0';
        $ecpay_invoice->Send['Donation'] = ($donationValue == "true") ? '1' : '0';
        $ecpay_invoice->Send['LoveCode'] = ($donationValue == "true") ? $donationCode : '';
        $ecpay_invoice->Send['CarruerType'] = $carruerType;
        $ecpay_invoice->Send['CarruerNum'] = $carruerNum;
        $ecpay_invoice->Send['TaxType'] = 1;
        $ecpay_invoice->Send['SalesAmount'] = round($order->getGrandTotal());
        $ecpay_invoice->Send['InvoiceRemark'] = 'v1.0.190822';
        $ecpay_invoice->Send['InvType'] = '07';
        $ecpay_invoice->Send['vat'] = '';
        return $RelateNumber;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $storeId
     */
    public function invalidateEInvoice(\Magento\Payment\Model\InfoInterface $payment, $storeId)
    {
        try {
            $sMsg = '';

            // 1.載入SDK
            $ecpay_invoice = $this->ecpayInvoice;

            // 2.寫入基本介接參數
            $ecpay_invoice->Invoice_Method = 'INVOICE_VOID';
            $ecpay_invoice->Invoice_Url = $this->getInvoiceApiUrl($storeId) . 'IssueInvalid';
            $ecpay_invoice->MerchantID = $this->getEcpayConfigFromStore("merchant_id", $storeId);
            $ecpay_invoice->HashKey = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_key", $storeId);
            $ecpay_invoice->HashIV = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_iv", $storeId);

            // 3.寫入發票相關資訊
            $additionalData = $payment->getAdditionalData();
            $invalidateInvoiceData = json_decode($additionalData, true);
            $ecpay_invoice->Send['InvoiceNumber'] = $invalidateInvoiceData["InvoiceNumber"];
            $ecpay_invoice->Send['Reason'] = 'ISSUE INVALID';

            // 4.送出
            $aReturn_Info = $ecpay_invoice->Check_Out();

            // 5.返回
            $payment->setData("ecpay_invoice_invalidate_data", json_encode($aReturn_Info));
        } catch (\Exception $e) {
            // 例外錯誤處理。
            $sMsg = $e->getMessage();
            $this->_logger->info("EInvoice Cancel has been Failed : " . $sMsg);
//            throw new LocalizedException(__($sMsg));
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param \Magento\Sales\Model\Order $order
     */
    public function issueAllowance(\Magento\Sales\Model\Order\Payment $payment, $order)
    {
        try {
            // 1.載入SDK
            $ecpay_invoice = $this->ecpayInvoice;

            $storeId = $order->getStoreId();

            // 2.寫入基本介接參數
            $ecpay_invoice->Invoice_Method = 'ALLOWANCE';
            $ecpay_invoice->Invoice_Url = $this->getInvoiceApiUrl($storeId) . 'Allowance';
            $ecpay_invoice->MerchantID = $this->getEcpayConfigFromStore("merchant_id", $storeId);
            $ecpay_invoice->HashKey = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_key", $storeId);
            $ecpay_invoice->HashIV = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_iv", $storeId);

            // 3.寫入發票相關資訊
            $additionalData = $payment->getAdditionalData();
            $invalidateInvoiceData = json_decode($additionalData, true);
            $ecpay_invoice->Send['InvoiceNo'] = $invalidateInvoiceData["InvoiceNumber"];
            $ecpay_invoice->Send['AllowanceNotify'] = $this->getEcpayConfigFromStore('invoice/issue_allowance', $storeId);
            $ecpay_invoice->Send['CustomerName'] = $order->getCustomerLastname() . $order->getCustomerFirstname();
            $ecpay_invoice->Send['NotifyMail'] = $order->getShippingAddress()->getEmail();
            $ecpay_invoice->Send['NotifyPhone'] = $order->getShippingAddress()->getTelephone();
            $this->getOrderItems($order, $ecpay_invoice);


            // 4.送出
            $aReturn_Info = $ecpay_invoice->Check_Out();

            // 5.返回
            $payment->setData("ecpay_invoice_invalidate_data", json_encode($aReturn_Info));
        } catch (\Exception $e) {
            // 例外錯誤處理。
            $sMsg = $e->getMessage();
            $this->_logger->info("EInvoice Cancel has been Failed : " . $sMsg);
//            throw new LocalizedException(__($sMsg));
        }
    }

    public function getOrderItems(\Magento\Sales\Model\Order $order, \Ecpay\Ecpaypayment\Helper\Library\EcpayInvoice $ecpay_invoice)
    {
        $ecpay_invoice->Send['AllowanceAmount'] = round($order->getTotalRefunded());
        $ecpay_invoice->Send['Items'] = [];

        $orderItems = $order->getAllVisibleItems();
        $orderTotal = round($order->getSubtotalInclTax() + $order->getDiscountAmount() + $order->getShippingAmount());
        $mileageUsedAmount = $order->getRewardPointsBalance();

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $mileagePerItem = $this->mileageSpentRateByItem(
                $orderTotal,
                $orderItem->getRowTotalInclTax(),
                $orderItem->getDiscountAmount(),
                $mileageUsedAmount
            );
            $itemGrandTotalInclTax = $orderItem->getRowTotalInclTax()
                - $orderItem->getDiscountAmount()
                - $mileagePerItem;

            if ($orderItem->getOriginalPrice() == 0) {
                continue;
            }

            array_push(
                $ecpay_invoice->Send['Items'],
                array(
                    'ItemName' => $orderItem->getData('name'),
                    'ItemCount' => (int)$orderItem->getData('qty_ordered'),
                    'ItemWord' => '批',
                    'ItemPrice' => $itemGrandTotalInclTax / (int)$orderItem->getData('qty_ordered'),
                    'ItemTaxType' => 1,
                    'ItemAmount' => $itemGrandTotalInclTax,
                    'ItemRemark' => '商品備註'
                )
            );
        }

        if (!empty($order->getShippingRefunded())) {
            array_push(
                $ecpay_invoice->Send['Items'],
                array(
                    'ItemName' => $order->getShippingDescription(),
                    'ItemCount' => 1,
                    'ItemWord' => '批',
                    'ItemPrice' => $order->getShippingRefunded(),
                    'ItemTaxType' => 1,
                    'ItemAmount' => $order->getShippingRefunded(),
                    'ItemRemark' => '商品備註'
                )
            );
        }
    }

    public function getInvoiceApiUrl($storeId)
    {
        $apiUrl = "";
        if ($this->getEcpayConfigFromStore("invoice/ecpay_invoice_test_flag", $storeId)) {
            $apiUrl = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_stage_url", $storeId);
        } else {
            $apiUrl = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_production_url", $storeId);
        }

        return $apiUrl;
    }

    public function getEcpayConfigFromStore($id, $storeId)
    {
        $prefix = "payment/ecpay_ecpaypayment/ecpay_";
        $path = $prefix . $id;
        return $this->_scopeConfig->getValue($path, 'store', $storeId);
    }

    /**
     * @param $orderItem
     * @return mixed
     */
    private function configurableProductCheck($orderItem)
    {
        if (empty($orderItem->getParentItem())) {
            return $orderItem;
        } else {
            return $orderItem->getParentItem();
        }
    }

    public function mileageSpentRateByItem($orderTotal, $itemRowTotal, $itemDiscountAmount, $mileageUsed)
    {
        $itemTotal = round($itemRowTotal - $itemDiscountAmount, 2);

        if ($mileageUsed) {
            return round(($itemTotal/$orderTotal) * $mileageUsed);
        }
        return is_null($mileageUsed) ? '0' : $mileageUsed;
    }

    /**
     * @param string $eInvoiceType
     * @return string
     */
    private function getCarruerType(string $eInvoiceType)
    {
        $carruerType = '';

        switch ($eInvoiceType) {
            case 'greenworld-invoice':
                $carruerType = '1';
                break;
            case 'cellphone-barcode-invoice':
                $carruerType = '3';
                break;
            case 'triplicate-invoice':
            case 'donation-invoice':
                break;
        }

        return $carruerType;
    }

    public function checkMobileBarCode($eInvoiceData, $storeId)
    {
        try
        {
            $sMsg = '' ;
            // 1.載入SDK程式
            $ecpay_invoice = $this->ecpayInvoice;
            // 2.寫入基本介接參數
            $ecpay_invoice->Invoice_Method = 'CHECK_MOBILE_BARCODE'; 					// 請見16.1操作發票功能類別
            $ecpay_invoice->Invoice_Url = $this->getCheckMobileBarCodeApiUrl();
            $ecpay_invoice->MerchantID = $this->getEcpayConfigFromStore("merchant_id", $storeId);
            $ecpay_invoice->HashKey = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_key", $storeId);
            $ecpay_invoice->HashIV = $this->getEcpayConfigFromStore("invoice/ecpay_invoice_hash_iv", $storeId);
            // 3.寫入發票傳送資訊
            $ecpay_invoice->Send['BarCode'] = $eInvoiceData["barCodeValue"]; 							// 手機條碼
            // 4.送出
            $aReturn_Info = $ecpay_invoice->Check_Out();

            // 5.返回
            return [
                "IsExist" => $aReturn_Info["IsExist"],
                "RtnCode" => $aReturn_Info["RtnCode"],
                "RtnMsg" => $aReturn_Info["RtnMsg"]
            ];
        } catch (\Exception $e) {
            // 例外錯誤處理。
            $sMsg = $e->getMessage();
            return [
                "RtnMsg" => $sMsg
            ];
        }
    }

    public function getCheckMobileBarCodeApiUrl()
    {
        if ($this->getMagentoConfig("test_flag")) {
            return "https://einvoice-stage.ecpay.com.tw/Query/CheckMobileBarCode";
        } else {
            return "https://einvoice.ecpay.com.tw/Query/CheckMobileBarCode";
        }
    }
}
