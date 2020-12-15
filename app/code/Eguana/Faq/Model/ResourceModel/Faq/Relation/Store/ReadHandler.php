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

use Eguana\Faq\Model\ResourceModel\Faq;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * Eguana\Faq\Model\ResourceModel\Faq\Relation\Store
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Faq
     */
    private $resourceFaq;

    /**
     * @param Faq $resourceFaq
     */
    public function __construct(
        Faq $resourceFaq
    ) {
        $this->resourceFaq = $resourceFaq;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceFaq->lookupStoreIds((int)$entity->getId());
            $categories = $this->resourceFaq->lookCategoryIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
            $entity->setData('category', $categories);
            $entity->setData('stores', $stores);
        }
        return $entity;
    }
}
