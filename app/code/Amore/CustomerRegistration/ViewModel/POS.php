<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 19
 * Time: 오후 5:00
 */

namespace Amore\CustomerRegistration\ViewModel;

use Amore\CustomerRegistration\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Amore\CustomerRegistration\Model\Verification;
use Magento\Customer\Model\Session;

/**
 * It will use for the pos step during registration
 * Class POS
 */
class POS implements ArgumentInterface
{

    /**
     * Data
     *
     * @var Data
     */
    private $configHelper;

    /**
     * @var Verification
     */
    private $verification;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * POS constructor.
     *
     * @param Data $configHelper config helper
     */
    public function __construct(
        Data $configHelper,
        Verification $verification,
        Session $customerSession
    )
    {
        $this->configHelper = $configHelper;
        $this->verification = $verification;
        $this->customerSession = $customerSession;
    }

    /**
     * Return the cms block identifier
     * This function will get the cms block identifier set by the admin
     * in the configuration against the terms for POS.
     *
     * @return string
     */
    public function getTermsCmsBlockId()
    {
        return $this->configHelper->getTermsCMSBlockId();
    }

    /**
     * Get CODE EXPIRATION TIME IN MINUTES
     * Get code expiration time in minutes set in setting from admin setting
     *
     * @return null|int
     */
    public function getCodeExpirationTimeInMinutes()
    {
        return $this->configHelper->getCodeExpirationTimeInMinutes();
    }

    /**
     * Get minimum mobile number digits
     * Get minimum mobile number digits set in setting from admin setting
     *
     * @return null|int
     */
    public function getMinimumMobileNumberDigits()
    {
        return $this->configHelper->getMinimumMobileNumberDigits();
    }

    /**
     * Get maximum mobile number digits
     * Get maximum mobile number digits set in setting from admin setting
     *
     * @return null|int
     */
    public function getMaximumMobileNumberDigits()
    {
        return $this->configHelper->getMaximumMobileNumberDigits();
    }

    public function getCurrentStep()
    {
        return $this->verification->getCurrentRegistrationStep();
    }

    private function getSocialLoginData()
    {
        $this->customerSession->start();
        $socialData = $this->customerSession->getData('social_user_data');
        return $socialData;
    }

    public function getSocialMediaName()
    {
        $socialMediaName = '';
        $socialMediaData = $this->getSocialLoginData();
        if($socialMediaData != null)
        {
            $socialMediaName = isset($socialMediaData['name'])?$socialMediaData['name']:'';
        }
        return $socialMediaName;
    }
}
