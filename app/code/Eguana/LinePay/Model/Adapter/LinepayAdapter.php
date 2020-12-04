<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/10/20
 * Time: 3:24 PM
 */
namespace Eguana\LinePay\Model\Adapter;

use Eguana\LinePay\Model\Payment;

/**
 * Class LinepayAdapter
 *
 * Line pay adapter class for sale and capture
 */
class LinepayAdapter
{
    /**
     * @var Payment
     */
    private $linePayModel;

    /**
     * LinepayAdapter constructor.
     * @param Payment $linePayModel
     */
    public function __construct(
        Payment $linePayModel
    ) {
        $this->linePayModel = $linePayModel;
    }

    /**
     * sale
     * @param array $attributes
     * @return array|bool|float|int|string|null
     */
    public function sale(array $attributes)
    {
        $transactionId = $attributes['transaction_id'];
        $amount = $attributes['amount'];
        $orderId = $attributes['order_id'];
        $response = $this->linePayModel->confirmPayment($transactionId, $orderId, $amount);
        return $response;
    }

    /**
     * Capture payment
     * @param array $attributes
     * @return array|bool|float|int|string|null
     */
    public function capture(array $attributes)
    {
        $transactionId = $attributes['transaction_id'];
        $amount = $attributes['amount'];
        $orderId = $attributes['order_id'];
        $response = $this->linePayModel->confirmPayment($transactionId, $orderId, $amount);
        return $response;
    }

    /**
     * Void payment
     * @param array $attributes
     * @return array|bool|float|int|string|null
     */
    public function void(array $attributes)
    {
        $transactionId = $attributes['transaction_id'];
        $response = $this->linePayModel->voidPayment($transactionId);
        return $response;
    }

    /**
     * Refund payment
     * @param $transactionId
     * @param $amount
     * @param $storeId
     * @return array|bool|float|int|string|null
     */
    public function refund($transactionId, $amount, $storeId)
    {
        $response = $this->linePayModel->refundPayment($transactionId, $amount, $storeId);
        return $response;
    }
}
