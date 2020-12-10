<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 6:53 PM
 */
namespace Eguana\EventReservation\Model\ResourceModel\Event\Relation\Store;

use Eguana\EventReservation\Model\ResourceModel\Event;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Used to read stores handle
 *
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Event
     */
    private $resourceEvent;

    /**
     * @param MetadataPool $metadataPool
     * @param Event $resourceEvent
     */
    public function __construct(
        MetadataPool $metadataPool,
        Event $resourceEvent
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceEvent = $resourceEvent;
    }

    /**
     * Execute method
     *
     * @param object $entity
     * @param array $arguments
     * @return bool|object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }
        return $entity;
    }
}
