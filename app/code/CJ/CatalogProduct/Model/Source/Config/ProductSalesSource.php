<?php

namespace CJ\CatalogProduct\Model\Source\Config;

/**
 * Class ProductSalesSource
 * @package CJ\CatalogProduct\Model\Source\Config
 */
class ProductSalesSource implements \Magento\Framework\Data\OptionSourceInterface
{
    const NO_USE = 0;
    const USE_ATTRIBUTE_ON_SALE = 1;
    const AUTOMATIC = 2;

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $_options = [
            ['label' => __('No use'), 'value' => self::NO_USE],
            ['label' => __('Use Product Attribute On Sale'), 'value' => self::USE_ATTRIBUTE_ON_SALE],
            ['label' => __('Automatic'), 'value' => self::AUTOMATIC]
        ];
        return $_options;
    }
}
