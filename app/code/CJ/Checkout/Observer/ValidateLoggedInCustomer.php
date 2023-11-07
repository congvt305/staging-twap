<?php
declare(strict_types=1);

namespace CJ\Checkout\Observer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Setup\Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ValidateLoggedInCustomer
 */
class ValidateLoggedInCustomer implements ObserverInterface
{
    const VN_LNG_WEBSITE = ['vn_laneige_website'];

    const  CHECKOUT_CART_ADD_CONTROLLER = 'controller_action_postdispatch_checkout_cart_add';
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var MessageManagerInterface
     */
    private MessageManagerInterface $messageManager;

    /**
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var ActionFlag
     */
    private ActionFlag $actionFlag;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;


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
        MessageManagerInterface $messageManager,
        Json $jsonSerializer,
        UrlInterface $url,
        ActionFlag $actionFlag,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->url = $url;
        $this->actionFlag = $actionFlag;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {

        if ($observer->getEvent()->getName() == self::CHECKOUT_CART_ADD_CONTROLLER
            && in_array($this->storeManager->getWebsite()->getCode(), self::VN_LNG_WEBSITE)
        ) {
            $productType = '';
            $productId = $observer->getRequest()->getParam('product');
            try {
                $productType = $this->productRepository->getById($productId)->getTypeId();
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }

            $qty = $observer->getRequest()->getParam('qty');
            // if add to cart from category page => qty param = null
            if (!isset($qty) && $productType == 'configurable') {
                return $this;
            }
        }

        if (!$this->customerSession->isLoggedIn()
            && !$this->isAllowedGuestCheckout()
            && in_array($this->storeManager->getWebsite()->getCode(), self::VN_LNG_WEBSITE)
        ) {
            $warningMessage = __('You need to register for Laneige membership before making a purchase');
            $controller = $observer->getControllerAction();
            $redirectionUrl = $this->url->getUrl('customer/account/login');

            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            $this->messageManager->addErrorMessage($warningMessage);

            if ($controller->getRequest()->isAjax()) {
                $controller->getResponse()->representJson(
                    $this->jsonSerializer->serialize([
                        'message' => $warningMessage,
                        'error' => true,
                        'backUrl' => $redirectionUrl,
                    ])
                );
            } else {
                $controller->getResponse()->setRedirect($redirectionUrl)->setHttpResponseCode(301)->sendResponse();
            }
        }
        return $this;
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
