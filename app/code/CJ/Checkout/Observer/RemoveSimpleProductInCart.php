<?php

namespace CJ\Checkout\Observer;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class RemoveSimpleProductInCart implements ObserverInterface
{
    const VN_LNG_WEBSITE = 'vn_laneige_website';
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Cart
     */
    private Cart $cart;

    /**
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param MessageManagerInterface $messageManager
     * @param Json $jsonSerializer
     * @param UrlInterface $url
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Cart $cart
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cart = $cart;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->customerSession->isLoggedIn() && !$this->isAllowedGuestCheckout() && $this->storeManager->getWebsite()->getCode() == self::VN_LNG_WEBSITE ) {

            //remove simple item from minicart
            $items = $this->cart->getItems();
            foreach ($items as $item) {
                if ($item->getProductType() == 'simple' ) {
                    $this->cart->getQuote()->removeAllItems();
                }
            }

            return $this;
        }
    }

    /**
     * @return bool
     */
    protected function isAllowedGuestCheckout(): bool
    {
        return $this->scopeConfig->isSetFlag(
            CheckoutHelper::XML_PATH_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE
        );
    }

}
