<?php

namespace CJ\CatalogProduct\Model\Entity\Attribute;

/**
 * Class Source
 * @package CJ\CatalogProduct\Model\Entity\Attribute
 */
class Source extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const VALUE_STABLE = 0;
    const VALUE_UP = 1;
    const VALUE_DOWN = 2;

    /**
     * @return array[]
     */
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
