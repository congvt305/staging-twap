<?php

namespace Amore\PointsIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amore\PointsIntegration\Model\Source\Config as PointConfig;
use Magento\Sales\Model\Order;
use \Magento\Sales\Model\OrderRepository;
use \Magento\Framework\Exception\CouldNotSaveException;

class POSSetOrderCancelSend implements ObserverInterface
{
    /**
     * @var PointConfig
     */
    protected $pointConfig;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct(
        PointConfig $pointConfig,
        OrderRepository $orderRepository
    )
    {
        $this->pointConfig = $pointConfig;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     * @throws CouldNotSaveException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Order $order
         */
        $order = $observer->getEvent()->getOrder();

        $moduleActive = $this->pointConfig->getActive($order->getStore()->getWebsiteId());
        $cancelledOrderToPos = $this->pointConfig->getPosCancelledOrderActive($order->getStore()->getWebsiteId());
        if ($moduleActive & $cancelledOrderToPos) {
            if (($order->getState() == 'canceled' && $order->getStatus() == 'canceled') &&
                !$order->getData('pos_order_cancel_sent') &&
                ($order->getData('pos_order_paid_sent') || $order->getData('pos_order_paid_send'))) {
                try {
                    $order->setData('pos_order_cancel_send', true);
                    $this->orderRepository->save($order);
                } catch (\Exception $exception) {
                    throw new CouldNotSaveException(__("Order can not be saved"));
                }
            }
        }
    }
}
