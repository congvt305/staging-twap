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
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\SessionFactory;

/**
 * Class CreateCustomer
 *
 * Class for creating customer
 */
class CreateCustomer extends Action
{
    /** @var  Page */
    private $resultPageFactory;

    /**
     * @var SocialLoginModel
     */
    private $socialLoginModel;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * CreateCustomer constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SocialLoginModel $socialLoginModel
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SocialLoginModel $socialLoginModel,
        SessionFactory $sessionFactory
    ) {
        $this->resultPageFactory               = $resultPageFactory;
        $this->socialLoginModel                = $socialLoginModel;
        $this->sessionFactory                  = $sessionFactory;
        parent::__construct($context);
    }

    /**
     * Create page
     * @return void
     */
    public function execute()
    {
        $this->socialLoginModel->getCoreSession()->start();
        $resultRedirect = $this->resultRedirectFactory->create();
        // First check to see if someone is currently logged in.
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            $resultRedirect->setPath('customer/account/index');
            return $resultRedirect;
        }
        if ($this->socialLoginModel->getCoreSession()->getData('social_user_data')) {
            $loginType = $this->getRequest()->getParam('type');
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Social login by %1', $loginType));
            return $resultPage;
        }
        $this->messageManager->addError(
            __('You are not authorized to view this page.')
        );
        $resultRedirect->setPath('customer/account/login');
        return $resultRedirect;
    }
}
