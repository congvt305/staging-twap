<?php
declare(strict_types=1);

namespace CJ\CatalogProduct\Plugin\Catalog\Model\Layer;

use Magento\Catalog\Model\Layer\FilterList;
use Amasty\Shopby\Helper\Category as CategoryHelper;

/**
 * Class FilterListPlugin
 */
class FilterListPlugin
{
    const MAPPING_ATTRIBUTE_FILTERS = [
        CategoryHelper::CATEGORY_FILTER_PARAM => CategoryHelper::ATTRIBUTE_CODE
    ];

    /**
     * Custom to display category's filters
     *
     * @param FilterList $subject
     * @param $result
     * @param \Magento\Catalog\Model\Layer $layer
     * @return mixed
     */
    public function afterGetFilters(FilterList $subject, $result, \Magento\Catalog\Model\Layer $layer)
    {
        $visibleFilters = $layer->getCurrentCategory()->getData('visible_filter_attributes') ?? [];
        if (!empty($visibleFilters)) {
            foreach ($result as $index => $filter) {
                $attributeCode = $filter->getRequestVar();

                if (!empty(self::MAPPING_ATTRIBUTE_FILTERS[$attributeCode])) {
                    $attributeCode = self::MAPPING_ATTRIBUTE_FILTERS[$attributeCode];
                }

                if (!in_array($attributeCode, $visibleFilters)) {
                    unset($result[$index]);
                }
            }
        }

        return $result;
    }
}
