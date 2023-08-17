<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 19
 * Time: 오후 5:00
 */

namespace Amore\CustomerRegistration\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Session\SessionManagerInterface as Session;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * It will use for the register step during registration
 * Class Register
 */
class Register implements ArgumentInterface
{
    const LINE = 'line';
    const VERIFIED_ICON = 'Amore_CustomerRegistration::images/check.svg';

    /**
     * Http
     *
     * @var Http
     */
    private $request;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var Data
     */
    private $configHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Register constructor.
     * @param Data $configHelper
     * @param Http $request
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Data $configHelper,
        Http $request,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Return the cms block identifier
     * This function will get the cms block identifier set by the admin
     * in the configuration against the pos alert for POS.
     *
     * @return string
     */
    public function getPosAlertCmsBlockId()
    {
        return $this->configHelper->getPosAlertCMSBlockId();
    }

    /**
     * Get the referrer code
     * It will return the referrer code from the get parameter
     *
     * @return mixed
     */
    public function getReferrerCode()
    {
        return $this->request->getParam('referrer_code', '');
    }

    /**
     * Get the favorite store
     * It will return the favorite store from the get parameter
     *
     * @return mixed
     */
    public function getFavoriteStore()
    {
        $onlineUserFavoriteStore = $this->configHelper->getPartnerId();
        return $this->request->getParam('favorite_store', $onlineUserFavoriteStore);
    }

    private function getSocialLoginData()
    {
        $this->customerSession->start();
        $socialData = $this->customerSession->getData('social_user_data');
        return $socialData;
    }

    /**
     * Get line social media app id
     * @return mixed|null
     */
    public function getLineId()
    {
        $lineId = null;
        $socialMediaData = $this->getSocialLoginData();
        if (isset($socialMediaData['socialmedia_type'])) {
            $socialMediaType = $socialMediaData['socialmedia_type'];
            if ($socialMediaType == self::LINE) {
                return $socialMediaData['appid'];
            }
        }
        return $lineId;
    }

    /**
     * Get social media type if LINE
     * @return bool
     */
    public function getSocialMediaType()
    {
        $socialMediaData = $this->getSocialLoginData();
        if (isset($socialMediaData['socialmedia_type'])) {
            $socialMediaType = $socialMediaData['socialmedia_type'];
            if ($socialMediaType == self::LINE) {
                return true;
            }
        }
        return false;
    }

    public function getSocialMediaEmail()
    {
        $socialMediaEmail = '';
        $socialMediaData = $this->getSocialLoginData();
        if ($socialMediaData != null) {
            $socialMediaEmail = isset($socialMediaData['email'])?$socialMediaData['email']:'';
        }
        return $socialMediaEmail;
    }

    /**
     * Get newsLetter privacy policy CMS block id
     *
     * @return string
     */
    public function getNewsLetterPolicyCMSBlockId()
    {
        return $this->configHelper->getNewsLetterPolicyCMSBlockId();
    }

    /**
     * To check BA Code feature value in configuration
     *
     * @return bool
     */
    public function checkBaCodeEnabled()
    {
        $currentWebsiteId = $this->storeManager->getStore()->getWebsiteId();
        $currentWebsiteId = $currentWebsiteId ? $currentWebsiteId : null;
        return $this->configHelper->getBaCodeEnable($currentWebsiteId);
    }

    /**
     * Retrieve BA code action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return mixed
     */
    public function getBaCodeVerifyUrl()
    {
        return $this->urlBuilder->getUrl(
            'customerregistration/verification/verifybacode',
            ['_secure' => true]
        );
    }

    /**
     * Get Url of verified icon
     *
     * @return string
     */
    public function getVerifiedIconUrl()
    {
        return self::VERIFIED_ICON;
    }
}
