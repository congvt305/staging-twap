<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 14/7/20
 * Time: 11:50 AM
 */
namespace Eguana\SocialLogin\Controller\Login;

use Eguana\SocialLogin\Helper\Data as Helper;
use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Model\Session as SessionAlias;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Json as JsonAlias;
use Magento\Framework\Controller\Result\JsonFactory as JsonFactoryAlias;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

/**
 * Class ValidateLogin
 *
 * Validate customer login
 */
class ValidateLogin extends Action
{
    const LINE = 'line';

    /** @var  Page */
    private $resultPageFactory;

    /**
     * @var SessionAlias
     */
    private $customerSession;

    /**
     * @var SocialLoginModel
     */
    private $socialLoginModel;

    /**
     * @var StoreManagerInterface
     */
    private $storemanager;

    /**
     * @var JsonFactoryAlias
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * ValidateLogin constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SessionAlias $session
     * @param SocialLoginModel $socialLoginModel
     * @param StoreManagerInterface $storemanager
     * @param JsonFactoryAlias $resultJsonFactory
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SessionAlias $session,
        SocialLoginModel $socialLoginModel,
        StoreManagerInterface $storemanager,
        JsonFactoryAlias $resultJsonFactory,
        LoggerInterface $logger,
        Helper $helper,
        CustomerRepository $customerRepository
    ) {
        $this->resultPageFactory               = $resultPageFactory;
        $this->customerSession                 = $session;
        $this->socialLoginModel                = $socialLoginModel;
        $this->storemanager                    = $storemanager;
        $this->resultJsonFactory               = $resultJsonFactory;
        $this->logger                          = $logger;
        $this->helper                          = $helper;
        $this->customerRepository              = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Check if customer exists then login otherwise redirect to login page
     * if customer is registering for first time in social login then redirect to social login form page
     * @return ResponseInterfaceAlias|JsonAlias|ResultInterfaceAlias
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $this->socialLoginModel->getCoreSession()->start();
        if ($this->socialLoginModel->getCoreSession()->getData('social_user_data')) {
            $socialData = $this->socialLoginModel->getCoreSession()->getData('social_user_data');
            $type = null;
            if (isset($socialData['socialmedia_type'])) {
                $type = $socialData['socialmedia_type'];
            }
            $url = $this->_url->getUrl('sociallogin/login/createcustomer', ['type'=> $type]);
            $response = [
                'errors' => false,
                'url'   => $url
            ];
            if ($this->helper->isMobile()) {
                $resultRedirect->setUrl($url);
                return $resultRedirect;
            } else {
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
        } elseif ($this->socialLoginModel->getCoreSession()->getSocialCustomerId()) {
            $data = $this->socialLoginModel->getCoreSession()->getSocialmediaId();
            try {
                $customer = $this->customerSession->getCustomer();
                $customerId = $this->socialLoginModel->getCoreSession()->getSocialCustomerId();
                $this->customerSession->loginById($customerId);
                $this->customerSession->setUsername($customer->getEmail());
                if ($this->socialLoginModel->getCoreSession()->getSocialmediaType() == self::LINE) {
                    $customer = $this->customerRepository->getById($customerId);
                    $customer->setCustomAttribute(
                        'line_id',
                        $this->socialLoginModel->getCoreSession()->getSocialmediaId()
                    );
                    $this->customerRepository->save($customer);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                //$this->logger->error($e->getMessage());
            }
            $this->socialLoginModel->getCoreSession()->unsSocialCustomerId();
            $url = $this->_url->getUrl('customer/account/index');
            $response = [
                'errors' => false,
                'messages' => __('Login successful.'),
                'url'   => $url
            ];
            if ($this->helper->isMobile()) {
                $resultRedirect->setUrl($url);
                return $resultRedirect;
            } else {
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
        } else {
            $url = $this->_url->getUrl('customer/account/login');
            $response = [
                'errors' => false,
                'url'   => $url
            ];
            if ($this->helper->isMobile()) {
                $resultRedirect->setUrl($url);
                return $resultRedirect;
            } else {
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
        }
    }
}
