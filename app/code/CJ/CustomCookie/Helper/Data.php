<?php

namespace CJ\CustomCookie\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Data extends AbstractHelper
{
    const USER_CLOSED_COOKIE_POPUP = 'user_close_cookie_popup';

    const XML_PATH_COOKIE_TEMPLATE_CMS_BLOCK_ID = 'cj_cookie_popup/general/cookie_cms_block_id';

    const XML_PATH_COOKIE_LIFETIME = 'cj_cookie_popup/general/cookie_lifetime';

    const XML_PATH_IS_ENABLE_COOKIE_POPUP = 'cj_cookie_popup/general/active';
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
    public function getCookieTemplateBlockId($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_TEMPLATE_CMS_BLOCK_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get cookie lifetime
     *
     * @return int
     */
    public function getCookieLifeTime($storeId)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is enabled cookie popup
     *
     * @param $storeId
     * @return int
     */
    public function isEnabledCookiePopup($storeId)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_IS_ENABLE_COOKIE_POPUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
