<?php

namespace Amasty\Shopby\Model\Source;

class SubcategoriesExpand implements \Magento\Framework\Option\ArrayInterface
{
    public const ALWAYS = 1;
    public const BY_CLICK = 2;
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ALWAYS,
                'label' => __('Always')
            ],
            [
                'value' => self::BY_CLICK,
                'label' => __('By Click')
            ],
        ];
    }
}
