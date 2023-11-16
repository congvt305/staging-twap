<?php

namespace Amasty\CPS\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class OutStockBottom extends SortAbstract implements SortInterface
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Move out of stock to bottom");
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection)
    {
        parent::sort($collection);

        $fromTables = $collection->getSelect()->getPart('from');
        if (!isset($fromTables['stock_status_index'])) {
            $this->stockHelper->addIsInStockFilterToCollection($collection);
            $fromTables = $collection->getSelect()->getPart('from');
        }

        $catalogInventoryTable = $collection->getResource()->getTable('cataloginventory_stock_status');
        if ($this->isMsiEnabled() && $fromTables['stock_status_index']['tableName'] != $catalogInventoryTable) {
            $salableColumn = 'is_salable';
        } else {
            $salableColumn = 'stock_status';
        }
        $collection->getSelect()
            ->order('stock_status_index.' . $salableColumn . ' ' . $collection::SORT_ORDER_DESC)
            ->order('e.entity_id ' . $collection::SORT_ORDER_ASC);

        return $collection;
    }

    /**
     * @return bool
     */
    private function isMsiEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }
}
