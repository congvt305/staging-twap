<?php

/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */

namespace Atome\MagentoPayment\Controller\Payment;

use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Helper\PaymentHelper;
use Atome\MagentoPayment\Model\Adapter\PaymentApi;
use Atome\MagentoPayment\Model\PaymentGateway;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Model\QuoteValidator;
use Magento\Quote\Model\ResourceModel\Quote as QuoteRepository;
use Magento\Sales\Model\ResourceModel\Order as OrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;

class Prepare extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $customerSession;
    protected $orderFactory;
    protected $quoteFactory;
    protected $paymentGatewayConfig;
    protected $commonHelper;
    protected $paymentHelper;
    protected $quoteRepository;
    protected $orderRepository;
    protected $jsonFactory;
    protected $quoteValidator;
    protected $paymentApi;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CheckoutSession $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        PaymentGatewayConfig $paymentGatewayConfig,
        PaymentApi $paymentApi,
        CommonHelper $commonHelper,
        PaymentHelper $paymentHelper,
        QuoteRepository $quoteRepository,
        OrderRepository $orderRepository,
        JsonFactory $jsonFactory,
        QuoteValidator $quoteValidator,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->commonHelper = $commonHelper;
        $this->paymentHelper = $paymentHelper;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->jsonFactory = $jsonFactory;
        $this->quoteValidator = $quoteValidator;
        $this->paymentApi = $paymentApi;
        $this->storeManager = $storeManager;
    }

    protected function responseError($message)
    {
        return $this->jsonFactory->create()->setData(['error' => true, 'message' => $message]);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function executePrepare()
    {
        if ($this->paymentGatewayConfig->getPaymentAction() != AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {
            return $this->responseError('invalid payment action');
        }

        $quote = $this->checkoutSession->getQuote();
        $quote->reserveOrderId();

        $queryParams = $this->getRequest()->getParams();
        if(!empty($queryParams['billingAddress'])) {
            $billingAddressData = json_decode($queryParams['billingAddress'], true);
        }

        if (!empty($billingAddressData) && is_array($billingAddressData)) {
            foreach ($billingAddressData as $key => $value) {
                $newKey = SimpleDataObjectConverter::camelCaseToSnakeCase($key);
                $billingAddressData[$newKey] = $value;
            }
            $billingAddress = $this->_objectManager->create(\Magento\Quote\Model\Quote\Address::class, ['data' => $billingAddressData]);
            $billingAddress->setAddressType('billing');
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
        }

        if (!$this->customerSession->isLoggedIn()) {
            $quote->setCustomerId(null)
                ->setCustomerEmail($this->getRequest()->getParam('email'))
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
            // FIX: Invalid customer address id xxx
            $quote->getBillingAddress()->setCustomerAddressId(null);
            $quote->getShippingAddress()->setCustomerAddressId(null);
        }

        $payment = $quote->getPayment();
        $oldReferenceId = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
        $oldAmountFormatted = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED);
        $oldCurrencyCode = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE);

        $quoteId = $quote->getEntityId();
        $paymentId = $payment->getPaymentId();
        $this->commonHelper->logInfo(json_encode(compact('quoteId', 'paymentId', 'oldReferenceId', 'oldAmountFormatted', 'oldCurrencyCode')));

        if (!empty($oldReferenceId)) {
            try {
                $paymentInfoResponse = $this->paymentApi->getPaymentInfo($oldReferenceId);
                if($paymentInfoResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PROCESSING) {
                    // avoid customer to pay this old payment
                    $this->paymentApi->cancelPayment($oldReferenceId);
                } else if ($paymentInfoResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PAID) {
                    throw new Exception("previous payment was paid successfully, cannot place again");
                }
            } catch (\Exception $e) {
                $this->commonHelper->logError("failed to check old payment, " . get_class($e) . ', message: ' . $e->getMessage());
                return $this->responseError('Your previous order is being processed, please do not submit duplicated order. (' . $oldReferenceId . ')');
            }
        }

        $resp = $this->paymentApi->preparePayment($quote, $reqData);
        if (!$resp->getReferenceId()) {
            return $this->responseError($resp->getMessage());
        }

        $payment->setMethod(\Atome\MagentoPayment\Model\PaymentGateway::METHOD_CODE);
        $payment->setAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID, $resp->getReferenceId());
        $payment->setAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED, $resp->getAmount());
        $payment->setAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE, $resp->getCurrency());
        $payment->setAdditionalInformation(PaymentGateway::MERCHANT_REFERENCE_ID, $quote->getReservedOrderId());
        if ($this->paymentGatewayConfig->isDebugEnabled()) {
            $debugSecret = trim(base64_encode(random_bytes(9)), '=');
            $debugSecret = str_replace(['+', '/'], ['-', '.'], $debugSecret);
            $payment->setAdditionalInformation(PaymentGateway::PAYMENT_DEBUG_SECRET, $debugSecret);

            foreach ($reqData as $k => $v) {
                if (substr($k, -3) === 'Url') {
                    $url = $v;
                    $url .= (strpos($v, '?') === false ? '?' : '&') . 'debugSecret=' . rawurlencode($debugSecret);
                    $this->commonHelper->debug("debug url: $k: $url");
                }
            }
        } else {
            $payment->unsAdditionalInformation(PaymentGateway::PAYMENT_DEBUG_SECRET);
        }

        $this->quoteValidator->validateBeforeSubmit($quote);

        $this->quoteRepository->save($quote);
        $this->checkoutSession->replaceQuote($quote);

        return $this->jsonFactory->create()->setData(['atomePaymentUrl' => $resp->getRedirectUrl()]);
    }

    private function createPaymentAndRedirect()
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if (!$order) {
                throw new Exception(__('Order not found.'));
            }
            $payment = $order->getPayment();
            if (!isset($payment) || empty($payment)) {
                throw new Exception(__('Order Payment is empty.'));
            }
            $method = $order->getPayment()->getMethod();
            if ($method !== PaymentGateway::METHOD_CODE) {
                throw new Exception(__('Payment method not correct.'));
            }

            $oldReferenceId = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
            $oldAmountFormatted = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED);
            $oldCurrencyCode = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE);

            $orderId = $order->getEntityId();
            $paymentId = $payment->getEntityId();
            $this->commonHelper->logInfo(json_encode(compact('orderId', 'paymentId', 'oldReferenceId', 'oldAmountFormatted', 'oldCurrencyCode')));

            if (!empty($oldReferenceId)) {
                $paymentResponse = $this->paymentApi->getPaymentInfo($oldReferenceId);
                if ($paymentResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PAID) {
                    $this->_redirect('atome/payment/result?type=result&orderId=' . $orderId);
                    return;
                } else if (
                    $paymentResponse->getMerchantReferenceId() == $order->getIncrementId() &&
                    $paymentResponse->getAmount() == $this->paymentHelper->formatAmount($order->getGrandTotal()) &&
                    $paymentResponse->getCurrency() == $order->getOrderCurrencyCode() &&
                    $paymentResponse->getStatus() == PaymentApi::PAYMENT_STATUS_PROCESSING
                ) {
                    $this->_redirect($paymentResponse->getRedirectUrl());
                    return;
                }
            }

            $resp = $this->paymentApi->preparePaymentFromOrder($order, $reqData);
            if (!$resp->getReferenceId()) {
                return $this->responseError($resp->getMessage());
            }

            $payment->setAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID, $resp->getReferenceId());
            $payment->setAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED, $resp->getAmount());
            $payment->setAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE, $resp->getCurrency());
            if ($this->paymentGatewayConfig->isDebugEnabled()) {
                $debugSecret = trim(base64_encode(random_bytes(9)), '=');
                $debugSecret = str_replace(['+', '/'], ['-', '.'], $debugSecret);
                $payment->setAdditionalInformation(PaymentGateway::PAYMENT_DEBUG_SECRET, $debugSecret);

                foreach ($reqData as $k => $v) {
                    if (substr($k, -3) === 'Url') {
                        $url = $v;
                        $url .= (strpos($v, '?') === false ? '?' : '&') . 'debugSecret=' . rawurlencode($debugSecret);
                        $this->commonHelper->debug("debug url: $k: $url");
                    }
                }
            } else {
                $payment->unsAdditionalInformation(PaymentGateway::PAYMENT_DEBUG_SECRET);
            }

            $this->orderRepository->save($order);

            $this->_redirect($resp->getRedirectUrl());
            return;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __($e->getMessage())
            );
            $this->commonHelper->logError($e->getMessage());
            $this->commonHelper->logError('debug_trace: ' . json_encode($e->getTrace()));
            $this->checkoutSession->restoreQuote();
            $order->cancel();
            $order->save();
            $registry = $this->_objectManager->get('Magento\Framework\Registry');
            $registry->register('isSecureArea', true, true);
            $order->delete();
            $registry->unregister('isSecureArea');
            $this->_redirect('checkout/cart');
        }
    }

    public function execute()
    {
        if ($this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
            $this->createPaymentAndRedirect();
            return;
        }

        try {
            return $this->executePrepare();
        } catch (\Exception $e) {
            $this->commonHelper->logError("prepare failed: " . get_class($e) . ', message: ' . $e->getMessage() . ', code: ' . $e->getCode() . ', file: ' . $e->getFile() . ', line: ' . $e->getLine() . 'prepare debug trace: ' . json_encode($e->getTrace()));
            return $this->responseError($e->getMessage());
        }
    }
}
