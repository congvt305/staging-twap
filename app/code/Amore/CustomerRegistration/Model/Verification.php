<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 25
 * Time: 오후 5:45
 */

namespace Amore\CustomerRegistration\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Math\Random;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * It will have main functions for Code verification
 * Class Verification
 * @package Amore\CustomerRegistration\Model
 */
class Verification
{
    /**
     * @var \Amore\CustomerRegistration\Helper\Data
     */
    private $configHelper;
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(Data $configHelper,
                                SessionManagerInterface $sessionManager,
                                SearchCriteriaBuilder $searchCriteriaBuilder,
                                CustomerRepositoryInterface $customerRepositoryInterface,
                                StoreManagerInterface $storeManager)
    {
        $this->configHelper = $configHelper;
        $this->sessionManager = $sessionManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeManager = $storeManager;
    }

    public function sendVerificationCode($mobileNumber)
    {
        $validateMobileNumberResult = $this->validateMobileNumber($mobileNumber);
        if($validateMobileNumberResult !== true)
        {
            return $validateMobileNumberResult;
        }
        $verificationCode = Random::getRandomNumber(1000, 9999);
        if($this->sendSMS($mobileNumber, '1234')) {
            return $this->setVerificationCode($mobileNumber, '1234');
        }
        return __('Can not send verification code.');
    }

    public function verifyCode($mobileNumber, $code)
    {
        $validateMobileNumberResult = $this->validateMobileNumber($mobileNumber);
        if($validateMobileNumberResult !== true)
        {
            return $validateMobileNumberResult;
        }

        $validateVerificationCodeResult = $this->validateVerificationCode($code);
        if($validateVerificationCodeResult !== true)
        {
            return $validateVerificationCodeResult;
        }

        return $this->verifySMSCode($mobileNumber,$code);

    }

    /**
     * Final verification of the whole customer information
     * This function will preform the final verification of the customer information. And return following code
     * 1: If mobile number format is correct or not
     * 2: Verification Code format is correct or not
     * 3: Verification code is wrong
     * 4: If customer with same name and phone number exist
     * 5: If mobile number is used by any other member
     * @param $firstName
     * @param $lastName
     * @param $mobileNumber
     * @param $code
     * @return array
     */
    public function customerVerification($firstName, $lastName, $mobileNumber, $code)
    {
        $response = [];
        $validateMobileNumberResult = $this->validateMobileNumber($mobileNumber);
        if($validateMobileNumberResult !== true)
        {
            $response['code'] = 1;
            $response['message'] = $validateMobileNumberResult;
            return $response;
        }

        $validateVerificationCodeResult = $this->validateVerificationCode($code);
        if($validateVerificationCodeResult !== true)
        {
            $response['code'] = 2;
            $response['message'] = $validateVerificationCodeResult;
            return $response;
        }

        if($this->verifySMSCode($mobileNumber,$code) === false)
        {
            $response['code'] = 3;
            $response['message'] = __('Verification code is wrong');
            return $response;
        }

        if($this->isCustomerExist($firstName, $lastName, $mobileNumber) === true)
        {
            $response['code'] = 4;
            $cmsPage = $this->configHelper->getDuplicateMembershipCmsPage();
            if($cmsPage) {
                $response['url'] = $this->storeManager->getStore()->getBaseUrl().$cmsPage;
            }else{
                $response['message'] = __('The requested membership information is already registered.');
            }
            return $response;
        }

        if($this->isMobileExist($mobileNumber) === true)
        {
            $response['code'] = 5;
            $cmsPage = $this->configHelper->getMembershipErrorCmsPage();
            if($cmsPage) {
                $response['url'] = $this->storeManager->getStore()->getBaseUrl().$cmsPage;
            }else{
                $response['message'] = __('There is a problem with the requested subscription information. Please contact our CS Center for registration.');
            }
            return $response;
        }

        $response['code'] = 6;
        return $response;
    }

    /**
     * To verify the code send to the customer against the mobile number
     * It wil verify the code send to the customer against the mobile number from the session
     * @param $mobileNumber
     * @param $verificationCode
     * @return bool
     */
    private function verifySMSCode($mobileNumber, $verificationCode)
    {
        $this->sessionManager->start();
        $savedMobileNumber = $this->sessionManager->getMobileNumber();
        $savedVerificationCode = $this->sessionManager->getVerificationCode();

        if($mobileNumber == $savedMobileNumber && $verificationCode == $savedVerificationCode)
        {
            return true;
        }

        return false;
    }

    /**
     * Check whether mobile number is correct or not
     *Check whether mobile number is correct or not
     * @param $mobileNumber
     * @return bool|\Magento\Framework\Phrase
     */
    private function validateMobileNumber($mobileNumber)
    {
        $mobileNumber = trim($mobileNumber);
        $mobileNumberLength = strlen($mobileNumber);

        if(is_null($mobileNumber) || empty($mobileNumber))
        {
            return __('Mobile number can not be null or empty');
        }else if(!preg_match('/^\d+$/',$mobileNumber) )
        {
            return __('Mobile number should have only digits');
        }else if($mobileNumberLength < $this->configHelper->getMinimumMobileNumberDigits())
        {
            return __('Mobile number digits can not be less than %1', $this->configHelper->getMinimumMobileNumberDigits());
        }else if($mobileNumberLength > $this->configHelper->getMaximumMobileNumberDigits())
        {
            return __('Mobile number digits can not be more than %1', $this->configHelper->getMaximumMobileNumberDigits());
        }
        return true;
    }

    /**
     * Verification Code format is correct or not
     * Verification Code format is correct or not
     * @param $verificationCode
     * @return bool|\Magento\Framework\Phrase
     */
    private function validateVerificationCode($verificationCode)
    {
        $verificationCodeLength = strlen($verificationCode);

        if(!preg_match('/^\d+$/',$verificationCode) )
        {
            return __('Validation Code should have only digits');
        }else if($verificationCodeLength != 4)
        {
            return __('Verification code length can not be more than 4');
        }

        return true;
    }

    /**
     * Link the verfication code with the mobile number
     * It will link the verfication code with the mobile number
     * so that customer while moving to next step will not change the mobile number and code
     * @param $mobileNumber
     * @param $verificationCode
     * @return bool
     */
    private function setVerificationCode($mobileNumber, $verificationCode)
    {
        $this->sessionManager->start();
        $this->sessionManager->setVerificationCode($verificationCode);
        $this->sessionManager->setMobileNumber($mobileNumber);
        return true;
    }

    /**
     * Send SMS to the specified mobile number
     * Send SMS to the specified mobile number
     * @param $mobileNumber
     * @param $verificationCode
     * @return bool
     */
    private function sendSMS($mobileNumber, $verificationCode)
    {
        return true;
    }

    /**
     * Check whether the customer with the same information exist or not
     * Check whether the customer with the same information exist or not
     * It verify the customer on based of mobile number, first name and last name
     * @param $firstName
     * @param $lastName
     * @param $mobileNumber
     * @return bool
     */
    private function isCustomerExist($firstName, $lastName, $mobileNumber)
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $searchCriteria = $this->searchCriteriaBuilder
                                ->addFilter('website_id', $websiteId)
                                ->addFilter('firstname', $firstName)
                                ->addFilter('lastname', $lastName)
                                ->addFilter('mobile_number', $mobileNumber)
                                ->create();
        $customers = $this->customerRepositoryInterface->getList($searchCriteria)->getItems();
        return count($customers)?true:false;

    }

    /**
     * Check whether mobile number is using by any other customer or not
     * if mobile number is using by any other customer then return false
     * else it wil return true
     * @param $mobileNumber
     * @return bool
     */
    private function isMobileExist($mobileNumber)
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('website_id', $websiteId)
            ->addFilter('mobile_number', $mobileNumber)
            ->create();
        $customers = $this->customerRepositoryInterface->getList($searchCriteria)->getItems();
        return count($customers)?true:false;
    }
}