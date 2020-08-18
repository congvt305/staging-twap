<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-18
 * Time: 오후 4:49
 */

namespace Eguana\PendingCanceler\Model;

use Magento\Store\Model\StoreManagerInterface;

class PendingOrderCanceler
{
    /**
     * @var GetPendingOrders
     */
    private $getPendingOrders;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var Source\Config
     */
    private $config;
    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    private $orderManagement;

    /**
     * PendingOrderCanceler constructor.
     * @param GetPendingOrders $getPendingOrders
     * @param StoreManagerInterface $storeManagerInterface
     * @param Source\Config $config
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     */
    public function __construct(
        GetPendingOrders $getPendingOrders,
        StoreManagerInterface $storeManagerInterface,
        \Eguana\PendingCanceler\Model\Source\Config $config,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        $this->getPendingOrders = $getPendingOrders;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->orderManagement = $orderManagement;
    }

    public function pendingCanceler()
    {
        $stores = $this->storeManagerInterface->getStores();
        foreach ($stores as $store) {
            $isActive = $this->config->getActive($store->getId());
            if ($isActive) {
                $pendingOrderList = $this->getPendingOrders->getPendingOrders($store->getId())->getItems();
                foreach ($pendingOrderList as $pendingOrder) {
                    $this->orderManagement->cancel($pendingOrder->getEntityId());
                }
            }
        }
    }
}
