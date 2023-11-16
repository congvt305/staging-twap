<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * @var string '{section}/'
     */
    protected $pathPrefix = 'amseotoolkit/';

    private const REDIRECTS_FOR_DELETED_PRODUCTS = 'redirect_settings/deleted_products_redirects/enabled';
    private const REDIRECT_TYPE_FOR_PRODUCTS = 'redirect_settings/deleted_products_redirects/redirect_type';
    private const REDIRECT_LIFETIME_FOR_PRODUCTS = 'redirect_settings/deleted_products_redirects/redirect_lifetime';
    private const REDIRECTS_FOR_DELETED_CATEGORIES = 'redirect_settings/deleted_categories_redirects/enabled';
    private const REDIRECT_TYPE_FOR_CATEGORIES = 'redirect_settings/deleted_categories_redirects/redirect_type';
    private const REDIRECT_LIFETIME_FOR_CATEGORIES = 'redirect_settings/deleted_categories_redirects/redirect_lifetime';
    private const HOME_REDIRECT = 'redirect_settings/home_redirect';
    private const FOUR_ZERO_FOUR_REDIRECT = 'redirect_settings/four_zero_four_redirect';

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isRedirectsForDeletedProductsEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::REDIRECTS_FOR_DELETED_PRODUCTS, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getRedirectTypeForProducts(?int $storeId = null): string
    {
        return (string)$this->getValue(self::REDIRECT_TYPE_FOR_PRODUCTS, $storeId);
    }
    
    /**
     * @param int|null $storeId
     * @return string
     */
    public function getRedirectLifetimeForProducts(?int $storeId = null): string
    {
        return (string)$this->getValue(self::REDIRECT_LIFETIME_FOR_PRODUCTS, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isRedirectsForDeletedCategoriesEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::REDIRECTS_FOR_DELETED_CATEGORIES, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getRedirectTypeForCategories(?int $storeId = null): string
    {
        return (string)$this->getValue(self::REDIRECT_TYPE_FOR_CATEGORIES, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getRedirectLifetimeForCategories(?int $storeId = null): string
    {
        return (string)$this->getValue(self::REDIRECT_LIFETIME_FOR_CATEGORIES, $storeId);
    }
    
    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isHomeRedirectEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::HOME_REDIRECT, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isRedirectFromFourZeroFourEnabled(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::FOUR_ZERO_FOUR_REDIRECT, $storeId);
    }
}
