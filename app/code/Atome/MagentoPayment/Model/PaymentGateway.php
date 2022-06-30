<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model;

use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Atome\MagentoPayment\Model\Config\LocaleConfig;
use Atome\MagentoPayment\Model\Adapter\PaymentApi;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\ResourceModel\Quote\Payment as PaymentQuoteRepository;
use Magento\Sales\Model\Order;

class PaymentGateway extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'atome_payment_gateway';

    const PAYMENT_REFERENCE_ID = 'atome_payment_reference_id';
    const PAYMENT_AMOUNT_FORMATTED = 'atome_payment_amount_formatted';
    const PAYMENT_CURRENCY_CODE = 'atome_payment_currency_code';
    const PAYMENT_DEBUG_SECRET = 'atome_debug_secret';
    const MERCHANT_REFERENCE_ID = 'merchant_reference_id';

    protected $_code = self::METHOD_CODE;
    protected $_isGateway = true;
    protected $_isInitializeNeeded = false;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseInternal = false;
    protected $_canFetchTransactionInfo = false;
    protected $_infoBlockType = 'Atome\MagentoPayment\Block\PaymentDisplayInfoBlock';

    protected $checkoutSession;

    protected $paymentGatewayConfig;
    protected $localeConfig;
    protected $paymentApi;
    protected $commonHelper;

    protected $transactionRepository;
    protected $transactionBuilder;
    protected $messageManager;
    protected $paymentQuoteRepository;

    /**
     * @var Order
     */
    private $order;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,

        \Magento\Checkout\Model\Session $checkoutSession,
        PaymentGatewayConfig $paymentGatewayConfig,
        LocaleConfig $localeConfig,
        PaymentApi $paymentApi,
        CommonHelper $commonHelper,
        PaymentQuoteRepository $paymentQuoteRepository,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Order $order,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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

        $this->checkoutSession = $checkoutSession;

        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->localeConfig = $localeConfig;
        $this->paymentApi = $paymentApi;
        $this->commonHelper = $commonHelper;

        $this->transactionRepository = $transactionRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->messageManager = $messageManager;

        $this->order = $order;

        $this->paymentQuoteRepository = $paymentQuoteRepository;

        if($this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
            $this->_isInitializeNeeded = true;
        }
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initialize($paymentAction, $stateObject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->getInfoInstance();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment instanceof \Magento\Sales\Model\Order\Payment) {
            throw new LocalizedException(__('unknown class to refund: ' . get_class($payment)));
        }

        if ($this->paymentGatewayConfig->getCountry() === 'tw' && round($amount) != $amount) {
            throw new LocalizedException(__('The refund amount must be integer'));;
        }

        $this->paymentGatewayConfig->setStoreId($payment->getOrder()->getStoreId());
        // check current status of payment, if it is "REFUNDED", then return directly
        $referenceId = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
        $paymentInfoResponse = $this->paymentApi->getPaymentInfo($referenceId);
        if ($paymentInfoResponse->getStatus() === PaymentApi::PAYMENT_STATUS_REFUNDED) {
            $this->commonHelper->logInfo("payment {$referenceId} has been refunded, return");
            return $this;
        }
        $this->commonHelper->logInfo("the amount from magento refund function: " . $amount);
        $amount = $payment->getCreditmemo()->getGrandTotal();
        $this->commonHelper->logInfo("the amount from credit memo: " . $amount);
        $this->paymentApi->refundPayment($payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID), $amount);
        return $this;
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!$this->getConfigData('merchant_api_key') || !$this->getConfigData('merchant_api_secret')) {
            return false;
        }

        $maxSpend = $this->paymentGatewayConfig->getMaxSpend();
        if ($maxSpend && $quote->getGrandTotal() > $maxSpend) {
            return false;
        }

        $excludedCategories = $this->getConfigData('exclude_category');
        if ($excludedCategories) {
            $quote = $this->checkoutSession->getQuote();
            $excludedCategoriesArray = explode(",", $excludedCategories);

            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $categoryIds = $product->getCategoryIds();
                foreach ($categoryIds as $k) {
                    if (in_array($k, $excludedCategoriesArray)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function canUseForCurrency($currencyCode)
    {
        return in_array($currencyCode, $this->localeConfig->getSupportedCurrencyCodes());
    }


    public function canUseForCurrencyAmount($currencyCode, $amount)
    {
        $min = $this->localeConfig->getMinimumSpend(0);
        $max = $this->localeConfig->getMaximumSpend(null);
        $maxSpend = $this->paymentGatewayConfig->getMaxSpend();
        return $this->canUseForCurrency($currencyCode)
            && (!$min || $amount >= $min)
            && (!$max || $amount <= $max)
            && (!$maxSpend || $amount <= $maxSpend);
    }

    /**
     * @param \Magento\Catalog\Model\Product[] $products
     * @return bool
     */
    public function canUseForProducts($products)
    {
        $excludedCategoriesString = $this->paymentGatewayConfig->getExcludedCategories();
        $excludedCategoriesArray = explode(",", $excludedCategoriesString);
        foreach ($products as $product) {
            $categoryIds = $product->getCategoryIds();
            foreach ($categoryIds as $k) {
                if (in_array($k, $excludedCategoriesArray)) {
                    return false;
                }
            }
        }
        return true;
    }
}
