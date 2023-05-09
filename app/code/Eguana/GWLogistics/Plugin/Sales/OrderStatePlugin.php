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
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(\Magento\Sales\Model\OrderFactory $orderFactory)
    {
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Handler\State $subject
     * @param $result
     * @param Order $order
     */
    public function afterCheck(\Magento\Sales\Model\ResourceModel\Order\Handler\State $subject, $result, Order $order)
    {
        if (!$order->getId()) {
            return $result;
        }
        $currentState = $order->getState();
        if ($currentState == Order::STATE_PROCESSING || $currentState == Order::STATE_COMPLETE) {
            if ($order->getData('sap_order_send_check') === NULL) {
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
                $hasShipments = $order->hasShipments() || $this->orderFactory->create()->load($order->getId())->hasShipments();
                if (($order->getStatus() === $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
                    && ($hasShipments || $order->getData('sent_to_ninjavan'))){
                    $order->setStatus('processing_with_shipment');
                }
            }
        }
        return $result;
    }

}
