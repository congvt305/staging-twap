<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/8/20
 * Time: 5:03 PM
 */
namespace Eguana\SocialLogin\Controller\LineLogin;

use Eguana\SocialLogin\Helper\Data as Helper;
use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Session\Generic;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Connect
 *
 * Line callback
 */
class Connect extends Action
{
    const OAUTH2_AUTH_URI = 'https://access.line.me/oauth2/v2.1/authorize';

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
        Generic $session,
        SocialLoginModel $socialLoginModel
    ) {
        $this->helper                            = $helper;
        $this->session                           = $session;
        $this->socialLoginModel                  = $socialLoginModel;
        parent::__construct(
            $context
        );
    }

    /**
     * Redirect to callback
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $state = hash('sha256', uniqid(rand(), true));
        $this->socialLoginModel->getCoreSession()->setLineLoginState($state);
        $clientId = $this->helper->getSecretId();
        $callbackUrl = $this->helper->getCallbackUrl();
        $url = self::OAUTH2_AUTH_URI . '?' . http_build_query(
                [
                    'response_type' => 'code',
                    'client_id' => $clientId,
                    'redirect_uri' => $callbackUrl,
                    'state' => $state,
                    'scope' => 'profile openid email',
                    'nonce' => '09876xyz'
                ]
            );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($url);
    }
}
