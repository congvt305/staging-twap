<?php

namespace Payoo\PayNow\Model;

use Magento\Sales\Model\Order\Payment\Transaction;
use Exception;
use Payoo\PayNow\Logger\Logger as PayooLogger;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;

class Payment
{
    /**
     * @var PayooLogger
     */
    protected PayooLogger $logger;

    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $transactionBuilder;


    /**
     * @var TransactionRepository
     */
    protected TransactionRepository $transactionRepository;

    /**
     * @param TransactionRepository $transactionRepository
     * @param BuilderInterface $transactionBuilder
     * @param PayooLogger $logger
     */
    public function __construct(
        TransactionRepository $transactionRepository,
        BuilderInterface $transactionBuilder,
        PayooLogger $logger
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->logger = $logger;
    }

    /**
     * @param $order
     * @param $transactionId
     * @param $type
     * @param array $paymentData
     * @return \Magento\Sales\Api\Data\TransactionInterface
     * @throws Exception
     */
    public function createTransaction($order, $transactionId, $type, array $paymentData = [])
    {
        try {
            //get payment object from order object
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);
            $payment->setTransactionId($transactionId);
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );

            $message = __('The authorized amount is %1.', $formatedPrice);
            //get the object of builder class
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => $paymentData]
                )
                ->setFailSafe(true)
                ->build($type); //build method creates the transaction and returns the object
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->save();
            $transaction->save();
            return $transaction;
        } catch (Exception $exception) {
            $this->logger->error(PayooLogger::TYPE_LOG_CREATE,
                [
                    'message' => $exception->getMessage()
                ]
            );
            throw new Exception(__('Something went wrong while saving transaction'));
        }
    }
}
