<?php

namespace Amasty\Scroll\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BackToTopMobile implements OptionSourceInterface
{
    public const ARROW = 'arrow';

    public const TEXT = 'text';

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
            ]
        ];
    }
}
