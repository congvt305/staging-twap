<?php

namespace CJ\CustomIpay88\Controller\Overridden\Checkout;

/**
 * Class Redirect
 */
class Redirect extends \Ipay88\Payment\Controller\Checkout\Redirect
{
    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $responseData = $this->ipay88PaymentDataHelper->normalizeResponseData($this->magentoRequest->getParams());

        $salesOrderSearchCriteria = $this->magentoApiSearchCriteriaBuilder->addFilter(
            \Magento\Sales\Api\Data\OrderInterface::INCREMENT_ID,
            $responseData['ref_no']
        )->create();

        $salesOrderCollection = $this->magentoSalesOrderRepository->getList($salesOrderSearchCriteria)->getItems();
        if ( ! $salesOrderCollection) {
            $this->redirectToCheckoutCartPage(__("No order #{$responseData['ref_no']} for processing found."), $responseData);

            return;
        }

        /**
         * @var \Magento\Sales\Model\Order $salesOrder
         */
        $salesOrder = reset($salesOrderCollection);

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_FAIL) {
            $this->redirectToCheckoutCartPage(__($responseData['err_desc']), $responseData, $salesOrder);

            return;
        }

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_PENDING) {
            $this->ipay88PaymentLogger->info('[redirect] pending', [
                'order'    => $salesOrder->getIncrementId(),
                'response' => $responseData,
            ]);

            $this->redirectToCheckoutSuccessPage(__('We have received and will process your order once payment is confirmed.'));

            return;
        }

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_SUCCESS) {
            $isResponseSignatureValid = $this->ipay88PaymentDataHelper->validateResponseSignature($responseData);
            if ( ! $isResponseSignatureValid) {
                $this->redirectToCheckoutCartPage(__("Returned signature `{$responseData['signature']}` not match."), $responseData, $salesOrder);

                return;
            }

            if ($salesOrder->getPayment()->getLastTransId()) {
                $this->ipay88PaymentLogger->info('[redirect] transaction existed', [
                    'order'    => $salesOrder->getIncrementId(),
                    'response' => $responseData,
                ]);

                $this->redirectToCheckoutSuccessPage();

                return;
            }

            $isProcessing = (bool) $this->magentoCache->load("ipay88_payment_processing_{$salesOrder->getIncrementId()}");
            if ($isProcessing) {
                $this->ipay88PaymentLogger->info('[redirect] processing by callback', [
                    'order'    => $salesOrder->getIncrementId(),
                    'response' => $responseData,
                ]);

                sleep(1);

                $this->redirectToCheckoutSuccessPage();

                return;
            }

            $this->magentoCache->save(1, "ipay88_payment_processing_{$salesOrder->getIncrementId()}");

            $salesInvoice = $this->magentoSalesInvoiceService->prepareInvoice($salesOrder);
            $salesInvoice->setTransactionId($responseData['trans_id']);
            //            $salesInvoice->setRequestedCaptureCase();
            $salesInvoice->register();

            //            $salesOrder->setCustomerNoteNotify(! empty($data['send_email']));
            $salesOrder->setIsInProcess(true);
            $salesOrder->getPayment()->setLastTransId($responseData['trans_id']);
            $salesOrder->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $salesOrder->setStatus($this->magentoSalesOrderConfig->getStateDefaultStatus($salesOrder->getState()));
            $salesOrder->addStatusToHistory($salesOrder->getStatus(), "Ipay88 transaction #{$salesInvoice->getTransactionId()} success.");

            /**
             * @var \Magento\Framework\DB\Transaction $dbTransaction ;
             */
            $dbTransaction = $this->magentoDbTransactionFactory->create();
            $dbTransaction->addObject($salesOrder);
            $dbTransaction->addObject($salesInvoice);
            $dbTransaction->save();

            $this->magentoCache->remove("ipay88_payment_processing_{$salesOrder->getIncrementId()}");

            $this->ipay88PaymentLogger->info('[redirect] success', [
                'order'    => $salesOrder->getIncrementId(),
                'invoice'  => $salesInvoice->getIncrementId(),
                'response' => $responseData,
            ]);

            $this->redirectToCheckoutSuccessPage();

            return;
        }

        $this->ipay88PaymentLogger->notice('[redirect] no handler', [
            'order'    => $salesOrder->getIncrementId(),
            'response' => $responseData,
        ]);

        $this->redirectToHomepage();
    }
}
