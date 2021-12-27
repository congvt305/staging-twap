<?php

namespace CJ\PointRedemption\Plugin;

use CJ\PointRedemption\Setup\Patch\Data\AddIsMembershipCategoryAttribute as MembershipCategory;

class ProductUrl
{
    /**
     * Add query ?point=1 to product url in membership category
     * @param $subject
     * @param $product
     * @param $params
     * @return array
     */
    public function beforeGetUrl($subject, $product, $params = [])
    {
        $currentCategory = $product->getCategory();
        $isMembershipCategory = $currentCategory
            ? $currentCategory->getData(MembershipCategory::CATEGORY_ATTRIBUTE_CODE_IS_MEMBERSHIP)
            : null;
        if ($isMembershipCategory) {
            if (!isset($params['_query'])) {
                $params['_query'] = [];
            }
            $params['_query']['point'] = 1;
        }

        return [$product, $params];
    }
}
