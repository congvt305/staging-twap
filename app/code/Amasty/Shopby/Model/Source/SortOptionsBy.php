<?php

namespace Amasty\Shopby\Model\Source;

class SortOptionsBy implements \Magento\Framework\Option\ArrayInterface
{
    public const POSITION = 0;
    public const NAME = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION,
                'label' => __('Position')
            ],
            [
                'value' => self::NAME,
                'label' => __('Name')
            ],
        ];
    }
}
