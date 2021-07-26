<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Model\Export\Order;

use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Amore\GcrmDataExport\Model\OrderColumnsInterface;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Class for getting attribute colelction
 * Class AttributeCollectionProvider
 */
class AttributeCollectionProvider
{
    /**
     * @var ItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * AttributeCollectionProvider constructor.
     * @param CollectionFactory $collectionFactory
     * @param AttributeFactory $attributeFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory,
        ItemCollectionFactory $itemCollectionFactory
    ) {
        $this->collection = $collectionFactory->create(Collection::class);
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * This function gets attribute collection
     * @return Collection
     * @throws \Exception
     */
    public function get(): Collection
    {
        if (count($this->collection) === 0) {
            /** @var Attribute $skuAttribute */
            $incrementIdAttribute = $this->attributeFactory->create();
            $incrementIdAttribute->setId(OrderColumnsInterface::ORDER_INCREMENT_ID);
            $incrementIdAttribute->setBackendType('varchar');
            $incrementIdAttribute->setDefaultFrontendLabel(OrderColumnsInterface::ORDER_INCREMENT_ID);
            $incrementIdAttribute->setAttributeCode(OrderColumnsInterface::ORDER_INCREMENT_ID);
            $this->collection->addItem($incrementIdAttribute);
        }
        return $this->collection;
    }
}
