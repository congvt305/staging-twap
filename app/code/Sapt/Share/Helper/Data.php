<?php


namespace Sapt\Share\Helper;

use \Eguana\Share\Helper\Data as EguanaShare;

class Data extends EguanaShare
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $storeManager);
    }

    public function getWhatappUrl(){
        return $this->scopeConfig->getValue('social_share/whatapp_share/whatapp_share_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getWhatappEnable()
    {
        return $this->scopeConfig->getValue('social_share/whatapp_share/whatapp_share_enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getInstagramUrl(){
        return $this->scopeConfig->getValue('social_share/instagram_share/instagram_share_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getInstagramEnable()
    {
        return $this->scopeConfig->getValue('social_share/instagram_share/instagram_share_enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
