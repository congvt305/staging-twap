<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 7/16/20
 * Time: 3:09 AM
 */

namespace Eguana\Magazine\Model\ResourceModel\Magazine\Relation\Store;

use Eguana\Magazine\Model\ResourceModel\Magazine;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/** This class is used or read store
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Magazine
     */
    private $resourceMagazine;

    /**
     * @param Magazine $resourceMagazine
     */
    public function __construct(
        Magazine $resourceMagazine
    ) {
        $this->resourceMagazine = $resourceMagazine;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return bool|object
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceMagazine->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
            $entity->setData('stores', $stores);
        }
        return $entity;
    }
}
