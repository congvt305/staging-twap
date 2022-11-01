<?php

namespace CJ\CustomAtome\Controller\Overridden\Payment;

use Atome\MagentoPayment\Helper\CallbackHelper;
use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Model\Adapter\PaymentApi;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Atome\MagentoPayment\Model\PaymentGateway;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\SalesRule\Model\Coupon\Usage\Processor as CouponUsageProcessor;

/**
 * Class Result
 */
class Result extends \Atome\MagentoPayment\Controller\Payment\Result
{
    /**
     * @var CouponUsageProcessor
     */
    protected $couponUsageProcessor;

    /**
     * @var \Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory
     */
    protected $updateInfoFactory;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param CommonHelper $commonHelper
     * @param PaymentGatewayConfig $paymentGatewayConfig
     * @param CallbackHelper $callbackHelper
     * @param PaymentApi $paymentApi
     * @param CouponUsageProcessor $couponUsageProcessor
     * @param \Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory $updateInfoFactory
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CommonHelper $commonHelper,
        PaymentGatewayConfig $paymentGatewayConfig,
        CallbackHelper $callbackHelper,
        PaymentApi $paymentApi,
        CouponUsageProcessor $couponUsageProcessor,
        \Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory $updateInfoFactory
    ) {
        $this->couponUsageProcessor = $couponUsageProcessor;
        $this->updateInfoFactory = $updateInfoFactory;
        parent::__construct($context, $checkoutSession, $commonHelper, $paymentGatewayConfig, $callbackHelper,
            $paymentApi);
    }

    /**
     * @return {@inheritDoc}
     */
    public function execute()
    {
        $this->commonHelper->logInfo('action Result: begin');
        if ($this->paymentGatewayConfig->getPaymentAction() == AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {
            try {
                $queryParams = $this->getRequest()->getParams();
                $type = $queryParams['type'] ?? '';
                $this->commonHelper->logInfo('action Result: queryParams => ' . json_encode($queryParams));
                $quoteId = $queryParams['quoteId'] ?? null;
                $orderId = $queryParams['orderId'] ?? null;
                if ($this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
                    $ctx = $this->callbackHelper->applyOrderPayment($orderId, $queryParams);
                } else {
                    $ctx = $this->callbackHelper->applyQuotePayment($quoteId, $queryParams);
                }
            } catch (LocalizedException $e) {
                $errorCode = $e->getCode();
                $error = $e->getMessage();
                $this->commonHelper->logError('action Result applyQuotePayment LocalizedException: ' . $error);
            } catch (Exception $e) {
                $errorCode = $e->getCode();
                $this->commonHelper->logError("action Result applyQuotePayment failed: " . get_class($e) . ':' . $e->getMessage());
                $error = "Payment Error: " . get_class($e) . ':' . $e->getMessage();
            }
        }
        if (!empty($error)) {
            $redirect = 'checkout/cart';
            if ($type != 'cancel' || $errorCode !== CommonHelper::EXP_CODE_PAYMENT_PROCESSING) {
                $this->messageManager->addError($error);
            }
            if (!empty($orderId) && $this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
                $order = $this->callbackHelper->getOrderByOrderId($orderId);
                if (!$this->paymentGatewayConfig->getClearCartWithoutPaying()) {
                    $this->checkoutSession->restoreQuote();
                }
                if ($order) {
                    $order->cancel();
                    $order->save();
                    $this->_refundCoupon($order);
                    if ($type == 'cancel' && $this->paymentGatewayConfig->getDeleteOrdersWithoutPaying()) {
                        $registry = $this->_objectManager->get('Magento\Framework\Registry');
                        $registry->register('isSecureArea', true, true);
                        $order->delete();
                        $registry->unregister('isSecureArea');
                    }
                    // 尝试去取消 payment，防止客户又去付款导致回调失败
                    try {
                        $payment = $order->getPayment();
                        $oldReferenceId = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
                        $this->commonHelper->logInfo("action Result: cancel old payment: {$payment->getPaymentId()} ($oldReferenceId), method={$payment->getMethod()}, data=" . json_encode($payment->getData()));
                        $this->paymentApi->cancelPayment($oldReferenceId);
                    } catch (\Exception $e) {
                        $this->commonHelper->logError("action Result: failed to cancel old payment, " . get_class($e) . ', message: ' . $e->getMessage());
                    }
                }
            }
        } else {
            if (!empty($orderId) && $this->paymentGatewayConfig->getOrderCreatedWhen() === 'before_paying') {
                $order = $ctx->orderCreated;
            } else {
                $order = $this->callbackHelper->getOrderByIncrementId($ctx->quote->getReservedOrderId());
                $this->checkoutSession
                    ->setLastQuoteId($ctx->quote->getId())
                    ->setLastSuccessQuoteId($ctx->quote->getId())
                    ->clearHelperData();
            }

            $this->checkoutSession->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId());
            $redirect = 'checkout/onepage/success';
            $this->messageManager->addSuccess(__("Atome Payment Completed"));
        }
        $this->commonHelper->logInfo('action Result: redirectUrl => ' . $redirect);
        $this->_redirect($redirect);
    }

    /**
     * push request about coupon used time to queue
     * later, cron will trigger the consumer to retrieve the request from queue to process decrease the used time
     *
     * @param $order
     * @return void
     */
    protected function _refundCoupon($order) {
        $couponCodes = $order->getCouponCode();
        if ($couponCodes) {
            $coupons = array_unique(explode(',', $couponCodes));
            if (count($coupons) == 1) {
                $coupon = reset($coupons);
                $coupon = trim($coupon);
                $updateInfo = $this->updateInfoFactory->create();
                $updateInfo->setCouponCode($coupon);
                $updateInfo->setCustomerId($order->getCustomerId());
                $updateInfo->setIsIncrement(false);
                $this->couponUsageProcessor->process($updateInfo);
            }
        }
    }
}
