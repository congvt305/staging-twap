<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: silentarmy
 * Date: 20/4/20
 * Time: 1:39 PM
 */
namespace Eguana\SocialLogin\Block\SocialLogin;

use Eguana\SocialLogin\Helper\Data;
use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Login
 *
 * Class for showing social logins
 */
class Login extends Template
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SocialLoginModel
     */
    private $socialLoginModel;

    /**
     * Login constructor.
     * @param Context $context
     * @param Data $helper
     * @param SocialLoginModel $socialLoginModel
     */
    public function __construct(
        Context $context,
        Data $helper,
        SocialLoginModel $socialLoginModel
    ) {
        $this->helper           = $helper;
        $this->socialLoginModel = $socialLoginModel;
        parent::__construct($context);
    }

    /**
     * Save state to registry
     * @return string
     */
    public function getState()
    {
        $state = hash('sha256', uniqid(rand(), true));
        $this->socialLoginModel->getCoreSession()->start();
        $this->socialLoginModel->getCoreSession()->setLineLoginState($state);
        return $state;
    }

    /**
     * Unset Session before login
     */
    public function unSetSession()
    {
        $this->socialLoginModel->getCoreSession()->start();
        $this->socialLoginModel->getCoreSession()->unsSocialUserData();
    }

    /**
     * Get helper
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
