<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-09-08
 * Time: 오전 10:20
 */

namespace Amore\Sap\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;

class CustomState extends \Magento\Sales\Model\ResourceModel\Order\Handler\State
{
    const STORES_VN = ['vn_laneige'];
    const PAYOO_PAYMENT_METHOD = 'paynow';

    public function check(Order $order)
    {
        $shippingMethod = $order->getShippingMethod();
        if ($shippingMethod == 'gwlogistics_CVS' && ($order->getStatus() == 'sap_processing' ||
            $order->getStatus() == 'sap_success' || $order->getStatus() == 'sap_fail' ||
            $order->getStatus() == 'preparing')) {
            $order->setState("processing")->setStatus($order->getStatus());
        } else {
            $currentState = $order->getState();
            if ($currentState == Order::STATE_NEW && $order->getIsInProcess()) {
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
                $currentState = Order::STATE_PROCESSING;
            }

            if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice()) {
                if (in_array($currentState, [Order::STATE_PROCESSING, Order::STATE_COMPLETE])
                    && !$order->canCreditmemo()
                    && !$order->canShip()
                ) {
                    $order->setState(Order::STATE_CLOSED)
                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
                } elseif ($currentState === Order::STATE_PROCESSING && !$order->canShip()) {
                    $order->setState(Order::STATE_COMPLETE)
                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                } elseif ($this->isOrderPayooPayment($order)
                    &&  $order->getStatus() == Order::STATE_PROCESSING
                    && $currentState == Order::STATE_PAYMENT_REVIEW) {
                    $order->setState(Order::STATE_PROCESSING);
                }
            }
        };

        return $this;
    }

    /**
     * Check if an order is VN laneige order, using Payoo as payment method
     * @param \Magento\Sales\Model\Order $order
     * @see /app/code/Payoo/PayNow/view/frontend/web/js/view/payment/method-renderer/paynow.js
     * @return bool
     */
    protected function isOrderPayooPayment(\Magento\Sales\Model\Order $order) {
        $storeCode = $order->getStore()->getCode();
        $paymentMethod = $order->getPayment()->getMethod();

        return in_array($storeCode, self::STORES_VN) && $paymentMethod == self::PAYOO_PAYMENT_METHOD;
    }
}
