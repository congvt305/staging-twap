<?php

namespace Amasty\SeoRichData\Model\Source\Category;

use Magento\Framework\Option\ArrayInterface;

class Description implements ArrayInterface
{
    public const NONE = 0;

    public const CATEGORY_DESCRIPTION = 1;
    public const META_DESCRIPTION = 2;

    public function toOptionArray()
    {
        return [
            self::NONE => __('None'),
            self::CATEGORY_DESCRIPTION => __('Category Full Description'),
            self::META_DESCRIPTION => __('Page Meta Description'),
        ];
    }
}
