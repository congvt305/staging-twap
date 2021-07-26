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

namespace Amore\GcrmDataExport\Model\Export\Quote;

use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\ImportExport\Model\Export\Factory as CollectionFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection;
use Amore\GcrmDataExport\Model\QuoteColumnsInterface;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Class for getting attribute colelction
 * Class AttributeCollectionProvider
 */
class AttributeCollectionProvider
{
    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

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
     * @param QuoteCollectionFactory $quoteCollectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory,
        QuoteCollectionFactory $quoteCollectionFactory
    ) {
        $this->collection = $collectionFactory->create(Collection::class);
        $this->quoteCollectionFactory = $quoteCollectionFactory;
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
            $incrementIdAttribute->setId(QuoteColumnsInterface::QUOTE_ENTITY_ID);
            $incrementIdAttribute->setBackendType('varchar');
            $incrementIdAttribute->setDefaultFrontendLabel(QuoteColumnsInterface::QUOTE_ENTITY_ID);
            $incrementIdAttribute->setAttributeCode(QuoteColumnsInterface::QUOTE_ENTITY_ID);
            $this->collection->addItem($incrementIdAttribute);
        }
        return $this->collection;
    }
}
