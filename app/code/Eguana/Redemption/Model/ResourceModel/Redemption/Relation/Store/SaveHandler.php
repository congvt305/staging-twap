<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 06:00 PM
 */
namespace Eguana\Redemption\Model\ResourceModel\Redemption\Relation\Store;

use Eguana\Redemption\Api\Data\RedemptionInterface;
use Eguana\Redemption\Model\ResourceModel\Redemption;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * This class is used to save store view ids
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
     * @var Redemption
     */
    private $resourceRedemption;

    /**
     * @param MetadataPool $metadataPool
     * @param Redemption $resourceRedemption
     */
    public function __construct(
        MetadataPool $metadataPool,
        Redemption $resourceRedemption
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceRedemption = $resourceRedemption;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldStores = $this->resourceRedemption->lookupStoreIds((int)$entity->getId());
        $newStores = (array)$entity->getStores();

        $table = $this->resourceRedemption->getTable('eguana_redemption_store');

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
    }
}
