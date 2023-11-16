<?php

namespace Amasty\Scroll\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Loading implements OptionSourceInterface
{
    public const NONE = 'none';

    public const AUTO = 'auto';

    public const BUTTON = 'button';

    public const COMBINED = 'combined';

    public const DEFAULT_COMBINED_VALUE = 3;

    public const COMBINED_BUTTON_AUTO = 'combined_button_auto';

    public const DEFAULT_COMBINED_BUTTON_AUTO_VALUE = 0;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NONE,
                'label' => __('None - module is disabled')
            ],
            [
                'value' => self::AUTO,
                'label' => __('Automatic - on page scroll')
            ],
            [
                'value' => self::BUTTON,
                'label' => __('Button - on button click')
            ],
            [
                'value' => self::COMBINED,
                'label' => __('Combined - automatic + button')
            ],
            [
                'value' => self::COMBINED_BUTTON_AUTO,
                'label' => __('Combined - button + automatic')
            ]
        ];
    }
}
