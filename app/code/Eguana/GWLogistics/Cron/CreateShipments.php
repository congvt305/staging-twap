<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/19/20
 * Time: 12:58 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Cron;

use Eguana\GWLogistics\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Eguana\GWLogistics\Model\Gateway\Command\CreateShipmentCommandFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CreateShipments
 * @package Eguana\GWLogistics\Model\Cron
 */
class CreateShipments
{
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Command\CreateShipmentCommandFactory
     */
    private $createShipmentCommandFactory;
    private $ordersToShip;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CreateShipments constructor.
     * @param Data $helper
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param reateShipmentCommandFactory $createShipmentCommandFactory
     */
    public function __construct(
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        Data $helper,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CreateShipmentCommandFactory $createShipmentCommandFactory
    ) {
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->createShipmentCommandFactory = $createShipmentCommandFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->helper->isCronEnabled() || !$this->helper->getOrderStatusToCreateShipment()) {
            return;
        }
        $this->ordersToShip = $this->findOrdersToShip();
        if (!$this->ordersToShip) {
            return;
        }
        foreach ($this->ordersToShip as $order) {
            $this->createShipmentOrder($order);
        }
    }

    private function findOrdersToShip()
    {
        $lastOrderId = (int)$this->helper->getLastOrderId();
        $orderStatuses = explode(',', $this->helper->getOrderStatusToCreateShipment());
        $enabledStores = $this->getEnabledStores();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $enabledStores, 'in')
            ->addFilter('status', $orderStatuses, 'in')
            ->addFilter('shipping_method', 'gwlogistics_CVS', 'eq')
            ->addFilter('entity_id', $lastOrderId, 'gt')
            ->create();
        $ordersToShip = $this->orderRepository->getList($searchCriteria);
        $this->logger->info('gwlogistics | cron findOrdersToShip count | '. $ordersToShip->getTotalCount());
        return $ordersToShip->getTotalCount() > 0 ? $ordersToShip->getItems() : false;
    }

    private function createShipmentOrder($order)
    {
        /** @var \Eguana\GWLogistics\Model\Gateway\Command\CreateShipmentCommand $command */
        $command = $this->createShipmentCommandFactory->create();
        $command->execute($order);
    }
    private function getEnabledStores()
    {
        $enabledStores = [];
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            if ($this->helper->isActive($store->getId())) {
                $enabledStores[] = $store->getId();
            }
        }
        return $enabledStores;
    }
}
