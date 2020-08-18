<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-18
 * Time: 오후 12:11
 */

namespace Amore\Sap\Model\Rma;

class Create extends \Magento\Rma\Model\Rma\Create
{
    /**
     * Get Order object
     *
     * @param null|int $orderId
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder($orderId = null)
    {
        if ($orderId === null) {
            $orderId = $this->getOrderId();
        }
        $orderId = intval($orderId);
        if ($orderId) {
            /** @var $order \Magento\Sales\Model\Order */
            $order = $this->_orderFactory->create();
            $order->load($orderId);
            $this->_order = $order;
        }

        return $this->_order;
    }
}
