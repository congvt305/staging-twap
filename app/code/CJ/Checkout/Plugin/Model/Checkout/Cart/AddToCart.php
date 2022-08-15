<?php

namespace CJ\Checkout\Plugin\Model\Checkout\Cart;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;


class AddToCart
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
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


    /**
     * Plugin remove simple product when add to cart VN LNG
     *
     * @param $subject
     * @param $productInfo
     * @param $requestInfo
     * @return array|null
     */
    public function aroundAddProduct(\Magento\Checkout\Model\Cart $subject, callable $proceed, $productInfo, $requestInfo = null)
    {
        try {
            if (!$this->customerSession->isLoggedIn() && !$this->isAllowedGuestCheckout() && $this->storeManager->getWebsite()->getCode() == self::VN_LNG_WEBSITE) {
                if ($productInfo->getTypeId() == 'simple') {
                    return;
                }
            }
            $proceed($productInfo, $requestInfo);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
