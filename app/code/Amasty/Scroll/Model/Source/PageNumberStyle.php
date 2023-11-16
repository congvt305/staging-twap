<?php

namespace Amasty\Scroll\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PageNumberStyle implements OptionSourceInterface
{
    public const AS_BUTTON = 'button';

    public const TEXT_WITH_DIVIDER = 'divider';

    public const TEXT_WITH_BACKGROUND = 'background';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AS_BUTTON,
                'label' => __('Button')
            ],
            [
                'value' => self::TEXT_WITH_DIVIDER,
                'label' => __('Text with divider')
            ],
            [
                'value' => self::TEXT_WITH_BACKGROUND,
                'label' => __('Text with background')
            ]
        ];
    }
}
