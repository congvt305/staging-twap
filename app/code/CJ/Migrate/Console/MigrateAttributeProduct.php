<?php

namespace CJ\Migrate\Console;

/**
 * Class MigrateAttributeProduct
 * @package CJ\Migrate\Console
 */
class MigrateAttributeProduct extends AbstractMigrateData
{
    const NAME = 'cj:migrate:attrbite_product';

    /**
     * @return string
     */
    protected function getNameConsole(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return self::TYPE_PRODUCT;
    }
}
