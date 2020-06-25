<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Model\ResourceModel\Faq\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Eguana\Faq\Api\Data\FaqInterface;
use Eguana\Faq\Model\ResourceModel\Faq;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class SaveHandler
 *
 * Eguana\Faq\Model\ResourceModel\Faq\Relation\Store
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Faq
     */
    private $resourceBlock;

    /**
     * @param MetadataPool $metadataPool
     * @param Faq $resourceBlock
     */
    public function __construct(
        MetadataPool $metadataPool,
        Faq $resourceBlock
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceBlock = $resourceBlock;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $linkField = $entityMetadata->getLinkField();
        $connection = $entityMetadata->getEntityConnection();
        $oldStores = $this->resourceBlock->lookupStoreIds((int)$entity->getId());
        $newStores = (array)$entity->getStores();
        $table = $this->resourceBlock->getTable('eguana_faq_store');

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $this->diffDelete($entity, $linkField, $delete, $connection, $table);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $this->diffInsert($entity, $insert, $linkField, $connection, $table);
        }

        return $entity;
    }

    /**
     * @param $entity
     * @param $linkField
     * @param array $delete
     * @param AdapterInterface $connection
     * @param $table
     */
    private function diffDelete($entity, $linkField, array $delete, AdapterInterface $connection, $table)
    {
        $where = [
            $linkField . ' = ?' => (int)$entity->getData($linkField),
            'store_id IN (?)' => $delete,
        ];
        $connection->delete($table, $where);
    }

    /**
     * @param $entity
     * @param array $insert
     * @param $linkField
     * @param AdapterInterface $connection
     * @param $table
     */
    private function diffInsert($entity, array $insert, $linkField, AdapterInterface $connection, $table)
    {
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
