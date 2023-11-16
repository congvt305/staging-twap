<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Cms\Helper\Page;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * @var string '{section}/'
     */
    protected $pathPrefix = 'amopengraphtags/';

    private const AMOPENGRAPHTAGS_PRODUCT_PAGE_ENABLED = 'product_page/enabled';
    private const AMOPENGRAPHTAGS_PRODUCT_PAGE_TITLE = 'product_page/open_graph_title';
    private const AMOPENGRAPHTAGS_PRODUCT_PAGE_DESCRIPTION = 'product_page/open_graph_description';
    private const AMOPENGRAPHTAGS_CATEGORY_PAGE_TITLE = 'category_page/open_graph_title';
    private const AMOPENGRAPHTAGS_CATEGORY_PAGE_DESCRIPTION = 'category_page/open_graph_description';
    private const AMOPENGRAPHTAGS_HOME_PAGE_ENABLED = 'cms_pages/enabled_on_home';
    private const AMOPENGRAPHTAGS_CMS_PAGES_ENABLED = 'cms_pages/enabled_on_cms';
    private const AMOPENGRAPHTAGS_CMS_PAGE_TITLE = 'cms_pages/open_graph_title';
    private const AMOPENGRAPHTAGS_CMS_PAGE_DESCRIPTION = 'cms_pages/open_graph_description';

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledOnProductPage(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::AMOPENGRAPHTAGS_PRODUCT_PAGE_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledOnHomePage(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::AMOPENGRAPHTAGS_HOME_PAGE_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledOnCmsPages(?int $storeId = null): bool
    {
        return (bool)$this->getValue(self::AMOPENGRAPHTAGS_CMS_PAGES_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getProductPageTitleAttribute(?int $storeId = null): string
    {
        return (string)$this->getValue(self::AMOPENGRAPHTAGS_PRODUCT_PAGE_TITLE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getProductPageDescriptionAttribute(?int $storeId = null): string
    {
        return (string)$this->getValue(self::AMOPENGRAPHTAGS_PRODUCT_PAGE_DESCRIPTION, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCategoryPageTitleAttribute(?int $storeId = null): string
    {
        return (string)$this->getValue(self::AMOPENGRAPHTAGS_CATEGORY_PAGE_TITLE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCategoryPageDescriptionAttribute(?int $storeId = null): string
    {
        return (string)$this->getValue(self::AMOPENGRAPHTAGS_CATEGORY_PAGE_DESCRIPTION, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCmsPageTitleAttribute(?int $storeId = null): string
    {
        return (string)$this->getValue(self::AMOPENGRAPHTAGS_CMS_PAGE_TITLE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCmsPageDescriptionAttribute(?int $storeId = null): string
    {
        return (string)$this->getValue(self::AMOPENGRAPHTAGS_CMS_PAGE_DESCRIPTION, $storeId);
    }

    /**
     * @return string
     */
    public function getHomePageIdentifier(): string
    {
        return (string)$this->scopeConfig->getValue(
            Page::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
