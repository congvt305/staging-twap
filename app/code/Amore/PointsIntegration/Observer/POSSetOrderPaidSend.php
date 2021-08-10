<?php

namespace Amore\PointsIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Amore\PointsIntegration\Model\Source\Config as PointConfig;
use \Magento\Sales\Model\OrderRepository;
use \Magento\Framework\Exception\CouldNotSaveException;

class POSSetOrderPaidSend implements ObserverInterface
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
         * @var InvoiceInterface $invoice
         */
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $moduleActive = $this->pointConfig->getActive($order->getStore()->getWebsiteId());
        $orderToPosActive = $this->pointConfig->getPosOrderActive($order->getStore()->getWebsiteId());
        if ($moduleActive & $orderToPosActive) {
            if (!$order->getData('pos_order_paid_sent')) {
                try {
                    $order->setData('pos_order_paid_send', true);
                    $this->orderRepository->save($order);
                } catch (\Exception $exception) {
                    throw new CouldNotSaveException(__("Order can not be saved"));
                }
            }
        }
    }
}
