<?php

namespace Eguana\EInvoice\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class EinvoiceIssue
 * @package Eguana\EInvoice\Model
 */
class EinvoiceIssue
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return int
     * @throws \Exception
     */
    public function toggleStatus($order)
    {
        $status = 0;
        $payment = $order->getPayment();
        $additionalData = $payment->getAdditionalData();
        $additionalData = json_decode($additionalData, true);

        if (isset($additionalData['InvoiceNumber'])) {
            throw new LocalizedException(__('This order has already issued an Einvoice, so we can\'t toggle the status of Einvoice Issue of it. Order id %1', $order->getIncrementId()));
        }
        if (isset($additionalData['RtnCode']) && $additionalData['RtnCode'] == 1) {
            $status = 0;
        } elseif ((isset($additionalData['RtnCode']) && $additionalData['RtnCode'] == 0) || !isset($additionalData['RtnCode'])) {
            $status = 1;
        }
        $additionalData['RtnCode'] = $status;
        $payment->setAdditionalData(json_encode($additionalData))->save();

        return $status;
    }
}