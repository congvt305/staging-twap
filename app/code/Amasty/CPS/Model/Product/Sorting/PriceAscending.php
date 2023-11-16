<?php

namespace Amasty\CPS\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class PriceAscending extends SortAbstract implements SortInterface
{
    /**
     * @return string
     */
    public function getSortField()
    {
        return 'price';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Price: Ascending');
    }

    /**
     * @return string
     */
    protected function getOrder()
    {
        return $this->ascOrder();
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection)
    {
        parent::sort($collection);
        $collection->addAttributeToSelect($this->getSortField(), true);
        $collection->addOrder($this->getSortField(), $this->getOrder());
        return $collection;
    }
}
