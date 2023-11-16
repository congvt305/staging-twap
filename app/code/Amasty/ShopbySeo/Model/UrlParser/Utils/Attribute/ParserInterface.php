<?php

declare(strict_types=1);

namespace Amasty\ShopbySeo\Model\UrlParser\Utils\Attribute;

interface ParserInterface
{
    /**
     * Parse prepared aliases and update request
     *
     * @param array $aliases
     * @param string $seoPart
     * @return array
     */
    public function parse(array $aliases, string $seoPart): array;
}
