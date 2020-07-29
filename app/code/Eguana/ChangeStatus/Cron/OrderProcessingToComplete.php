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

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Eguana\ChangeStatus\Model\Source\Config $config,
        \Eguana\ChangeStatus\Model\GetCompletedOrders $completedOrders,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->completedOrders = $completedOrders;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $isActive = $this->config->getChangeOrderStatusActive($store->getId());

            if ($isActive) {
                $orderList = $this->completedOrders->getCompletedOrder();

                foreach ($orderList as $order) {
                    $order->setStatus('complete');
                    $this->orderRepository->save($order);
                }
            }
        }
    }
}
