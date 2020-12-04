<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/7/20
 * Time: 4:00 PM
 */
namespace Eguana\NewsBoard\Model\ResourceModel\News\Relation\Store;

use Eguana\NewsBoard\Api\Data\NewsInterface;
use Eguana\NewsBoard\Model\ResourceModel\News;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Psr\Log\LoggerInterface;

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
     * @var News
     */
    private $resourceEvent;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MetadataPool $metadataPool
     * @param News $resourceEvent
     * @param LoggerInterface $logger
     */
    public function __construct(
        MetadataPool $metadataPool,
        News $resourceEvent,
        LoggerInterface $logger
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceEvent = $resourceEvent;
        $this->logger = $logger;
    }

    /**
     * Execute method
     *
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        $result = '';
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $connection = $entityMetadata->getEntityConnection();

            $oldStores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
            $newStores = (array)$entity->getStores();
            $newCategories = (array)$entity->getData('category');

            $table = $this->resourceEvent->getTable('eguana_news_store');

            $delete = $oldStores;
            if ($delete) {
                $where = [
                    $linkField . ' = ?' => (int)$entity->getData($linkField),
                    'store_id IN (?)' => $delete,
                ];
                $connection->delete($table, $where);
            }
            $insert = $newStores;
            if ($insert) {
                $data = [];
                $index = 0;
                foreach ($insert as $storeId) {
                    $data[] = [
                        $linkField => (int)$entity->getData($linkField),
                        'store_id' => (int)$storeId,
                        'category' => $newCategories[$index],
                    ];
                    $index++;
                }
                $connection->insertMultiple($table, $data);
            }
            return $entity;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }
}
