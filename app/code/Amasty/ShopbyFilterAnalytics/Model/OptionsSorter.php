<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model;

use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UnionModel;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UnionModelFactory;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UpdatePosition;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\DB\Select;

class OptionsSorter
{
    /**
     * @var CollectionFactory
     */
    private $optionsCollectionFactory;

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
        CollectionFactory $optionsCollectionFactory,
        UnionModelFactory $modelFactory,
        DateConverter $dateConverter,
        UpdatePosition $updatePosition
    ) {
        $this->optionsCollectionFactory = $optionsCollectionFactory;
        $this->modelFactory = $modelFactory;
        $this->dateConverter = $dateConverter;
        $this->updatePosition = $updatePosition;
    }

    /**
     * @param int[] $attributeIds
     * @param int[] $excludedAttributeIds
     * @param array $filters
     */
    public function sortOptionsOfAttributes(
        array $attributeIds = [],
        array $excludedAttributeIds = [],
        array $filters = []
    ): void {
        /** @var UnionModel $unionModel */
        $unionModel = $this->modelFactory->create();

        if (isset($filters[UnionModel::DATE])) {
            $unionModel->dateFilter($this->dateConverter->prepareDateFilter($filters[UnionModel::DATE]));
        }
        $optionsCollection = $this->getOptionsCollection($unionModel);
        if ($attributeIds) {
            $optionsCollection->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds]);
        } elseif ($excludedAttributeIds) {
            $optionsCollection->addFieldToFilter('main_table.attribute_id', ['nin' => $excludedAttributeIds]);
        }

        $position = 0;
        $attributeId = '0';
        $changedAttributeIds = [];

        foreach ($optionsCollection->getData() as &$optionRow) {
            if ($optionRow['attribute_id'] !== $attributeId) {
                $attributeId = $optionRow['attribute_id'];
                $position = 0;
                $changedAttributeIds[] = (int) $attributeId;
            }

            $this->updatePosition->updateOption((int) $optionRow['option_id'], $position++);
        }

        $this->updatePosition->changeAttributesSortBy($changedAttributeIds);
    }

    /**
     * @param UnionModel $unionModel
     *
     * @return Collection
     */
    private function getOptionsCollection(UnionModel $unionModel): Collection
    {
        /** @var Collection $optionsCollection */
        $optionsCollection = $this->optionsCollectionFactory->create();
        $select = $optionsCollection->getSelect();
        $select->joinLeft(
            ['analytic' => $unionModel->getSelect()],
            'analytic.option_id = main_table.option_id',
            []
        );
        $select->group(['option_id', 'attribute_id', 'sort_order']);
        $select->order(['attribute_id', 'counter DESC', 'sort_order']);

        $select->reset(Select::COLUMNS);
        $select->columns(['option_id', 'attribute_id', 'counter' => 'SUM(analytic.counter)']);

        return $optionsCollection;
    }
}
