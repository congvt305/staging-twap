<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 15/7/20
 * Time: 7:40 PM
 */
namespace Eguana\StoreLocator\Model\ResourceModel\StoreInfo\Relation\Store;

use Eguana\StoreLocator\Api\Data\StoreInfoInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var StoreInfo
     */
    protected $resourceEvent;

    /**
     * @param MetadataPool $metadataPool
     * @param StoreInfo $resourceEvent
     */
    public function __construct(
        MetadataPool $metadataPool,
        StoreInfo $resourceEvent
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceEvent = $resourceEvent;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(StoreInfoInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldStores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
        $newStores = $entity->getStores();
        if (!($newStores === null)) {
            $newStores = (array)$entity->getStores();
            $table = $this->resourceEvent->getTable('eguana_storelocator_store');

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
                        'store_id' => (int)$storeId,
                    ];
                }
                $connection->insertMultiple($table, $data);
            }
        }
        return $entity;
    }
}
