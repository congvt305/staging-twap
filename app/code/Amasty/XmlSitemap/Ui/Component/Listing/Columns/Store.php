<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Ui\Component\Listing\Columns;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if (array_key_exists($this->storeKey, $item) && !is_array($item[$this->storeKey])) {
            $item[$this->storeKey] = [$item[$this->storeKey]];
        }

        return parent::prepareItem($item);
    }
}
