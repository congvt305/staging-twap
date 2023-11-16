<?php

namespace Amasty\CPS\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

interface SortInterface
{
    /**
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection);

    /**
     * @return string
     */
    public function getLabel();
}
