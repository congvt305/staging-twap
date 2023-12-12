<?php
declare(strict_types=1);

namespace Sapt\Catalog\Plugin\Catalog\Block;

class Toolbar
{

    /**
    * Plugin
    *
    * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
    * @param \Closure $proceed
    * @param \Magento\Framework\Data\Collection $collection
    * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
    */
    public function afterSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $result
    ){
        $currentOrder = $subject->getCurrentOrder();
        if ($currentOrder) {
            if ($currentOrder == 'high_to_low') {
                $result->getCollection()->setOrder('price', 'desc');
            } elseif ($currentOrder == 'low_to_high') {
                $result->getCollection()->setOrder('price', 'asc');
            }
        } else {
            $result->getCollection()->getSelect()->reset('order');
            $result->getCollection()->setOrder('price', 'asc');
        }
        return $result;
    }
}
