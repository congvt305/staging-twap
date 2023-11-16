<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Ui\DataProvider\AnalyticsModifier;

use Amasty\ShopbyFilterAnalytics\Model\ConfigProvider;
use Amasty\ShopbyFilterAnalytics\Model\FunctionalityManager;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics\OptionsCollection;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics\OptionsCollectionFactory;
use Magento\Framework\Api\Filter;

class OptionsProcessor
{
    /**
     * @var OptionsCollection
     */
    private $collection;

    /**
     * @var OptionsCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FunctionalityManager
     */
    private $functionalityManager;

    public function __construct(
        OptionsCollectionFactory $collectionFactory,
        ConfigProvider $configProvider,
        FunctionalityManager $functionalityManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configProvider = $configProvider;
        $this->functionalityManager = $functionalityManager;
    }

    /**
     * @param Filter $filter
     */
    public function addFilter(Filter $filter): void
    {
        $this->getCollection()->addFieldToFilter(
            $filter->getField(),
            [$filter->getConditionType() => $filter->getValue()]
        );
    }

    /**
     * @return OptionsCollection
     */
    public function getCollection(): OptionsCollection
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
            $this->collection->setOrder('attribute_id')
                ->setOrder('counter')
                ->setOrder('sort_order', OptionsCollection::SORT_ORDER_ASC)
                ->setOrder('frontend_label', OptionsCollection::SORT_ORDER_ASC);
        }

        return $this->collection;
    }

    /**
     * @param array $data
     */
    public function modifyData(array &$data): void
    {
        if (!$this->functionalityManager->isPremActive()) {
            return;
        }
        $optionsLimit = $this->configProvider->getOptionsLimit();
        if ($optionsLimit <= 0 && $optionsLimit !== null) {
            return;
        }
        $items = &$data['items'];
        $attributeIds = $this->collectAttributeIds($items);
        if (!$attributeIds) {
            return;
        }
        $collection = $this->getCollection();
        $collection->addAttributeIdsFilter($attributeIds);

        $processingId = 0;
        $rowKey = null;
        $attributeCounter = 0;

        foreach ($collection->getData() as $optionRow) {
            $currentId = (int) $optionRow['attribute_id'];
            if ($processingId !== $currentId) {
                $attributeCounter = $optionsLimit ?? 0;
                $processingId = $currentId;
                $rowKey = $this->getItemKey($items, $processingId);
            }

            if ($optionsLimit === null || $attributeCounter-- > 0) {
                $items[$rowKey]['options_data'][] = $optionRow;
            }
        }
    }

    /**
     * @param array $data
     *
     * @return int[]
     */
    private function collectAttributeIds(array &$data): array
    {
        $attributeIds = [];
        foreach ($data as &$row) {
            $attributeIds[] = (int) $row['attribute_id'];
        }

        return $attributeIds;
    }

    /**
     * @param array $items
     * @param int $id
     *
     * @return int|string|null
     */
    private function getItemKey(array &$items, int $id)
    {
        foreach ($items as $key => &$row) {
            if ((int) $row['attribute_id'] === $id) {
                return $key;
            }
        }

        return null;
    }
}
