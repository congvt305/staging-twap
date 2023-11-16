<?php

namespace Amasty\CrossLinks\Model\Source;

class TargetType implements \Magento\Framework\Option\ArrayInterface
{
    public const TYPE_SELF = '_self';
    public const TYPE_BLANK = '_blank';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_SELF, 'label' => __('Self')],
            ['value' => self::TYPE_BLANK, 'label' => __('Blank')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::TYPE_SELF => __('Self'), self::TYPE_BLANK => __('Blank')];
    }
}
