<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/7/20
 * Time: 4:00 PM
 */
namespace Eguana\VideoBoard\Model\ResourceModel\VideoBoard\Relation\Store;

use Eguana\VideoBoard\Api\Data\VideoBoardInterface;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard;
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
     * @var VideoBoard
     */
    private $resourceEvent;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MetadataPool $metadataPool
     * @param VideoBoard $resourceEvent
     * @param LoggerInterface $logger
     */
    public function __construct(
        MetadataPool $metadataPool,
        VideoBoard $resourceEvent,
        LoggerInterface $logger
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceEvent = $resourceEvent;
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
            $entityMetadata = $this->metadataPool->getMetadata(VideoBoardInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $connection = $entityMetadata->getEntityConnection();

            $oldStores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
            $newStores = (array)$entity->getStores();

            $table = $this->resourceEvent->getTable('eguana_video_board_store');

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
