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
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $config;

    /**
     * CustomState constructor.
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     */
    public function __construct(
        \Amore\PointsIntegration\Model\Source\Config $config
    ) {
        $this->config = $config;
    }

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
                    if ($this->posOrderActiveCheck($order->getStore()->getWebsiteId())) {
                        if (!$order->getData('pos_order_send_check')) {
                            $order->setState(Order::STATE_COMPLETE)->setStatus($order->getStatus());
                        }
                    } else {
                        $order->setState(Order::STATE_COMPLETE)
                            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                    }
                }
            }
        }

        return $this;
    }

    public function posOrderActiveCheck($websiteId)
    {
        $pointsIntegrationActive = $this->config->getActive($websiteId);
        $posOrderActive = $this->config->getPosOrderActive($websiteId);

        return ($pointsIntegrationActive && $posOrderActive);
    }
}
