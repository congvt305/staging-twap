<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: silentarmy
 * Date: 21/4/20
 * Time: 1:06 PM
 */
namespace Eguana\SocialLogin\Controller\Login;

use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Magento\Customer\Model\Session as SessionAlias;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CreateCustomer
 *
 * Class for creating customer
 */
class CreateCustomer extends Action
{
    /** @var  Page */
    protected $resultPageFactory;

    /**
     * @var SessionAlias
     */
    protected $customerSession;

    /**
     * @var SocialLoginModel
     */
    protected $socialLoginModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storemanager;

    /**
     * CreateCustomer constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SessionAlias $session
     * @param SocialLoginModel $socialLoginModel
     * @param StoreManagerInterface $storemanager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SessionAlias $session,
        SocialLoginModel $socialLoginModel,
        StoreManagerInterface $storemanager
    ) {
        $this->resultPageFactory               = $resultPageFactory;
        $this->customerSession                 = $session;
        $this->socialLoginModel                = $socialLoginModel;
        $this->storemanager                    = $storemanager;
        parent::__construct($context);
    }

    /**
     * Create page
     * @return void
     */
    public function execute()
    {
        $this->socialLoginModel->getCoreSession()->start();
        if ($this->socialLoginModel->getCoreSession()->getData('social_user_data')) {
            $resultPage = $this->resultPageFactory->create();
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } elseif ($this->socialLoginModel->getCoreSession()->getSocialCustomerId()) {
            try {
                $customerId = $this->socialLoginModel->getCoreSession()->getSocialCustomerId();
                $this->customerSession->loginById($customerId);
                $customer = $this->customerSession->getCustomer();
                $this->customerSession->setUsername($customer->getEmail());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Sorry, that\'s all we know.')
                );
            }
            $this->socialLoginModel->getCoreSession()->unsSocialCustomerId();
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/index');
            $this->messageManager->addSuccess(
                __('Login successful.')
            );
            return $resultRedirect;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
    }
}
