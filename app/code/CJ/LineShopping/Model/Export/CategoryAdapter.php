<?php

namespace CJ\LineShopping\Model\Export;

use Magento\Catalog\Model\Product;

class CategoryAdapter
{
    /**
     * @param $categories
     * @param $parentId
     * @return array
     */
    public function export($categories, $parentId): array
    {
        $listCategory = [];
        foreach ($categories as $category) {
            $data['category_title'] = $category->getName();
            $data['category_value'] = $category->getEntityId();
            $data['category_value_parent'] = $category->getParentId() != $parentId ? $category->getParentId() : '0';
            $data['category_flag'] = '1';
            $listCategory[] = $data;
        }
        return $listCategory;
    }
}
