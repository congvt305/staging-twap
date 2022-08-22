<?php

namespace CJ\Checkout\Plugin\Controller\Cart;
use Magento\Checkout\Controller\Cart\Add;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class AddToCart
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

    private $productRepository;


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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
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
        $this->productRepository = $productRepository;
    }

    /**
     * @param Add $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(Add $subject, callable $proceed) {
        if (!$this->customerSession->isLoggedIn()
            && !$this->isAllowedGuestCheckout()
            && in_array($this->storeManager->getWebsite()->getCode(), self::VN_LNG_WEBSITE)
        ) {
            $product = $this->_initProduct($subject);
            if($product) {
                $redirect = $this->resultRedirectFactory->create();
                $warningMessage = __('You need to register for Laneige membership before making a purchase');
                $this->messageManager->addWarningMessage($warningMessage);
                $redirect->setPath(self::CUSTOMER_LOGIN_PAGE);
                return $redirect;
            }
            return $proceed();
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

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct($subject)
    {
        $productId = (int)$subject->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }


}
