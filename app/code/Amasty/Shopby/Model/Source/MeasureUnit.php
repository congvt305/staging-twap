<?php

namespace Amasty\Shopby\Model\Source;

class MeasureUnit implements \Magento\Framework\Option\ArrayInterface
{
    public const CUSTOM            = 0;
    public const CURRENCY_SYMBOL   = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CURRENCY_SYMBOL,
                'label' => __('Store Currency')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom label')
            ]
        ];
    }
}
