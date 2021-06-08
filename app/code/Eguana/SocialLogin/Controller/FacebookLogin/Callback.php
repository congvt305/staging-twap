<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 28/5/20
 * Time: 4:05 PM
 */
namespace Eguana\SocialLogin\Controller\FacebookLogin;

use Eguana\SocialLogin\Helper\Data as Helper;
use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Eguana\SocialLogin\Model\SocialLoginRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Eguana\SocialLogin\Controller\AbstractSocialLogin;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeAlias;
use Psr\Log\LoggerInterface;

/**
 * Class Callback
 *
 * Callback class for facebook login
 */
class Callback extends Action
{
    const SOCIAL_MEDIA_TYPE = 'facebook';

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var SocialLoginModel
     */
    protected $socialLoginModel;

    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var SocialLoginRepository
     */
    private $socialLoginRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Callback constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param SocialLoginModel $socialLoginModel
     * @param Curl $curl
     * @param SocialLoginRepository $socialLoginRepository
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Helper $helper,
        SocialLoginModel $socialLoginModel,
        Curl $curl,
        SocialLoginRepository $socialLoginRepository
    ) {
        $this->helper                           = $helper;
        $this->socialLoginModel                 = $socialLoginModel;
        $this->curlClient                       = $curl;
        $this->socialLoginRepository            = $socialLoginRepository;
        $this->logger                           = $logger;
        parent::__construct(
            $context,
        );
    }

    /**
     * Facebook callback function
     * @return ResponseInterface|Controller\ResultInterface|null
     */
    public function execute()
    {
        $socialMediaType = self::SOCIAL_MEDIA_TYPE;
        $fb = null;
        $accessToken = null;
        $user = null;
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        $client_id = $this->helper->getAppId();
        $client_secret = $this->helper->getAppSecret();
        $redirect_uri = $this->helper->getFbCallbackUrl();
        if ($this->socialLoginModel->getCoreSession()->getFacebookLoginState() != $state) {
            $this->getResponse()->setBody(__('Warning! State mismatch. Authentication attempt may have been compromised.'));
            return null;
        }
        $this->socialLoginModel->getCoreSession()->unsFacebookLoginState();
        $response = $this->getAccessToken($client_id, $client_secret, $redirect_uri, $code);
        try {
            $access_token = $response['access_token'];
            $response = $this->verifyAccessToken($access_token);
            $this->logger->info("Log 2: For Access Token");
            if ($response['success'] != 1) {
                $this->logger->info("Log 3: For Unspecified OAuth Error");
                $this->getResponse()->setBody(__('Unspecified OAuth error occurred.'));
                return null;
            }
        } catch (\Exception $e) {
            $this->getResponse()->setBody(__($e->getMessage()));
            return null;
        }
        try {
            $response = $this->getFbUserProfile($access_token, $client_id, $redirect_uri);
        } catch (\Exception $e) {
            $this->getResponse()->setBody(__($e->getMessage()));
            return null;
        }
        $userid = $response['id'];
        $customerId = $this->socialLoginRepository->getSocialMediaCustomer($userid, $socialMediaType);
        //If customer exists then login and close popup else close pop and redirect to social login page
        if ($customerId) {
            $this->socialLoginModel->getCoreSession()->unsSocialCustomerId();
            $this->socialLoginModel->getCoreSession()->setSocialCustomerId($customerId);
            $this->socialLoginModel->getCoreSession()->unsSocialmediaType();
            $this->socialLoginModel->getCoreSession()->setSocialmediaType('facebook');
            $this->socialLoginModel->getCoreSession()->unsSocialmediaId();
            $this->socialLoginModel->getCoreSession()->setSocialmediaId($userid);
        } else {
            $this->socialLoginModel->redirectCustomer($response, $userid, $socialMediaType);
        }
        if ($this->helper->isMobile()) {
            $url = $this->_url->getUrl('sociallogin/login/validatelogin');
            $this->_redirect($url);
            $this->logger->info("Log 4: For Mobile");
        } else {
            $this->helper->closePopUpWindow($this);
        }
    }

    /**
     * @return Curl
     */
    private function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * Get facebook user access token
     * @param $client_id
     * @param $client_secret
     * @param $redirect_uri
     * @param $code
     * @return mixed|null
     */
    private function getAccessToken($client_id, $client_secret, $redirect_uri, $code)
    {
        $response = null;
        try {
            $apiUrl = "https://graph.facebook.com/v7.0/oauth/access_token";
            $request = 'client_id=' . $client_id;
            $request .= '&client_secret=' . $client_secret;
            $request .= '&redirect_uri=' . $redirect_uri;
            $request .= '&code=' . $code;
            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => $request,
                    CURLOPT_HEADER => false,
                    CURLOPT_VERBOSE => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]
                ]
            );
            $this->getCurlClient()->get($apiUrl, []);
            $status = $this->getCurlClient()->getStatus();
            if (($status == 400 || $status == 401)) {
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
     * Verify user access token
     * @param $access_token
     * @return mixed|null
     */
    private function verifyAccessToken($access_token)
    {
        $response = null;
        try {
            $apiUrl = "https://graph.facebook.com/me";
            $request = 'access_token=' . $access_token;
            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => $request,
                    CURLOPT_HEADER => false,
                    CURLOPT_VERBOSE => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]
                ]
            );
            $this->getCurlClient()->get($apiUrl, []);
            $status = $this->getCurlClient()->getStatus();
            if (($status == 400 || $status == 401)) {
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
     * Get facebook user profile
     * @param $access_token
     * @param $client_id
     * @param $redirect_uri
     * @return mixed|null
     */
    private function getFbUserProfile($access_token, $client_id, $redirect_uri)
    {
        $response = null;
        try {
            $apiUrl = "https://graph.facebook.com/v7.0/me?access_token=" . $access_token . "&fields=id,name,email";
            $request = 'access_token=' . $access_token;
            $request = 'fields=id,name,email';
            $this->getCurlClient()->get($apiUrl, []);
            $status = $this->getCurlClient()->getStatus();
            if (($status == 400 || $status == 401)) {
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
}
