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
use Magento\Framework\HTTP\Header;

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
    const XML_LINE_AGREEMENT_TEXT           = 'SocialLogin/line/line_messages_agreement';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    /**
     * Data constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\HTTP\Header $httpHeader
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Header $httpHeader
    ) {
        parent::__construct($context);
        $this->resultPageFactory                = $resultPageFactory;
        $this->httpHeader                       = $httpHeader;
    }

    /**
     * Check if user is from mobile device
     * @return string|null
     */
    public function isMobile()
    {
        $browserStatus = null;
        $userAgent = $this->httpHeader->getHttpUserAgent();
        //Identifying if user is on mobile browser or not
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$userAgent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($userAgent,0,4))) {
            $browserStatus = 'Mobile';
        }
        return $browserStatus;
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
     * Get LINE Agreement Text
     * @return mixed
     */
    public function getAgreementText()
    {
        return $this->scopeConfig->getValue(self::XML_LINE_AGREEMENT_TEXT, ScopeInterface::SCOPE_STORE);
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
