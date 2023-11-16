<?php

namespace Amasty\Scroll\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BackToTopDesktop implements OptionSourceInterface
{
    public const ARROW = 'arrow';

    public const TEXT = 'text';

    public const EDGE = 'edge';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ARROW,
                'label' => __('Arrow only')
            ],
            [
                'value' => self::TEXT,
                'label' => __('Arrow and text')
            ],
            [
                'value' => self::EDGE,
                'label' => __('Arrow and text (page edge)')
            ]
        ];
    }
}
