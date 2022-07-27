<?php

namespace CJ\VLogicTablerate\Model\Plugin\Checkout\Block\Cart;

use CJ\VLogicTablerate\Model\Source\Config;
use Magento\Store\Model\StoreManagerInterface;

class Shipping
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsStateActive(\Magento\Checkout\Block\Cart\LayoutProcessor $subject, $result)
    {
        if ($this->storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return $result;
        }
        return $result || $this->_scopeConfig->getValue(
                Config::V_LOGIC_CARRIERS_ACTIVE_XML_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}
