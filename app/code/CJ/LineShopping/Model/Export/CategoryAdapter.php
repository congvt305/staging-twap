<?php

namespace CJ\LineShopping\Model\Export;

class CategoryAdapter
{
    const TW_SULWHASOO_WEBSITE_NAME = 'TW Sulwhasoo Website';
    const TW_LANEIGE_WEBSITE_NAME = 'TW Laneige Website';
    /**
     * @param $categories
     * @param $parentId
     * @param $website
     * @return array
     */
    public function export($categories, $parentId, $website): array
    {
        $listCategory = [];
        foreach ($categories as $category) {
            $data['category_title'] = $category->getName();
            $data['category_value'] = $category->getEntityId();
            $data['category_value_parent'] = $category->getParentId() != $parentId ? $category->getParentId() : '0';
            $data['category_flag'] = '1';
            $listCategory[] = $data;
        }
        $dummyCategory = $this->addDummyCategory($website->getName());
        return array_merge($listCategory, $dummyCategory);
    }

    /**
     * @param $websiteName
     * @return array
     */
    protected function addDummyCategory($websiteName): array
    {
        $data = [];
        switch ($websiteName) {
            case self::TW_SULWHASOO_WEBSITE_NAME:
                $data['category_title'] = 'SULWHASOO';
                $data['category_value'] = '01_SULWHASOO';
                $data['category_value_parent'] = '0';
                $data['category_flag'] = '0';
                break;
            case self::TW_LANEIGE_WEBSITE_NAME:
                $data['category_title'] = 'LENGENIE';
                $data['category_value'] = '01_LENGENIE';
                $data['category_value_parent'] = '0';
                $data['category_flag'] = '0';
                break;
            default:
                break;
        }
        $dummyCategory[] = $data;
        return $dummyCategory;
    }
}
