<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/3/20
 * Time: 4:30 PM
 */

namespace Eguana\GWLogistics\Plugin\Sales;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;

class OrderStatePlugin
{
    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\State $subject
     * @param $result
     * @param Order $order
     */
    public function afterCheck(\Magento\Sales\Model\ResourceModel\Order\Handler\State $subject, $result, Order $order)
    {
        $currentState = $order->getState();
        if ($currentState == Order::STATE_PROCESSING || $currentState == Order::STATE_COMPLETE) {
            if ($order->getData('sap_order_send_check') === NULL) {
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
                if (($order->getStatus() === $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING)) && $order->hasShipments()){
                    $order->setStatus('processing_with_shipment');
                }
            }
        }
        return $result;
    }

}
