<?php

namespace CJ\LineShopping\Model\Export;

class CategoryAdapter
{
    const TW_SULWHASOO_WEBSITE_CODE = 'base';
    const TW_LANEIGE_WEBSITE_CODE = 'tw_lageige_website';
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
        $dummyCategory = $this->addDummyCategory($website->getCode());
        return array_merge($listCategory, $dummyCategory);
    }

    /**
     * @param $websiteCode
     * @return array
     */
    protected function addDummyCategory($websiteCode): array
    {
        $data = [];
        switch ($websiteCode) {
            case self::TW_SULWHASOO_WEBSITE_CODE:
                $data['category_title'] = 'SULWHASOO';
                $data['category_value'] = '01_SULWHASOO';
                $data['category_value_parent'] = '0';
                $data['category_flag'] = '0';
                break;
            case self::TW_LANEIGE_WEBSITE_CODE:
                $data['category_title'] = 'LANEIGE';
                $data['category_value'] = '01_LANEIGE';
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
