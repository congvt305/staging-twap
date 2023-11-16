<?php

namespace Amasty\CPS\Plugin\ElasticSearch\SearchAdapter\Query\Builder;

class SortPlugin
{
    public const FIELD_NAME_POSITION_TEMPLATE = 'brand_position_%s';

    /**
     * @var \Amasty\ShopbyBrand\Helper\Content
     */
    private $contentHelper;

    public function __construct(
        \Amasty\ShopbyBrand\Helper\Content $contentHelper
    ) {
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     */
    public function afterGetSort($subject, $result)
    {
        if ($brand = $this->contentHelper->getCurrentBranding()) {
            foreach ($result as $sortKey => $sort) {
                $key = key($sort);
                $order = $sort[$key]['order'];
                if (strpos($key, 'category_position') === 0 || strpos($key, 'position_category') === 0) {
                     $result[$sortKey] = [
                        sprintf(self::FIELD_NAME_POSITION_TEMPLATE, $brand->getValue()) => [
                            'order' => strtolower($order)
                        ]
                     ];
                }
            }
        }

        return $result;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     */
    public function afterExecute($subject, $result)
    {
        return $this->afterGetSort($subject, $result);
    }
}
