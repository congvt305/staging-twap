<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 19/10/20
 * Time: 6:03 PM
 */
namespace Eguana\LinePay\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Eguana\LinePay\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Payment Data Builder for refund request
 */
class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;
    const AMOUNT = 'amount';
    const PAYMENT_METHOD_NONCE = 'paymentMethodNonce';
    const MERCHANT_ACCOUNT_ID = 'merchantAccountId';
    const ORDER_ID = 'orderId';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        $result = [
            self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
            self::PAYMENT_METHOD_NONCE => $payment->getAdditionalInformation(
                DataAssignObserver::PAYMENT_METHOD_NONCE
            ),
            self::ORDER_ID => $order->getOrderIncrementId()
        ];

        return $result;
    }
}
