<?php

declare(strict_types=1);

namespace Amasty\Meta\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * @var string '{section}/'
     */
    protected $pathPrefix = 'ammeta/';

    private const AMMETA_PRODUCT_URL_TEMPLATE = 'product/url_template';
    private const AMMETA_AUTOMATICALLY_MODIFY_URL_KEY = 'product/automatically_modify_url_key';

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getProductTemplate(?int $storeId = null): string
    {
        $urlTemplate = $this->getValue(self::AMMETA_PRODUCT_URL_TEMPLATE, $storeId);

        return trim((string)$urlTemplate);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isAutomaticallyModifyUrlKey(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::AMMETA_AUTOMATICALLY_MODIFY_URL_KEY, $storeId);
    }
}
