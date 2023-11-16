<?php

declare(strict_types=1);

namespace Amasty\ShopbySeo\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    public const AMSHOPBY_SEO_REL_NOFOLLOW = 'robots/rel_nofollow';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_shopby_seo/';

    public function isEnableRelNofollow(): bool
    {
        return (bool) $this->getValue(self::AMSHOPBY_SEO_REL_NOFOLLOW);
    }
}
