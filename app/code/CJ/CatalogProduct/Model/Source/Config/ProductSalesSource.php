<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CJ\CatalogProduct\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @api
 * @since 100.0.2
 */
class ProductSalesSource implements OptionSourceInterface
{
    /**
     * Option values
     */
    const NO_USE = 0;
    const USE_ATTRIBUTE_ON_SALE = 1;
    const AUTOMATIC = 2;

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
