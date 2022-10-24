<?php

namespace CJ\CustomAtome\Helper\Overridden;

use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Helper\PaymentHelper;
use Atome\MagentoPayment\Model\Adapter\PaymentApi;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Atome\MagentoPayment\Model\PaymentGateway;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteValidator;
use Magento\Quote\Model\ResourceModel\Quote as QuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Magento\Sales\Model\OrderRepository;

class CallbackHelper extends \Atome\MagentoPayment\Helper\CallbackHelper
{
    protected $_eventManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        CommonHelper $commonHelper,
        PaymentHelper $paymentHelper,
        PaymentGatewayConfig $paymentGatewayConfig,
        QuoteManagement $quoteManagement,
        BuilderInterface $transactionBuilder,
        OrderSender $orderSender,
        OrderRepository $orderRepository,
        PaymentRepository $paymentRepository,
        TransactionRepository $transactionRepository,
        NotifierInterface $notifier,
        QuoteValidator $quoteValidator,
        QuoteRepository $quoteRepository,
        PaymentApi $paymentApi,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct(
            $context,
            $resourceConnection,
            $searchCriteriaBuilder,
            $globalConfig,
            $commonHelper,
            $paymentHelper,
            $paymentGatewayConfig,
            $quoteManagement,
            $transactionBuilder,
            $orderSender,
            $orderRepository,
            $paymentRepository,
            $transactionRepository,
            $notifier,
            $quoteValidator,
            $quoteRepository,
            $paymentApi)
        ;
    }


    /**
     * @param Quote $quote
     * @param Order $order
     */
    private function sendPaymentNewEmail($quote, $order)
    {
        if ($this->paymentGatewayConfig->getOrderEmailSendBy() == 'atome') {

            try {
                $this->commonHelper->logInfo("send new payment email by atome: quote={$quote->getEntityId()}, order={$order->getIncrementId()}");
                $sendRes = $this->orderSender->send($order);
                $this->commonHelper->logInfo("send result: " . json_encode($sendRes));
            } catch (\Exception $e) {
                $this->commonHelper->logError("failed to send email by atome: " . json_encode($e));
            }
        } else {
            // a flag to set that there will be redirect to third party after confirmation
            $emailRedirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
            // we only want to send to customer about new order when there is no redirect to third party
            if (!$emailRedirectUrl && $order->getCanSendNewEmailFlag()) {
                try {
                    $this->commonHelper->logInfo("send new payment email by default: quote={$quote->getEntityId()}, order={$order->getIncrementId()}, url=$emailRedirectUrl");
                    if ($this->globalConfig->getValue('sales_email/general/async_sending') || !$order->getEmailSent()) {
                        $this->commonHelper->logInfo("send new payment email by default: begin sending");
                        $sendRes = $this->orderSender->send($order);
                        $this->commonHelper->logInfo("send result: " . json_encode($sendRes));
                    }
                } catch (\Exception $e) {
                    $this->commonHelper->logError("failed to send email by default: " . json_encode($e));
                }
            } else {
                $this->commonHelper->logInfo("skip new payment email by default: quote={$quote->getEntityId()}, order={$order->getIncrementId()}, url=$emailRedirectUrl");
            }
        }
    }

    /**
     * @param string $quoteId
     * @param array $queryParams
     * @return \Atome\MagentoPayment\Model\ApplyQuotePaymentContext
     * @throws LocalizedException
     */
    private function initQuotePaymentContext($quoteId, $queryParams)
    {
        /** @var \Atome\MagentoPayment\Model\ApplyQuotePaymentContext $ctx */
        $ctx = ObjectManager::getInstance()->create('Atome\MagentoPayment\Model\ApplyQuotePaymentContext');

        $ctx->quote = ObjectManager::getInstance()->create('Magento\Quote\Model\Quote');
        $this->quoteRepository->loadByIdWithoutStore($ctx->quote, $quoteId);

        $payment = $ctx->quote->getPayment();
        $quoteReferenceId = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
        $quoteAmountFormatted = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED);
        $quoteCurrencyCode = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE);

        if ($this->paymentGatewayConfig->isDebugEnabled()) {
            $queryDebugSecret = $queryParams['debugSecret'] ?? null;
            $paymentDebugSecret = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_DEBUG_SECRET);
            if ($paymentDebugSecret && $paymentDebugSecret === $queryDebugSecret) {
                $ctx->paymentResponse = ObjectManager::getInstance()->create('\Atome\MagentoPayment\Model\PaymentResponse');
                $ctx->paymentResponse->setData([
                    // 'referenceId' => 'DEBUG-' . date('Ymd-His'),
                    'referenceId' => $quoteReferenceId,
                    'currency' => $quoteCurrencyCode,
                    'amount' => $quoteAmountFormatted,
                    'status' => $queryParams['debugPaymentStatus'] ?? PaymentApi::PAYMENT_STATUS_PAID,
                ]);
                $this->commonHelper->debug("debug mock PaymentResponse: " . json_encode($ctx->paymentResponse->getData()));
            }
        }

        if (!isset($ctx->paymentResponse)) {
            $ctx->paymentResponse = $this->paymentApi->getPaymentInfo($quoteReferenceId);
        }
        return $ctx;
    }

    /**
     * @param string $orderId
     * @param array $queryParams
     * @return \Atome\MagentoPayment\Model\ApplyQuotePaymentContext
     * @throws LocalizedException
     */
    private function initOrderPaymentContext($orderId, $queryParams)
    {
        /** @var \Atome\MagentoPayment\Model\ApplyQuotePaymentContext $ctx */
        $ctx = ObjectManager::getInstance()->create('Atome\MagentoPayment\Model\ApplyQuotePaymentContext');

        $ctx->orderCreated = $this->getOrderByOrderId($orderId);
        if (empty($ctx->orderCreated) || !$ctx->orderCreated instanceof Order) {
            throw new \RuntimeException("No order $orderId in database. Maybe you have cancelled it or left the atome payment page.");
        }

        $quoteId = $ctx->orderCreated->getQuoteId();
        $ctx->quote = ObjectManager::getInstance()->create('Magento\Quote\Model\Quote');
        $this->quoteRepository->loadByIdWithoutStore($ctx->quote, $quoteId);

        $payment = $ctx->orderCreated->getPayment();
        if ($payment->getMethod() !== PaymentGateway::METHOD_CODE) {
            throw new \RuntimeException("This order's payment is not Atome");
        }

        $paymentReferenceId = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
        $paymentAmountFormatted = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED);
        $paymentCurrencyCode = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE);

        if ($this->paymentGatewayConfig->isDebugEnabled()) {
            $queryDebugSecret = $queryParams['debugSecret'] ?? null;
            $paymentDebugSecret = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_DEBUG_SECRET);
            if ($paymentDebugSecret && $paymentDebugSecret === $queryDebugSecret) {
                $ctx->paymentResponse = ObjectManager::getInstance()->create('\Atome\MagentoPayment\Model\PaymentResponse');
                $ctx->paymentResponse->setData([
                    // 'referenceId' => 'DEBUG-' . date('Ymd-His'),
                    'referenceId' => $paymentReferenceId,
                    'currency' => $paymentCurrencyCode,
                    'amount' => $paymentAmountFormatted,
                    'status' => $queryParams['debugPaymentStatus'] ?? PaymentApi::PAYMENT_STATUS_PAID,
                ]);
                $this->commonHelper->debug("debug mock PaymentResponse: " . json_encode($ctx->paymentResponse->getData()));
            }
        }

        if (!isset($ctx->paymentResponse)) {
            $ctx->paymentResponse = $this->paymentApi->getPaymentInfo($paymentReferenceId);
        }
        return $ctx;
    }

    /**
     * @param string $quoteId
     * @param array $queryParams
     * @return \Atome\MagentoPayment\Model\ApplyQuotePaymentContext
     * @throws LocalizedException
     */
    private function applyQuotePaymentInternal($quoteId, $queryParams)
    {
        $ctx = $this->initQuotePaymentContext($quoteId, $queryParams);
        $payment = $ctx->quote->getPayment();
        $quoteAmountFormatted = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED);
        $quoteCurrencyCode = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE);

        if ($ctx->paymentResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PROCESSING) {
            throw new LocalizedException(__("Atome payment is processing. Please wait a while."), null, CommonHelper::EXP_CODE_PAYMENT_PROCESSING);
        }

        if ($ctx->paymentResponse->getStatus() === PaymentApi::PAYMENT_STATUS_CANCELLED) {
            $ctx->quote->setIsActive(false);
            $this->quoteRepository->save($ctx->quote);
        }

        if ($ctx->paymentResponse->getAmount() != $quoteAmountFormatted || $ctx->paymentResponse->getCurrency() != $quoteCurrencyCode) {
            throw new LocalizedException(__('There are issues when processing your payment. Invalid payment request.'));
        }

        if (!$ctx->quote->getIsActive()) {
            if ($ctx->paymentResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PAID) {
                return $ctx;
            } else {
                throw new LocalizedException(__('Atome payment failed. Please try again or use an alternative payment method.'));
            }
        } else {
            if ($ctx->paymentResponse->getStatus() !== PaymentApi::PAYMENT_STATUS_PAID) {
                throw new LocalizedException(__("Atome payment doesn't complete. Please try again or use an alternative payment method."));
            }
        }

        if (!$ctx->quote->getIsActive() || $ctx->paymentResponse->getStatus() !== PaymentApi::PAYMENT_STATUS_PAID) {
            throw new LocalizedException(__("Atome payment unexpected problem occurs."));
        }

        //防止生成重复订单
        $merchantReferenceId = $payment->getAdditionalInformation(PaymentGateway::MERCHANT_REFERENCE_ID);
        if ($merchantReferenceId && $this->getOrderByIncrementId($merchantReferenceId)) {
            $this->commonHelper->logInfo('order has been created, return');
            return $ctx;
        }

        $ctx->quote->collectTotals();
        $this->quoteValidator->validateBeforeSubmit($ctx->quote);

        $ctx->orderCreated = $this->quoteManagement->submit($ctx->quote);
        if (!$ctx->orderCreated) {
            throw new LocalizedException(__('Error occurs when placing order: can not submit'));
        }

        if ($this->paymentHelper->formatAmount($ctx->orderCreated->getGrandTotal()) != $ctx->paymentResponse->getAmount()) {
            throw new LocalizedException(__("The grand total of this order is not equal to the amount of the payment.(order: {$this->paymentHelper->formatAmount($ctx->orderCreated->getGrandTotal())}, quote: {$this->paymentHelper->formatAmount($ctx->quote->getGrandTotal())}, atome: {$ctx->paymentResponse->getAmount()})"));
        }

        $payment = $ctx->orderCreated->getPayment();

        $orderAmountFormatted = $ctx->orderCreated->getBaseCurrency()->formatTxt($ctx->orderCreated->getGrandTotal());
        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($ctx->orderCreated)
            ->setTransactionId($ctx->paymentResponse->getReferenceId())
            ->setFailSafe(true)
            ->build(Transaction::TYPE_CAPTURE);  // can not use payment here, or it will not be displayed in the Order View => Transactions list

        $payment->setLastTransId($ctx->paymentResponse->getReferenceId());
        $payment->setTransactionId($ctx->paymentResponse->getReferenceId());
        $payment->addTransactionCommentsToOrder($transaction, __('The paid amount: %1.', $orderAmountFormatted));
        $payment->setParentTransactionId(null);
        $this->paymentRepository->save($payment);

        $ctx->orderCreated->setBaseCustomerBalanceInvoiced(null);
        $ctx->orderCreated->setCustomerBalanceInvoiced(null);
        if ($this->paymentGatewayConfig->getOrderStatus() === 'complete') {
            $ctx->orderCreated->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
            $ctx->orderCreated->addStatusToHistory($ctx->orderCreated->getStatus(), 'Order processed successfully');
        }
        $this->orderRepository->save($ctx->orderCreated);

        $transaction = $this->transactionRepository->save($transaction);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $transactionId = $transaction->getTransactionId();

        return $ctx;
    }

    /**
     * @param string $orderId
     * @param array $queryParams
     * @return \Atome\MagentoPayment\Model\ApplyQuotePaymentContext
     * @throws LocalizedException
     */
    private function applyOrderPaymentInternal($orderId, $queryParams)
    {
        $ctx = $this->initOrderPaymentContext($orderId, $queryParams);
        $payment = $ctx->orderCreated->getPayment();
        $paymentAmountFormatted = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED);
        $paymentCurrencyCode = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE);

        if ($ctx->paymentResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PROCESSING) {
            throw new LocalizedException(__("Atome payment is processing. Please wait a while."), null, CommonHelper::EXP_CODE_PAYMENT_PROCESSING);
        }

        if ($ctx->paymentResponse->getStatus() === PaymentApi::PAYMENT_STATUS_CANCELLED) {
            $ctx->orderCreated->cancel();
            $ctx->orderCreated->save();
            $ctx->orderCreated->addStatusToHistory($ctx->orderCreated->getStatus(), 'Order cancelled');
            $this->orderRepository->save($ctx->orderCreated);
            return $ctx;
        }

        if ($ctx->paymentResponse->getAmount() != $paymentAmountFormatted || $ctx->paymentResponse->getCurrency() != $paymentCurrencyCode) {
            throw new LocalizedException(__('There are issues when processing your payment. Invalid payment request.'));
        }

        if ($ctx->paymentResponse->getAmount() != $this->paymentHelper->formatAmount($ctx->orderCreated->getGrandTotal()) || $ctx->paymentResponse->getCurrency() != $ctx->orderCreated->getOrderCurrencyCode()) {
            throw new LocalizedException(__('There are issues when processing your payment. The payment amount or currency does not match the order.'));
        }

        if ($ctx->paymentResponse->getStatus() !== PaymentApi::PAYMENT_STATUS_PAID) {
            throw new LocalizedException(__("Atome payment doesn't complete. Please wait a while."));
        }

        if (!in_array($ctx->orderCreated->getStatus(), [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_PROCESSING, \Magento\Sales\Model\Order::STATE_COMPLETE, "processing_with_shipment"])) {
            throw new LocalizedException(__("The order has wrong status: {$ctx->orderCreated->getStatus()}"));
        }

        if ($this->paymentGatewayConfig->getOrderStatus() === 'complete') {
            if ($ctx->orderCreated->getStatus() === \Magento\Sales\Model\Order::STATE_COMPLETE) {
                return $ctx;
            }
            $ctx->orderCreated->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
            $ctx->orderCreated->addStatusToHistory($ctx->orderCreated->getStatus(), 'Order processed successfully');
        } else {
            if ($ctx->orderCreated->getStatus() === \Magento\Sales\Model\Order::STATE_PROCESSING) {
                return $ctx;
            }
            $ctx->orderCreated->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
        }

        if (!$this->hasInvoice($ctx->orderCreated, $ctx->paymentResponse->getReferenceId())) {
            $payment->capture(null);
            $orderAmountFormatted = $ctx->orderCreated->getBaseCurrency()->formatTxt($ctx->orderCreated->getGrandTotal());
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($ctx->orderCreated)
                ->setTransactionId($ctx->paymentResponse->getReferenceId())
                ->setFailSafe(true)
                ->build(Transaction::TYPE_CAPTURE);  // can not use payment here, or it will not be displayed in the Order View => Transactions list

            $transaction = $this->transactionRepository->save($transaction);
            $payment->setLastTransId($ctx->paymentResponse->getReferenceId());
            $payment->setTransactionId($ctx->paymentResponse->getReferenceId());
            $payment->addTransactionCommentsToOrder($transaction, __('The paid amount: %1.', $orderAmountFormatted));
            $payment->setParentTransactionId(null);
            $this->paymentRepository->save($payment);
        }

        $ctx->orderCreated->setBaseCustomerBalanceInvoiced(null);
        $ctx->orderCreated->setCustomerBalanceInvoiced(null);
        $this->orderRepository->save($ctx->orderCreated);

        return $ctx;
    }

    /**
     * {@inheritdoc}
     */
    public function applyOrderPayment($orderId, $queryParams)
    {
        $connection = $this->resourceConnection->getConnection('sales');
        $connection->beginTransaction();
        try {
            $ctx = $this->applyOrderPaymentInternal($orderId, $queryParams);

            $connection->commit();

            if (!empty($queryParams['debugException'])) {
                $connection->beginTransaction();
                throw new \RuntimeException('test exception during payment callback transaction');
            }
        } catch (\Exception $e) {
            $this->commonHelper->debug("applyOrderPayment debug trace: " . json_encode($e->getTrace()));
            $this->commonHelper->logError("applyOrderPayment failed: " . get_class($e) . ', message: ' . $e->getMessage() . ', code: ' . $e->getCode() . ', file: ' . $e->getFile() . ', line: ' . $e->getLine());
             // custom to return coupon usage
            $orderCreated = $this->orderRepository->get($orderId);
            $connection->rollBack();
            $this->_eventManager->dispatch('order_cancel_after', ['order' => $orderCreated]);
            // end custom
            throw $e;
        }
        if ($ctx->orderCreated && $ctx->quote) {
            $ctx->orderCreated->setCanSendNewEmailFlag(true);
            $this->sendPaymentNewEmail($ctx->quote, $ctx->orderCreated);
        }
        return $ctx;
    }


}
