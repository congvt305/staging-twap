<?php

namespace Amasty\CPS\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class NameAscending extends SortAbstract implements SortInterface
{
    /**
     * @return string
     */
    protected function getSortField()
    {
        return 'name';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Name: Ascending');
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
