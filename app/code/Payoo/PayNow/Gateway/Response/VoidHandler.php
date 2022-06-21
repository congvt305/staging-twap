<?php

namespace Payoo\PayNow\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

/**
 * Class VoidHandler
 *
 * Void request handler
 */
class VoidHandler extends TransactionIdHandler
{

    /**
     * Set transaction id
     * @param Payment $orderPayment
     * @param $transaction
     */
    protected function setTransactionId(Payment $orderPayment, $transaction)
    {
        return;
    }

    /**
     * Close transaction
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return true;
    }

    /**
     * Close parent transaction
     * @param Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }
}
