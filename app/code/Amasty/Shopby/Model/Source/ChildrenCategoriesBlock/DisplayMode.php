<?php

namespace Amasty\Shopby\Model\Source\ChildrenCategoriesBlock;

class DisplayMode
{
    public const DISABLED = 0;
    public const IMAGES = 1;
    public const LABELS = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $optionValue => $optionLabel) {
            $options[] = [
                'value' => $optionValue,
                'label' => $optionLabel
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::DISABLED => __('Disabled'),
            self::IMAGES => __('Category Thumbnail Images'),
            self::LABELS => __('Category Names Without Images')
        ];
    }
}
