<?php

namespace CJ\Checkout\Plugin\Controller\Product\View;

use Magento\Catalog\Controller\Product\View;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;


class ViewProduct
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
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    private $messageManager;

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
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute(View $subject, callable $proceed) {
            $redirect = $this->resultRedirectFactory->create();
            if (!$this->customerSession->isLoggedIn()
                && !$this->isAllowedGuestCheckout()
                && in_array($this->storeManager->getWebsite()->getCode(), self::VN_LNG_WEBSITE)
            ) {
                $this->messageManager->addWarningMessage(__('You need to register for Laneige membership before making a purchase'));
                $redirect->setPath('customer/account/login');
                return $redirect;

            }
        return $proceed();
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
