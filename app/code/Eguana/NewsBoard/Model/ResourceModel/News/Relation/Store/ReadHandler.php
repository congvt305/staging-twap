<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 4:00 PM
 */
namespace Eguana\NewsBoard\Model\ResourceModel\News\Relation\Store;

use Eguana\NewsBoard\Model\ResourceModel\News;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * This class is used to read stores handle
 *
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var News
     */
    private $resourceEvent;

    /**
     * @param News $resourceEvent
     */
    public function __construct(
        News $resourceEvent
    ) {
        $this->resourceEvent = $resourceEvent;
    }

    /**
     * Execute method
     *
     * @param object $entity
     * @param array $arguments
     * @return bool|object
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceEvent->lookupStoreIds((int)$entity->getId());
            $categories = $this->resourceEvent->lookCategoryIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
            $entity->setData('category', $categories);
            $entity->setData('stores', $stores);
        }

        return $entity;
    }
}
