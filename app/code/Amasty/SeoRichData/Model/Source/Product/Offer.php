<?php

namespace Amasty\SeoRichData\Model\Source\Product;

use Magento\Framework\Option\ArrayInterface;

class Offer implements ArrayInterface
{
    public const CONFIGURABLE = 0;
    public const LIST_OF_SIMPLES = 1;
    public const AGGREGATE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::CONFIGURABLE => __('Main Offer'),
            self::LIST_OF_SIMPLES => __('List of Associated Products Offers'),
            self::AGGREGATE => __('Aggregate Offer'),
        ];
    }
}
