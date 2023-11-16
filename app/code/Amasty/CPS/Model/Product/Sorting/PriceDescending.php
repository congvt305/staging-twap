<?php

namespace Amasty\CPS\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class PriceDescending extends PriceAscending implements SortInterface
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Price: Descending');
    }

    /**
     * @return string
     */
    protected function getOrder()
    {
        return $this->descOrder();
    }
}
