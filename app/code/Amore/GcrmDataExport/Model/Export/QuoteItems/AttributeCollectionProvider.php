<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
declare(strict_types=1);

namespace Amore\GcrmDataExport\Model\Export\QuoteItems;

use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemsCollectionFactory;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Amore\GcrmDataExport\Model\QuoteItemsColumnsInterface;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Class for getting attribute colelction
 *
 * Class AttributeCollectionProvider
 */
class AttributeCollectionProvider
{
    /**
     * @var QuoteItemsCollectionFactory
     */
    private $quoteItemsCollectionFactory;

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
     * @param QuoteItemsCollectionFactory $quoteItemsCollectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory,
        QuoteItemsCollectionFactory $quoteItemsCollectionFactory
    ) {
        $this->collection = $collectionFactory->create(Collection::class);
        $this->quoteItemsCollectionFactory = $quoteItemsCollectionFactory;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * This function gets attribute collection
     *
     * @return Collection
     * @throws \Exception
     */
    public function get(): Collection
    {
        if (count($this->collection) === 0) {
            /** @var Attribute $skuAttribute */
            $incrementIdAttribute = $this->attributeFactory->create();
            $incrementIdAttribute->setId(QuoteItemsColumnsInterface::QUOTE_ITEM_ITEM_ID);
            $incrementIdAttribute->setBackendType('varchar');
            $incrementIdAttribute->setDefaultFrontendLabel(QuoteItemsColumnsInterface::QUOTE_ITEM_ITEM_ID);
            $incrementIdAttribute->setAttributeCode(QuoteItemsColumnsInterface::QUOTE_ITEM_ITEM_ID);
            $this->collection->addItem($incrementIdAttribute);
        }
        return $this->collection;
    }
}
