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

/**
 * It will use for the register step during registration
 * Class Register
 */
class Register implements ArgumentInterface
{

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
     * Register constructor.
     *
     * @param Http $request request
     */
    public function __construct(
        Data $configHelper,
        Http $request,
        Session $customerSession
    ) {
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
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
        $onlineUserFavoriteStore = $this->configHelper->getOnlineUserFavoriteStore();
        return $this->request->getParam('favorite_store', $onlineUserFavoriteStore);
    }

    private function getSocialLoginData()
    {
        $this->customerSession->start();
        $socialData = $this->customerSession->getData('social_user_data');
        return $socialData;
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
}
