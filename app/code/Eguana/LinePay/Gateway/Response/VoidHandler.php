<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 19/10/20
 * Time: 1:47 PM
 */
namespace Eguana\LinePay\Gateway\Response;

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
