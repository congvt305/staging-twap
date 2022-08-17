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
use Magento\Framework\Json\Helper\Data as JsonHelper;


class ViewProduct
{
    const VN_LNG_WEBSITE = ['vn_laneige_website'];

    const CUSTOMER_LOGIN_PAGE = 'customer/account/login';
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

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;


    /**
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        JsonHelper $jsonHelper
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(View $subject, callable $proceed) {
            $redirect = $this->resultRedirectFactory->create();
            if (!$this->customerSession->isLoggedIn()
                && !$this->isAllowedGuestCheckout()
                && in_array($this->storeManager->getWebsite()->getCode(), self::VN_LNG_WEBSITE)
            ) {
                $warningMessage = __('You need to register for Laneige membership before making a purchase');
                if ($subject->getRequest()->isAjax()) {
                    $subject->getResponse()->representJson(
                        $this->jsonHelper->jsonEncode(
                            [
                                'message' => $warningMessage,
                                'error' => true,
                                'backUrl' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . self::CUSTOMER_LOGIN_PAGE
                            ]
                        )
                    );
                    return;
                }
                $this->messageManager->addWarningMessage($warningMessage);
                $redirect->setPath(self::CUSTOMER_LOGIN_PAGE);
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
