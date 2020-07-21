<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 15/7/20
 * Time: 7:39 PM
 */
namespace Eguana\StoreLocator\Model\ResourceModel\StoreInfo\Relation\Store;

use Eguana\StoreLocator\Model\ResourceModel\StoreInfo;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var StoreInfo
     */
    protected $resourceEvent;

    /**
     * @param StoreInfo $resourceEvent
     */
    public function __construct(
        StoreInfo $resourceEvent
    ) {
        $this->resourceEvent = $resourceEvent;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
            $entity->setData('stores', $stores);
        }
        return $entity;
    }
}
