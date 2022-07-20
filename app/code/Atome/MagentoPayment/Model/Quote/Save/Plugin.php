<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Quote\Save;

use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Helper\PaymentHelper;
use Atome\MagentoPayment\Model\Adapter\PaymentApi;
use Atome\MagentoPayment\Model\PaymentGateway;
use Magento\Quote\Model\Quote;

class Plugin
{
    protected $commonHelper;
    protected $paymentApi;
    protected $paymentHelper;

    public function __construct(
        CommonHelper $commonHelper,
        PaymentApi $paymentApi,
        PaymentHelper $paymentHelper
    ) {
        $this->commonHelper = $commonHelper;
        $this->paymentApi = $paymentApi;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param Quote $quote
     * @return mixed
     */
    public function aroundSave(
        $subject,
        \Closure $proceed,
        $quote
    ) {
        $payment = $quote->getPayment();

        if ($quote->getIsActive() && $payment) {
            $oldReferenceId = $payment->getAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
            if ($oldReferenceId) {
                $grandTotalFormatted = $this->paymentHelper->formatAmount($quote->getGrandTotal());
                if ($grandTotalFormatted != $payment->getAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED)
                    || $quote->getQuoteCurrencyCode() != $payment->getAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE)
                    || $payment->getMethod() !== PaymentGateway::METHOD_CODE
                ) {
                    try {
                        $paymentInfoResponse = $this->paymentApi->getPaymentInfo($oldReferenceId);
                        if ($paymentInfoResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PROCESSING) {
                            $this->commonHelper->logInfo("cancel old payment: {$payment->getPaymentId()} ($oldReferenceId), method={$payment->getMethod()}, data=" . json_encode($payment->getData()));
                            $this->paymentApi->cancelPayment($oldReferenceId);
                        } else if ($paymentInfoResponse->getStatus() === PaymentApi::PAYMENT_STATUS_PAID) {
                            throw new \Exception("previous payment was paid successfully, cannot edit again");
                        }
                    } catch (\Exception $e) {
                        $this->commonHelper->logError("failed to cancel old payment, " . get_class($e) . ', message: ' . $e->getMessage());
                        throw $e;
                    }
                    $payment->unsAdditionalInformation(PaymentGateway::PAYMENT_REFERENCE_ID);
                    $payment->unsAdditionalInformation(PaymentGateway::PAYMENT_AMOUNT_FORMATTED);
                    $payment->unsAdditionalInformation(PaymentGateway::PAYMENT_CURRENCY_CODE);
                    $payment->unsAdditionalInformation(PaymentGateway::PAYMENT_DEBUG_SECRET);
                    $payment->unsAdditionalInformation(PaymentGateway::MERCHANT_REFERENCE_ID);
                }
            }
        }
        return $proceed($quote);
    }
}
