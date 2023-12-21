<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UnionModel;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UnionModelFactory;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UpdatePosition;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\DB\Select;

class AttributeSorter
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceModel\UnionModelFactory
     */
    private $modelFactory;

    /**
     * @var DateConverter
     */
    private $dateConverter;

    /**
     * @var UpdatePosition
     */
    private $updatePosition;

    public function __construct(
        CollectionFactory $collectionFactory,
        UnionModelFactory $modelFactory,
        DateConverter $dateConverter,
        UpdatePosition $updatePosition
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->modelFactory = $modelFactory;
        $this->dateConverter = $dateConverter;
        $this->updatePosition = $updatePosition;
    }

    /**
     * Change position of all filterable attributes.
     *
     * @param array $filters
     */
    public function sorAllAttributes(array $filters = []): void
    {
        /** @var UnionModel $unionModel */
        $unionModel = $this->modelFactory->create();
        $unionModel->addAttributeColumn();

        if (isset($filters[UnionModel::DATE])) {
            $unionModel->dateFilter($this->dateConverter->prepareDateFilter($filters[UnionModel::DATE]));
        }
        $attributeCollection = $this->getAttributeCollection($unionModel);

        $position = 0;

        foreach ($attributeCollection->getData() as &$attributeRow) {
            $this->updatePosition->updateAttribute(
                (int) $attributeRow['attribute_id'],
                $position++,
                isset($attributeRow['setting_id']) ? (int) $attributeRow['setting_id'] : null
            );
        }
    }

    /**
     * @param UnionModel $unionModel
     *
     * @return Collection
     */
    private function getAttributeCollection(UnionModel $unionModel): Collection
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addIsFilterableFilter();
        $select = $collection->getSelect();
        $select->reset(Select::COLUMNS);
        $select->columns(['attribute_id']);
        $select->joinLeft(
            ['analytic' => $unionModel->getSelect()],
            'analytic.attribute_id = main_table.attribute_id',
            ['counter' => 'SUM(analytic.counter)']
        );

        $select->joinLeft(
            ['pos' => $this->updatePosition->getFilterTable()],
            'pos.attribute_code = main_table.attribute_code',
            ['setting_id']
        );

        $select->group(['attribute_id', 'position']);
        $select->order(['counter DESC', 'position']);

        return $collection;
    }
}
