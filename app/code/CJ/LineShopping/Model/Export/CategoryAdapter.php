<?php

namespace CJ\LineShopping\Model\Export;

use Magento\Catalog\Model\Product;

class CategoryAdapter
{
    /**
     * @param $categories
     * @param $website
     * @return array
     */
    public function export($categories): array
    {
        $listCategory = [];
        foreach ($categories as $category) {
            $data['category_title'] = $category->getName();
            $data['category_value'] = $category->getEntityId();
            $data['category_value_parent'] = $category->getParentId();
            $data['category_flag'] = '1';
            $listCategory[] = $data;
        }
        return $listCategory;
    }
}
