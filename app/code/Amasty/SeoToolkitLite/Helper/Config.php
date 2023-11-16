<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

namespace Amasty\SeoToolkitLite\Helper;

use Amasty\SeoToolkitLite\Model\Config\Backend\ViewAllCanonical;
use Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const MODULE_NAME = 'amseotoolkit/';
    public const XML_PATH_IS_SEO_URL_ENABLED = 'general/enable_seo_url';
    public const XML_PATH_SEO_KEY = 'general/seo_key';
    public const XML_PATH_PAGER_PREV_NEXT = 'pager/prev_next';
    public const XML_PATH_PAGER_VIEW_ALL_CANONICAL = 'pager/view_all_canonical';

    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_NAME . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isPrevNextLinkEnabled()
    {
        return $this->getModuleConfig(self::XML_PATH_PAGER_PREV_NEXT) && !$this->isViewAllCanonicalEnabled();
    }

    /**
     * @return bool
     */
    public function isViewAllCanonicalEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_PAGER_VIEW_ALL_CANONICAL);
    }

    /**
     * @return bool
     */
    public function isAllPageEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(ViewAllCanonical::CATALOG_FRONTEND_LIST_ALLOW_ALL);
    }

    public function isAddPageToMetaTitleEnabled()
    {
        return $this->getModuleConfig('pager/meta_title');
    }

    public function isAddPageToMetaDescEnabled()
    {
        return $this->getModuleConfig('pager/meta_description');
    }

    public function isNoIndexNoFollowEnabled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isSeoUrlsEnabled()
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_IS_SEO_URL_ENABLED);
    }

    /**
     * @return string
     */
    public function getSeoKey()
    {
        return (string)urlencode(trim($this->getModuleConfig(self::XML_PATH_SEO_KEY)));
    }
}
