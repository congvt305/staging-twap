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
        $this->invalidateEInvoice($payment);

        return $this;
        throw new LocalizedException(__("TEST"));

        $additionalInfo = $payment->getAdditionalInformation();
        $rawDetailsInfo = $additionalInfo["raw_details_info"];

        if ($amount != $rawDetailsInfo["amount"]) {
            throw new LocalizedException(__($rawDetailsInfo["RtnMsg"]));
        }

        $tradeNo = $rawDetailsInfo["TradeNo"];
        $merchantId = $rawDetailsInfo["MerchantID"];
        $merchantTradeNo = $rawDetailsInfo["MerchantTradeNo"];
        $checkMacValue = $rawDetailsInfo["CheckMacValue"];

        $url = "https://payment.ecpay.com.tw/CreditDetail/DoAction";
        $params = $this->getRefundParams($merchantId, $merchantTradeNo, $tradeNo, $amount, $checkMacValue);

        $this->curl->post($url, $params);
        $result = $this->curl->getBody();

        $stringToArray = $this->ecpayResponse($result);

        if ($stringToArray["RtnCode"] !== 1) {
            $this->_logger->critical(__($stringToArray["RtnMsg"]));
            throw new LocalizedException(__($stringToArray["RtnMsg"]));
        }

        $this->createTransaction($payment, $tradeNo, $rawDetailsInfo);

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $tradeNo
     * @param $rawDetailsInfo
     */
    private function createTransaction(\Magento\Payment\Model\InfoInterface $payment, $tradeNo, $rawDetailsInfo): void
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
     * @param $checkMacValue
     * @return array
     */
    private function getRefundParams($merchantId, $merchantTradeNo, $tradeNo, float $amount, $checkMacValue): array
    {
        $params = [
            "MerchantID" => $merchantId,
            "MerchantTradeNo" => $merchantTradeNo,
            "TradeNo" => $tradeNo,
            "Action" => "R",
            "TotalAmount" => $amount,
            "CheckMacValue" => $checkMacValue
        ];
        return $params;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function invalidateEInvoice(\Magento\Payment\Model\InfoInterface $payment): void
    {
        try {
            $sMsg = '';

            // 1.載入SDK
            include_once($this->directoryList->getPath("app") . '/code/Ecpay/Ecpaypayment/Helper/Library/Ecpay_Invoice.php');
            $ecpay_invoice = new \EcpayInvoice();

            // 2.寫入基本介接參數
            $ecpay_invoice->Invoice_Method = 'INVOICE_VOID';
            $ecpay_invoice->Invoice_Url = 'https://einvoice-stage.ecpay.com.tw/Invoice/IssueInvalid';
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
            $order = $payment->getOrder();
            $invoiceCollection = $order->getInvoiceCollection();

            foreach ($invoiceCollection as $invoice) {
                $payment->setData("ecpay_invoice_invalidate_data", json_encode($aReturn_Info));
            }
        } catch (Exception $e) {
            // 例外錯誤處理。
            $sMsg = $e->getMessage();
            throw new LocalizedException(__($sMsg));
        }
    }
}