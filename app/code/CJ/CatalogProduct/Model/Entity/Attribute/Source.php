<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CJ\CatalogProduct\Model\Entity\Attribute;

/**
 * @api
 * @since 100.0.2
 */
class Source extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Option values
     */
    const VALUE_STABLE = 0;
    const VALUE_UP = 1;
    const VALUE_DOWN = 2;

    public function getAllOptions()
    {
        $_options = [
            ['label' => __('Rank Stable'), 'value' => self::VALUE_STABLE],
            ['label' => __('Rank Up'), 'value' => self::VALUE_UP],
            ['label' => __('Rank Down'), 'value' => self::VALUE_DOWN]
        ];
        return $_options;
    }
}
