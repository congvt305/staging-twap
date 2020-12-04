<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 7:03 PM
 */
namespace Eguana\EventReservation\Model\ResourceModel\Event\Relation\Store;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Model\ResourceModel\Event;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to save store view ids
 *
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param MetadataPool $metadataPool
     * @param Event $resourceEvent
     */
    public function __construct(
        LoggerInterface $logger,
        MetadataPool $metadataPool,
        Event $resourceEvent
    ) {
        $this->logger           = $logger;
        $this->metadataPool     = $metadataPool;
        $this->resourceEvent    = $resourceEvent;
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
        try {
            $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $connection = $entityMetadata->getEntityConnection();

            $oldStores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
            $newStores = (array)$entity->getStores();
            if (empty($newStores)) {
                $newStores = (array)$entity->getStoreId();
            }

            $table = $this->resourceEvent->getTable('eguana_event_reservation_store');

            $delete = array_diff($oldStores, $newStores);
            if ($delete) {
                $where = [
                    $linkField . ' = ?' => (int)$entity->getData($linkField),
                    'store_id IN (?)' => $delete,
                ];
                $connection->delete($table, $where);
            }

            $insert = array_diff($newStores, $oldStores);
            if ($insert) {
                $data = [];
                foreach ($insert as $storeId) {
                    $data[] = [
                        $linkField => (int)$entity->getData($linkField),
                        'store_id' => (int)$storeId
                    ];
                }
                $connection->insertMultiple($table, $data);
            }

        } catch (\Exception $e) {
            $this->logger->info('Error while saving store:', $e->getMessage());
        }

        return $entity;
    }
}
