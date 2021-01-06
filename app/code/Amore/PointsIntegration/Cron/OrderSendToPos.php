<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-04
 * Time: 오후 2:54
 */

namespace Amore\PointsIntegration\Cron;


use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class OrderSendToPos
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $config;
    /**
     * @var \Amore\PointsIntegration\Model\GetOrdersToSendPos
     */
    private $ordersToSendPos;
    /**
     * @var \Amore\PointsIntegration\Model\PosOrderSender
     */
    private $posOrderSender;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * CompletedOrderToPosCron constructor.
     * @param StoreManagerInterface $storeManagerInterface
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     * @param \Amore\PointsIntegration\Model\GetOrdersToSendPos $ordersToSendPos
     * @param \Amore\PointsIntegration\Model\PosOrderSender $posOrderSender
     * @param DateTime $dateTime
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        \Amore\PointsIntegration\Model\Source\Config $config,
        \Amore\PointsIntegration\Model\GetOrdersToSendPos $ordersToSendPos,
        \Amore\PointsIntegration\Model\PosOrderSender $posOrderSender,
        DateTime $dateTime
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->ordersToSendPos = $ordersToSendPos;
        $this->posOrderSender = $posOrderSender;
        $this->dateTime = $dateTime;
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . sprintf('/var/log/test_%s.log',date('Ymd')));
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $cronActive = $this->config->getCronActive($store->getWebsiteId());

            if ($cronActive) {
                $orderList = $this->ordersToSendPos->getOrders($store->getId());
                /** @var \Magento\Sales\Model\Order $order */
                foreach ($orderList as $order) {
                    if ($this->dateCheck($order)) {
                        $logger->info($order->getStoreId());
                        $logger->info($order->getIncrementId());
//                        $this->posOrderSender->posOrderSend($order);

                    }
                }
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function dateCheck($order)
    {
        $result = false;
        if ($order->getStatus() == 'complete') {
            $updatedAt = $order->getUpdatedAt();
            $websiteId = $order->getStore()->getWebsiteId();
            $dateAfterToSendPos = is_null($this->config->getDaysForCompletedOrder($websiteId)) ? 7 : $this->config->getDaysForCompletedOrder($websiteId);

            $availableDateToSend = $this->dateTime->date('Y-m-d H:i:s', strtotime($updatedAt . '+' . $dateAfterToSendPos . 'days'));
            $currentDate = $this->dateTime->date();
            if ($availableDateToSend < $currentDate) {
                $result = true;
            }
        }
        return $result;
    }
}
