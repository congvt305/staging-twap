<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 20/4/20
 * Time: 2:24 PM
 */
namespace Eguana\SocialLogin\Controller\LineLogin;

use Eguana\SocialLogin\Helper\Data as Helper;
use Eguana\SocialLogin\Model\SocialLoginHandler as SocialLoginModel;
use Eguana\SocialLogin\Model\SocialLoginRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Callback
 *
 * Callback class for line login
 */
class Callback extends Action
{
    const SOCIAL_MEDIA_TYPE = 'line';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

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
     * @param PageFactory $resultPageFactory
     * @param Helper $helper
     * @param RedirectFactory $resultRedirectFactory
     * @param Curl $curl
     * @param SocialLoginModel $socialLoginModel
     * @param SocialLoginRepository $socialLoginRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Helper $helper,
        RedirectFactory $resultRedirectFactory,
        Curl $curl,
        SocialLoginModel $socialLoginModel,
        SocialLoginRepository $socialLoginRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory                 = $resultPageFactory;
        $this->helper                            = $helper;
        $this->resultRedirectFactory             = $resultRedirectFactory;
        $this->curlClient                        = $curl;
        $this->socialLoginModel                  = $socialLoginModel;
        $this->socialLoginRepository             = $socialLoginRepository;
    }

    /**
     * Line callback
     * @return ResponseInterface|ResultInterface|null
     */
    public function execute()
    {
        $socialMediaType = self::SOCIAL_MEDIA_TYPE;
        $client_id = $this->helper->getSecretId();
        $client_secret = $this->helper->getChannelSecret();
        $code = $this->getRequest()->getParam('code');
        $redirect_uri = $this->helper->getCallbackUrl();
        $state = $this->getRequest()->getParam('state');
        if ($this->socialLoginModel->getCoreSession()->getLineLoginState() != $state) {
            $this->messageManager->addError(
                __('Warning! State mismatch. Authentication attempt may have been compromised.')
            );
        }
        $this->socialLoginModel->getCoreSession()->unsLineLoginState();
        if (!isset($code)) {
            $this->messageManager->addError(
                __('Warning! Visitor may have declined access or navigated to the page without being redirected.')
            );
        }
        $response = $this->getAccessToken($client_id, $client_secret, $redirect_uri, $code);
        try {
            $access_token = $response['access_token'];
            $id_token = $response['id_token'];
        } catch (\Exception $e) {
            $this->getResponse()->setBody(__($e->getMessage()));
            return null;
        }
        $postData = $this->getLineUserProfile($access_token, $id_token, $client_id);
        $dataUser = $this->verifyUserProfile($postData);
        $userid = $dataUser['sub'];
        $customerId = $this->socialLoginRepository->getSocialMediaCustomer($userid, $socialMediaType);
        //If customer exists then login and close popup else close pop and redirect to social login page
        $this->socialLoginModel->redirectCustomer($customerId, $dataUser, $userid, $socialMediaType);
        $this->helper->closePopUpWindow($this);
    }

    /**
     * @return Curl
     */
    private function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * Get line access token
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
            $apiUrl = "https://api.line.me/oauth2/v2.1/token";
            $request = 'client_id=' . $client_id;
            $request .= '&client_secret=' . $client_secret;
            $request .= '&redirect_uri=' . $redirect_uri;
            $request .= '&code=' . $code;
            $request .= '&grant_type=authorization_code';
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
            $this->getCurlClient()->post($apiUrl, []);
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
     * Get line user profile
     * @param $access_token
     * @return string|null
     */
    private function getLineUserProfile($access_token, $id_token, $client_id)
    {
        try {
            $url = "https://api.line.me/v2/profile";
            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER =>  ["Content-Type: application/json", "Authorization: Bearer " . $access_token]
                ]
            );
            $this->getCurlClient()->get($url, []);
            $status = $this->getCurlClient()->getStatus();
            if (($status == 400 || $status == 401)) {
                $message = __('Unspecified OAuth error occurred.');
                $this->getResponse()->setBody(__($message));
                return null;
            }
            $data = json_decode($this->getCurlClient()->getBody(), true);
            $username = $data['displayName'];
            $postData = '';
            $params = [
                "id_token" => $id_token,
                "client_id" => $client_id
            ];
            foreach ($params as $k => $v) {
                $postData .= $k . '=' . $v . '&';
            }
            $postData = rtrim($postData, '&');
            return $postData;
        } catch (\Exception $e) {
            $this->getResponse()->setBody(__($e->getMessage()));
            return null;
        }
    }

    /**
     * Verify user profile
     * @param $postData
     * @return mixed|null
     */
    private function verifyUserProfile($postData)
    {
        try {
            $verifyUrl = 'https://api.line.me/oauth2/v2.1/verify' . '?' . http_build_query([
                    'scope' => 'profile openid email'
                ]);
            $this->getCurlClient()->setOptions(
                [
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => $postData,
                    CURLOPT_HEADER => false,
                    CURLOPT_POST   => 2,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]
                ]
            );
            $this->getCurlClient()->post($verifyUrl, []);
            $status = $this->getCurlClient()->getStatus();
            if (($status == 400 || $status == 401)) {
                $message = __('Unspecified OAuth error occurred.');
                $this->getResponse()->setBody(__($message));
                return null;
            }
            $dataUser = json_decode($this->getCurlClient()->getBody(), true);
            return $dataUser;
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return null;
        }
    }
}
