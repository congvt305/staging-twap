<?php

namespace Payoo\PayNow\Gateway\Response;

use Payoo\PayNow\Logger\Logger as PayooLogger;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionIdHandler
 *
 * Handle transaction
 */
class TransactionIdHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * @var PayooLogger
     */
    private PayooLogger $logger;

    /**
     * @param PayooLogger $logger
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        PayooLogger $logger,
        SubjectReader $subjectReader
    ) {
        $this->logger = $logger;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handles response
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        if ($paymentDO->getPayment() instanceof Payment) {
            if (isset($response['ResponseData'])) {
                $responseData = json_decode($response['ResponseData'], true);
                /** @var Payment $orderPayment */
                $orderPayment = $paymentDO->getPayment();
                $transferCode = isset($responseData['TransferCode']) ? $responseData['TransferCode'] : '';
                $this->setTransactionId(
                    $orderPayment,
                    $transferCode
                );
                $orderPayment->setTransactionAdditionalInfo(
                    'REFUND_INFO',
                    $responseData
                );
                $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
                $closed = $this->shouldCloseParentTransaction($orderPayment);
                $orderPayment->setShouldCloseParentTransaction($closed);
                $this->logger->info(PayooLogger::TYPE_LOG_REFUND,
                    [
                        'OrderNo' => $paymentDO->getOrder()->getOrderIncrementId(),
                        'refundSuccess' => $responseData
                    ]);
            }
        }
    }

    /**
     * Set transaction id
     * @param Payment $orderPayment
     * @param $transaction
     */
    protected function setTransactionId(Payment $orderPayment, $transaction)
    {
        $orderPayment->setTransactionId($transaction);
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return false;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }
}
