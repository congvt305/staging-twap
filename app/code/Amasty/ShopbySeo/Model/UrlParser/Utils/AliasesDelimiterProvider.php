<?php

declare(strict_types=1);

namespace Amasty\ShopbySeo\Model\UrlParser\Utils;

use Amasty\ShopbySeo\Helper\Config;

class AliasesDelimiterProvider
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve seo options delimiter
     *
     * @return string
     */
    public function execute()
    {
        $delimiter = $this->config->getOptionSeparator() ?: '-';
        return (string) $delimiter;
    }
}
