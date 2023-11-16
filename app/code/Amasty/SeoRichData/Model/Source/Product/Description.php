<?php

namespace Amasty\SeoRichData\Model\Source\Product;

use Magento\Framework\Option\ArrayInterface;

class Description implements ArrayInterface
{
    public const NONE = 0;

    public const SHORT_DESCRIPTION = 1;
    public const FULL_DESCRIPTION = 2;
    public const META_DESCRIPTION = 3;

    public function toOptionArray()
    {
        return [
            self::NONE => __('None'),
            self::SHORT_DESCRIPTION => __('Product Short Description'),
            self::FULL_DESCRIPTION => __('Product Full Description'),
            self::META_DESCRIPTION => __('Page Meta Description'),
        ];
    }
}
