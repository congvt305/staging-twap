<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 20/4/20
 * Time: 12:42 PM
 */
namespace Eguana\SocialLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper as AbstractHelperAlias;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\Template as TemplateAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * Helper class
 */
class Data extends AbstractHelperAlias
{
    const XML_SOCIAL_LOGIN_ENABLE           = 'SocialLogin/general/sociallogin_mod_enable';
    const XML_SOCIAL_LOGIN_LINE_ENABLE      = 'SocialLogin/line/line_enable';
    const XML_SOCIAL_LINE_SECRET_ID         = 'SocialLogin/line/channel_id';
    const XML_SOCIAL_LINE_CHANNEL_SECRET    = 'SocialLogin/line/channel_secret';
    const XML_SOCIAL_LINE_CALLBACK_URL      = 'SocialLogin/line/callback_url';
    const XML_SOCIAL_LOGIN_FACEBOOK_ENABLE  = 'SocialLogin/facebook/facebook_enable';
    const XML_SOCIAL_FACEBOOK_APP_ID        = 'SocialLogin/facebook/app_id';
    const XML_SOCIAL_FACEBOOK_APP_SECRET    = 'SocialLogin/facebook/app_secret';
    const XML_SOCIAL_FACEBOOK_CALLBACK_URL  = 'SocialLogin/facebook/oauth_redirect_uri';
    const XML_SOCIAL_GOOGLE_ENABLE          = 'SocialLogin/google/google_enable';
    const XML_SOCIAL_GOOGLE_CLIENT_ID       = 'SocialLogin/google/client_id';
    const XML_SOCIAL_GOOGLE_CLIENT_SECRET   = 'SocialLogin/google/client_secret';
    const XML_SOCIAL_GOOGLE_CALLBACK_URL    = 'SocialLogin/google/oauth_redirect_uri';
    const XML_LINE_ADD_FRIEND               = 'SocialLogin/line/line_add_friend';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory                = $resultPageFactory;
    }

    /**
     * Check if module is enabled or not
     * @param null $store
     * @return bool
     */
    public function isEnabledInFrontend($store = null)
    {
        $isEnabled = true;
        $enabled =  $this->scopeConfig->getValue(self::XML_SOCIAL_LOGIN_ENABLE, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }

    /**
     * Get line add friend link
     * @return mixed
     */
    public function getLineAddFriendLink()
    {
        return $this->scopeConfig->getValue(self::XML_LINE_ADD_FRIEND, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Close login pop up window
     * @param $objectAction
     */
    public function closePopUpWindow($objectAction)
    {
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock(TemplateAlias::class)
            ->setTemplate('Eguana_SocialLogin::social/close-popup.phtml')
            ->toHtml();
        $objectAction->getResponse()->setBody($block);
    }

    /**
     * Check if line login is enbled or not
     * @param null $store
     * @return bool
     */
    public function isEnabledLine($store = null)
    {
        $isEnabled = true;
        $enabled =  $this->scopeConfig->getValue(
            self::XML_SOCIAL_LOGIN_LINE_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }

    /**
     * Check if facebook login is enabled or not
     * @param null $store
     * @return bool
     */
    public function isEnabledFacebook($store = null)
    {
        $isEnabled = true;
        $enabled =  $this->scopeConfig->getValue(
            self::XML_SOCIAL_LOGIN_FACEBOOK_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }

    /**
     * Check if google login is enabled or not
     * @param null $store
     * @return bool
     */
    public function isEnabledGoogle($store = null)
    {
        $isEnabled = true;
        $enabled =  $this->scopeConfig->getValue(self::XML_SOCIAL_GOOGLE_ENABLE, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }

    /**
     * Get secret id
     * @return string
     */
    public function getSecretId()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_LINE_SECRET_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get app id
     * @return string
     */
    public function getAppId()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_FACEBOOK_APP_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get channel secret
     * @return string
     */
    public function getChannelSecret()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_LINE_CHANNEL_SECRET, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get app secret
     * @return string
     */
    public function getAppSecret()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_FACEBOOK_APP_SECRET, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get callback url
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_LINE_CALLBACK_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get fb callback url
     * @return string
     */
    public function getFbCallbackUrl()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_FACEBOOK_CALLBACK_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get google client id
     * @return string
     */
    public function getGoogleClientId()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_GOOGLE_CLIENT_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get google client secret
     * @return string
     */
    public function getGoogleClientSecret()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_GOOGLE_CLIENT_SECRET, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get google callback url
     * @return string
     */
    public function getGoogleCallbackUrl()
    {
        return $this->scopeConfig->getValue(self::XML_SOCIAL_GOOGLE_CALLBACK_URL, ScopeInterface::SCOPE_STORE);
    }
}
