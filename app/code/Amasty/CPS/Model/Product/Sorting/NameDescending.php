<?php

namespace Amasty\CPS\Model\Product\Sorting;

use \Magento\Catalog\Model\ResourceModel\Product\Collection;

class NameDescending extends NameAscending implements SortInterface
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return __("Name: Descending");
    }

    /**
     * @return string
     */
    protected function getOrder()
    {
        return $this->descOrder();
    }
}
