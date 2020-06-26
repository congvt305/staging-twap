<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/6/20
 * Time: 5:06 PM
 */
namespace Eguana\SocialLogin\Controller\GoogleLogin;

use Eguana\SocialLogin\Helper\Data as Helper;
use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Eguana\SocialLogin\Model\SocialLoginRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

/**
 * Class Callback
 *
 * Callback class for google login
 */
class Callback extends Action
{
    const SOCIAL_MEDIA_TYPE = 'google';
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var SocialLoginModel
     */
    protected $socialLoginModel;

    /**
     * @var SocialLoginRepository
     */
    private $socialLoginRepository;

    /**
     * Callback constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param Curl $curl
     * @param SocialLoginModel $socialLoginModel
     * @param SocialLoginRepository $socialLoginRepository
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Helper $helper,
        Curl $curl,
        SocialLoginModel $socialLoginModel,
        SocialLoginRepository $socialLoginRepository
    ) {
        $this->helper                            = $helper;
        $this->curlClient                        = $curl;
        $this->socialLoginModel                  = $socialLoginModel;
        $this->socialLoginRepository             = $socialLoginRepository;
        parent::__construct(
            $context,
        );
    }

    /**
     * Google login callback
     * @return ResponseInterface|ResultInterfaceAlias|null
     */
    public function execute()
    {
        $socialMediaType = self::SOCIAL_MEDIA_TYPE;
        $params = $this->getRequest()->getParams();
        $clientId = $this->helper->getGoogleClientId();
        $clientSecret = $this->helper->getGoogleClientSecret();
        $clientRedirectUrl = $this->helper->getGoogleCallbackUrl();
        // Google passes a parameter 'code' in the Redirect Url
        if (isset($params['code'])) {
            try {
                // Get the access token
                $data = $this->getAccessToken($clientId, $clientRedirectUrl, $clientSecret, $params['code']);
                // Access Token
                $access_token = $data['access_token'];
                // Get user information
                $user_info = $this->getUserProfileInfo($access_token);
                $userid = $user_info['id'];
            } catch (\Exception $e) {
                $this->getResponse()->setBody(__($e->getMessage()));
                return null;
            }
            $customerId = $this->socialLoginRepository->getSocialMediaCustomer($userid, $socialMediaType);
            //If customer exists then login and close popup else close pop and redirect to social login page
            if ($customerId) {
                $this->socialLoginModel->getCoreSession()->unsSocialCustomerId();
                $this->socialLoginModel->getCoreSession()->setSocialCustomerId($customerId);
            } else {
                $this->socialLoginModel->redirectCustomer($user_info, $userid, $socialMediaType);
            }
            $this->helper->closePopUpWindow($this);
        }
    }

    /**
     * Holds the various APIs functions
     * @param $client_id
     * @param $redirect_uri
     * @param $client_secret
     * @param $code
     * @return mixed
     */
    private function getAccessToken($client_id, $redirect_uri, $client_secret, $code)
    {
        $url = 'https://www.googleapis.com/oauth2/v4/token';
        try {
            $curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=';
            $curlPost = $curlPost . $client_secret . '&code=' . $code . '&grant_type=authorization_code';
            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => 1,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_POSTFIELDS => $curlPost
                ]
            );
            $this->getCurlClient()->post($url, []);
            $status = $this->getCurlClient()->getStatus();
            if ($status != 200) {
                $message = __('Unspecified OAuth error occurred.');
                $this->getResponse()->setBody(__($message));
                return null;
            }
            $response = json_decode($this->getCurlClient()->getBody(), true);
            return $response;
        } catch (\Exception $e) {
            $this->getResponse()->setBody(__($e->getMessage()));
            return null;
        }
    }

    /**
     * Get user profile info
     * @param $access_token
     * @return mixed
     */
    private function getUserProfileInfo($access_token)
    {
        $url = 'https://www.googleapis.com/oauth2/v2/userinfo?fields=name,email,gender,id,picture,verified_email';

        try {
            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $access_token,
                    ]
                ]
            );
            $this->getCurlClient()->get($url, []);
            $status = $this->getCurlClient()->getStatus();
            if ($status != 200) {
                $message = __('Unspecified OAuth error occurred.');
                $this->getResponse()->setBody(__($message));
            }
            $response = json_decode($this->getCurlClient()->getBody(), true);
            return $response;
        } catch (\Exception $e) {
            $this->getResponse()->setBody(__($e->getMessage()));
            return null;
        }
    }

    /**
     * @return Curl
     */
    private function getCurlClient()
    {
        return $this->curlClient;
    }
}
