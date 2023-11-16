<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     * @var string '{section}/'
     */
    protected $pathPrefix = 'amshopbyfilteranalytics/';

    /**
     * Is analytics gathering enabled.
     *
     * @return bool
     */
    public function isAnalyticsEnabled(): bool
    {
        return $this->isSetGlobalFlag('analytic/enabled');
    }

    /**
     * Options items limit per attribute item.
     *     null - unlimited
     *     0 or less - disabled
     *
     * @return ?int
     */
    public function getOptionsLimit(): ?int
    {
        $limit = $this->getGlobalValue('analytic/options_per_attribute');
        
        if ($limit === '' || $limit === null) {
            return null;
        }

        return (int) $limit;
    }
}
