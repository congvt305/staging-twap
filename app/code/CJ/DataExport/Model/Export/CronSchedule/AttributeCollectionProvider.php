<?php

namespace CJ\DataExport\Model\Export\CronSchedule;

/**
 * Class AttributeCollectionProvider
 */
class AttributeCollectionProvider
{
    /**
     * @var \Magento\ImportExport\Model\Export\Factory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $attributeFactory;
    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $collection;

    /**
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
    ) {
        $this->collection = $collectionFactory->create(\Magento\Framework\Data\Collection::class);
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function get(): \Magento\Framework\Data\Collection
    {
        if (count($this->collection) === 0) {
            /** @var \Magento\Eav\Model\Entity\Attribute $skuAttribute */
            $incrementIdAttribute = $this->attributeFactory->create();
            $incrementIdAttribute->setId(CronScheduleInterface::SCHEDULE_ID);
            $incrementIdAttribute->setBackendType('varchar');
            $incrementIdAttribute->setDefaultFrontendLabel(CronScheduleInterface::SCHEDULE_ID);
            $incrementIdAttribute->setAttributeCode(CronScheduleInterface::SCHEDULE_ID);
            $this->collection->addItem($incrementIdAttribute);
        }
        return $this->collection;
    }
}
