<?php

namespace CJ\AmastyCheckoutCore\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CheckoutConfig implements ConfigProviderInterface
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        if ($this->_storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return [];
        }
        $additionalVariables['enable_multi_coupons'] = $this->_scopeConfig->getValue(
            'amcoupons/general/enable_multi_coupons',
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
        $additionalVariables['enable_macau'] = $this->_scopeConfig->getValue(
            'carriers/vlogic/enable_macau',
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
        return $additionalVariables;
    }
}
