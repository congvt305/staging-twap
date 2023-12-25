<?php

namespace Sapt\Megamenu\Plugin;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class RemoveBlock implements ObserverInterface
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    protected $_scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct
    (
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            /** @var \Magento\Framework\View\Layout $layout */
            $layout = $observer->getLayout();
            $block = $layout->getBlock('catalog.topnav');  // here block reference name to remove

            if ($block) {
                $remove = $this->_scopeConfig->getValue('sapt_megamenu/general/enable', ScopeInterface::SCOPE_STORE);
                if ($remove) {
                    $layout->unsetElement('catalog.topnav');
                }
            }
        }
    }
}
