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
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
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
     * @var \Amore\PointsIntegration\Model\PosOrderData
     */
    private $posOrderData;
    /**
     * @var \Amore\PointsIntegration\Model\Connection\Request
     */
    private $request;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Config
     */
    private $PointsIntegrationConfig;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * OrderProcessingToComplete constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Eguana\ChangeStatus\Model\Source\Config $config
     * @param \Eguana\ChangeStatus\Model\GetCompletedOrders $completedOrders
     * @param OrderRepositoryInterface $orderRepository
     * @param \Amore\PointsIntegration\Model\PosOrderData $posOrderData
     * @param \Amore\PointsIntegration\Model\Connection\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param Config $PointsIntegrationConfig
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Eguana\ChangeStatus\Model\Source\Config $config,
        \Eguana\ChangeStatus\Model\GetCompletedOrders $completedOrders,
        OrderRepositoryInterface $orderRepository,
        \Amore\PointsIntegration\Model\PosOrderData $posOrderData,
        \Amore\PointsIntegration\Model\Connection\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        Config $PointsIntegrationConfig,
        Logger $logger
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->completedOrders = $completedOrders;
        $this->orderRepository = $orderRepository;
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->PointsIntegrationConfig = $PointsIntegrationConfig;
        $this->logger = $logger;
    }

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
                        $this->posOrderSend($order);
                    } catch (\Exception $exception) {
                        $this->logger->info($exception->getMessage());
                    }
                }
            }
        }
    }
}
