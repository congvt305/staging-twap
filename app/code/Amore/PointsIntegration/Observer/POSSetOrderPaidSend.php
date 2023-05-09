<?php

namespace Amore\PointsIntegration\Observer;

use Amore\PointsIntegration\Logger\Logger as PointsLogger;
use Amore\PointsIntegration\Model\PointUpdate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
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

    /**
     * @var PointUpdate
     */
    private $pointUpdate;

    /**
     * @var PointsLogger
     */
    private $logger;

    /**
     * @param PointConfig $pointConfig
     * @param OrderRepository $orderRepository
     * @param PointUpdate $pointUpdate
     * @param PointsLogger $logger
     */
    public function __construct(
        PointConfig $pointConfig,
        OrderRepository $orderRepository,
        PointUpdate $pointUpdate,
        PointsLogger $logger
    ) {
        $this->pointConfig = $pointConfig;
        $this->orderRepository = $orderRepository;
        $this->pointUpdate = $pointUpdate;
        $this->logger = $logger;
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

        $pointAmount = $order->getData('am_spent_reward_points') ?: 0;
        if ($pointAmount && $order->getCustomerId()) {
            try {
                $this->pointUpdate->pointUpdate($order, $pointAmount, PointUpdate::POINT_REDEEM,PointUpdate::POINT_REASON_PURCHASE);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }
}
