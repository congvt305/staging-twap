<?php

namespace CJ\CustomCookie\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Data extends AbstractHelper
{
    /**
     * constant cms block id for cookie template
     */
    const COOKIE_TEMPLATE_CMS_BLOCK_ID = 'web/cookie/cookie_cms_block_id';
    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param Context $context
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        Context $context
    ) {
        $this->cookieManager = $cookieManager;
        parent::__construct($context);
    }

    /**
     * Get cookie template block id
     *
     * @return mixed
     */
    public function getCookieTemplateBlockId()
    {
        return $this->scopeConfig->getValue(
            self::COOKIE_TEMPLATE_CMS_BLOCK_ID,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Is Enabled Cookie on Browser
     *
     * @return bool
     */
    public function isEnabledCookieBrowser()
    {
        return !empty($this->cookieManager->getCookie(\Magento\Cookie\Helper\Cookie::IS_USER_ALLOWED_SAVE_COOKIE));
    }


}
