<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 22/10/20
 * Time: 4:31 PM
 */
namespace Eguana\LinePay\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Eguana\LinePay\Model\Quote as LinePayModel;

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
    private $subjectReader;

    /**
     * @var LinePayModel
     */
    private $quoteModel;

    /**
     * TransactionIdHandler constructor.
     * @param SubjectReader $subjectReader
     * @param LinePayModel $quoteModel
     */
    public function __construct(
        SubjectReader $subjectReader,
        LinePayModel $quoteModel
    ) {
        $this->subjectReader = $subjectReader;
        $this->quoteModel                        = $quoteModel;
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
            if (isset($response['object']['returnCode'])) {
                if ($response['object']['returnCode'] == '0000') {
                    if (isset($response['object']['info']['transactionId'])) {
                        $transaction = $response['object']['info']['transactionId'];

                        /** @var Payment $orderPayment */
                        $orderPayment = $paymentDO->getPayment();
                        $quote = $this->quoteModel->getQuote();
                        $quoteInfo = $quote->getPayment()->getAdditionalInformation();
                        $quoteInfo = $quoteInfo["raw_details_info"];
                        $transactionInfo = $response['object']['info']['payInfo'][0];
                        $data = array_merge($quoteInfo, $transactionInfo);
                        $this->setTransactionId(
                            $orderPayment,
                            $transaction
                        );
                        $orderPayment->setTransactionAdditionalInfo(
                            'raw_details_info',
                            $data
                        );
                        $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
                        $closed = $this->shouldCloseParentTransaction($orderPayment);
                        $orderPayment->setShouldCloseParentTransaction($closed);
                    }
                }
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
