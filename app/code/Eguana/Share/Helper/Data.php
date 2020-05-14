<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-22
 * Time: 오후 5:11
 */

namespace Eguana\Share\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    public function getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl();
    }

    public function getShareIconEnabled(){
        return $this->scopeConfig->getValue('social_share/general/enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTwitterEnable()
    {
        return $this->scopeConfig->getValue('social_share/twitter_share/twitter_share_enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTwitterUrl(){
        return $this->scopeConfig->getValue('social_share/twitter_share/twitter_share_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getFacebookEnable()
    {
        return $this->scopeConfig->getValue('social_share/facebook_share/facebook_share_enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getFacebookUrl(){
        return $this->scopeConfig->getValue('social_share/facebook_share/facebook_share_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPinterestEnable()
    {
        return $this->scopeConfig->getValue('social_share/pinterest_share/pinterest_share_enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPinterestUrl(){
        return $this->scopeConfig->getValue('social_share/pinterest_share/pinterest_share_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getGoogleEnable()
    {
        return $this->scopeConfig->getValue('social_share/google_share/google_share_enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getGoogleUrl(){
        return $this->scopeConfig->getValue('social_share/google_share/google_share_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}