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
     * @var SocialLoginModel
     */
    protected $socialLoginModel;

    /**
     * CreateCustomer constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SocialLoginModel $socialLoginModel
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SocialLoginModel $socialLoginModel
    ) {
        $this->resultPageFactory               = $resultPageFactory;
        $this->socialLoginModel                = $socialLoginModel;
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
            return $this->resultPageFactory->create();
        }
        $this->messageManager->addError(
            __('You are not authorized to view this page.')
        );
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account/login');
        return $resultRedirect;
    }
}
