<?php

namespace Payoo\PayNow\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

class ResultHandler implements HandlerInterface
{
    const RESULT = 'RESULT';

    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
       
        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        $payment->setIsTransactionPending(true);

        $payment->setAdditionalInformation(
            self::RESULT,
            $response
        );
    }
}
