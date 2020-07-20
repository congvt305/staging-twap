<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 7/16/20
 * Time: 3:12 AM
 */

namespace Eguana\Magazine\Model\ResourceModel\Magazine\Relation\Store;

use Eguana\Magazine\Api\Data\MagazineInterface;
use Eguana\Magazine\Model\ResourceModel\Magazine;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Psr\Log\LoggerInterface;

/**
 * This calss is used to save store
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Magazine
     */
    private $resourceMagazine;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MetadataPool $metadataPool
     * @param Magazine $resourceMagazine
     * @param LoggerInterface $logger
     */
    public function __construct(
        MetadataPool $metadataPool,
        Magazine $resourceMagazine,
        LoggerInterface $logger
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceEvent = $resourceMagazine;
        $this->logger = $logger;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(MagazineInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $connection = $entityMetadata->getEntityConnection();

            $oldStores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
            $newStores = (array)$entity->getStores();

            $table = $this->resourceEvent->getTable('eguana_magazine_store');

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
            return $entity;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
}
