<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 1:27 PM
 */

namespace Eguana\ChangeStatus\Cron;

use Amore\PointsIntegration\Logger\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderProcessingToComplete
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var \Eguana\ChangeStatus\Model\Source\Config
     */
    private $config;
    /**
     * @var \Eguana\ChangeStatus\Model\GetCompletedOrders
     */
    private $completedOrders;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Eguana\ChangeStatus\Model\Source\Config $config
     * @param \Eguana\ChangeStatus\Model\GetCompletedOrders $completedOrders
     * @param OrderRepositoryInterface $orderRepository
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Eguana\ChangeStatus\Model\Source\Config $config,
        \Eguana\ChangeStatus\Model\GetCompletedOrders $completedOrders,
        OrderRepositoryInterface $orderRepository,
        Logger $logger
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->completedOrders = $completedOrders;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * Update status order
     *
     * @return void
     */
    public function execute()
    {
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $isActive = $this->config->getChangeOrderStatusActive($store->getId());

            if ($isActive) {
                $orderList = $this->completedOrders->getCompletedOrder($store->getId());

                foreach ($orderList as $order) {
                    try {
                        $order->setStatus('complete');
                        $order->setState('complete');
                        $this->orderRepository->save($order);
                    } catch (\Exception $exception) {
                        $this->logger->info($exception->getMessage());
                    }
                }
            }
        }
    }
}
