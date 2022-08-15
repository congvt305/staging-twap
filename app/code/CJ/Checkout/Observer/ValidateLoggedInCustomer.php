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
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ValidateLoggedInCustomer
 */
class ValidateLoggedInCustomer implements ObserverInterface
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
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->url = $url;
        $this->actionFlag = $actionFlag;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->customerSession->isLoggedIn() && !$this->isAllowedGuestCheckout() && $this->storeManager->getWebsite()->getCode() == self::VN_LNG_WEBSITE) {
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
                $controller->getResponse()->setRedirect($redirectionUrl)->sendResponse();
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
