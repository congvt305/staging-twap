<?php

namespace CJ\Cms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package CJ\Cms\Helper
 */
class Data extends AbstractHelper
{
    /**
     * xml path get url from another site
     */
    const XML_PATH_CMS_MIGRATE_URL = 'custom_cms/migrate/url';

    /**
     * xml path get token from another site
     */
    const XML_PATH_CMS_MIGRATE_TOKEN = 'custom_cms/migrate/token';

    /**
     * xml path get store id from another site
     */
    const XML_PATH_CMS_MIGRATE_STORE_ID = 'custom_cms/migrate/store_id';

    /**
     * Get Migrate Url
     *
     * @return string
     */
    public function getMigrateUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CMS_MIGRATE_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Migrate Token
     *
     * @return string
     */
    public function getMigrateToken()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CMS_MIGRATE_TOKEN, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Migrate Store Id
     *
     * @return int
     */
    public function getMigrateStoreId()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_CMS_MIGRATE_STORE_ID, ScopeInterface::SCOPE_STORE);
    }
}
