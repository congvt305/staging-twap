<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/10/20
 * Time: 3:48 PM
 */
namespace Eguana\LinePay\Gateway\Request;

use Eguana\LinePay\Model\Quote as LinePayModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class CaptureDataBuilder
 */
class AuthorizeDataBuilder implements BuilderInterface
{
    use Formatter;

    const TRANSACTION_ID = 'transaction_id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var LinePayModel
     */
    private $quoteModel;

    /**
     * AuthorizeDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param LinePayModel $quoteModel
     */
    public function __construct(SubjectReader $subjectReader, LinePayModel $quoteModel)
    {
        $this->subjectReader                     = $subjectReader;
        $this->quoteModel                        = $quoteModel;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $quote = $this->quoteModel->getQuote();
        $payment = $paymentDO->getPayment();
        $transactionId = $quote->getPayment()->getAdditionalInformation('transaction_id');
        $amount = (int)round($quote->getGrandTotal(), 0);
        if (!$transactionId) {
            throw new LocalizedException(__('No authorization transaction to proceed authorize.'));
        }
        return [
            self::TRANSACTION_ID => $transactionId,
            'amount' => $amount
        ];
    }
}
