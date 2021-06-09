<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/8/20
 * Time: 5:00 PM
 */
namespace Eguana\SocialLogin\Controller\FacebookLogin;

use Eguana\SocialLogin\Helper\Data as Helper;
use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\Generic;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller;
use Psr\Log\LoggerInterface;

/**
 * Class Connect
 *
 * Facebook callback
 */
class Connect extends Action
{
    const OAUTH2_AUTH_URI = 'https://www.facebook.com/v7.0/dialog/oauth';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var SocialLoginModel
     */
    private $socialLoginModel;

    /**
     * Connect constructor.
     * @param Context $context
     * @param Helper $helper
     * @param Generic $session
     * @param SocialLoginModel $socialLoginModel
     */
    public function __construct(
        Context $context,
        Helper $helper,
        LoggerInterface $logger,
        Generic $session,
        SocialLoginModel $socialLoginModel
    )
    {
        $this->helper = $helper;
        $this->session = $session;
        $this->socialLoginModel = $socialLoginModel;
        $this->logger = $logger;
        parent::__construct(
            $context
        );
    }

    /**
     * Redirect to callback
     * @return ResponseInterface|Controller\ResultInterface
     */
    public function execute()
    {
        $state = hash('sha256', uniqid(rand(), true));
        $this->socialLoginModel->getCoreSession()->setFacebookLoginState($state);
        $fbAppId = $this->helper->getAppId();
        $fbRedirectUrl = $this->helper->getFbCallbackUrl();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sociallogin.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Log 1: ".$fbRedirectUrl);
        $url = self::OAUTH2_AUTH_URI . '?' . http_build_query(
                [
                    'client_id' => $fbAppId,
                    'redirect_uri' => $fbRedirectUrl,
                    'state' => $state
                ]
            );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($url);
    }
}
