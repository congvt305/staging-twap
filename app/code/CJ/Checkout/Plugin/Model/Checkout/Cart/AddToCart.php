<?php

namespace CJ\Checkout\Plugin\Model\Checkout\Cart;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;


class AddToCart
{
    const VN_LNG_WEBSITE = ['vn_laneige_website'];
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
        LoggerInterface $logger,
        Http $request
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->request = $request;
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
     * Plugin can not add product when customer does not login in  VN LNG
     *
     * @param $subject
     * @param $productInfo
     * @param $requestInfo
     * @return array|null
     */
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        $productType = $productInfo->getTypeId();
        $qty = $this->request->getParam('qty');
        // if add to cart from category page => qty param = null
        if (!isset($qty)
            && $productType == 'configurable'
            && in_array($this->storeManager->getWebsite()->getCode(), self::VN_LNG_WEBSITE)
        ) {
            return [$productInfo, $requestInfo];
        }

        if (!$this->customerSession->isLoggedIn()
            && !$this->isAllowedGuestCheckout()
            && in_array($this->storeManager->getWebsite()->getCode(), self::VN_LNG_WEBSITE)
        ) {
            throw new LocalizedException(__("You need to register for Laneige membership before making a purchase"));
        }

        if ($productInfo->getData('promotion_text')
        ) {
            throw new LocalizedException(__("Product is coming soon"));
        }

        return [$productInfo, $requestInfo];

    }
}
