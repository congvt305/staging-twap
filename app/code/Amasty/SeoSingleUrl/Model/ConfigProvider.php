<?php

declare(strict_types=1);

namespace Amasty\SeoSingleUrl\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    private const PRODUCT_URL_TYPE = 'general/product_url_type';
    private const PRODUCT_USE_CATEGORIES = 'general/product_use_categories';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_seourl/';

    public function isProductUseCategories(?int $storeId = null): ?bool
    {
        $result = $this->getValue(self::PRODUCT_USE_CATEGORIES, $storeId);

        return $result === null ?: (bool) $result;
    }

    public function getProductUrlType(?int $storeId = null): ?string
    {
        return $this->getValue(self::PRODUCT_URL_TYPE, $storeId);
    }
}
