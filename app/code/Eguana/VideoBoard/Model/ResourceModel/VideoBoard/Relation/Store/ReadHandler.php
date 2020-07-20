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

use Eguana\VideoBoard\Model\ResourceModel\VideoBoard;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * This class is used to read stores handle
 *
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Event
     */
    private $resourceEvent;

    /**
     * @param VideoBoard $resourceEvent
     */
    public function __construct(
        VideoBoard $resourceEvent
    ) {
        $this->resourceEvent = $resourceEvent;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
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
