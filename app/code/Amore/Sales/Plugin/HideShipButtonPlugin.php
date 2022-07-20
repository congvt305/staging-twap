<?php

namespace Amore\Sales\Plugin;

class HideShipButtonPlugin
{
    /**
     * const ECPAY CODE
     */
    const ECPAY_CODE = 'ecpay_ecpaypayment';

    /**
     * const PENDING STATUS
     */
    const PENDING_STATUS = 'pending';

    /**
     * After plugin remove ship button when order is pending and order is failed ecpay method
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     * @param $result
     * @return mixed
     */
    public function afterGetOrderId(\Magento\Sales\Block\Adminhtml\Order\View $subject, $result)
    {
        $order = $subject->getOrder();
        // remove ship button when status is pending and order is failed ecpay method
        $payment = $order->getPayment();
        if ($order->getStatus() == self::PENDING_STATUS && $payment->getLastTransId() == null && $payment->getMethod() == self::ECPAY_CODE) {
            $subject->removeButton('order_ship');
        }
        return $result;
    }
}
