<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 25
 * Time: 오후 5:45
 */

namespace Amore\CustomerRegistration\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Math\Random;
use Amore\CustomerRegistration\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\StoreSms\Model\SmsSender;

/**
 * It will have main functions for Code verification
 * Class Verification
 */
class Verification
{
    /**
     * Data
     *
     * @var Data
     */
    private $configHelper;

    /**
     * Session Manager Interface
     *
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * Search Criteria Builder
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Customer Repository Interface
     *
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * Store Manager Interface
     *
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var SmsSender
     */
    private $smsSender;

    /**
     * Verification constructor.
     *
     * @param Data                        $configHelper                helper
     * @param SessionManagerInterface     $sessionManager              manager
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder       criteria
     * @param CustomerRepositoryInterface $customerRepositoryInterface repo
     * @param StoreManagerInterface       $storeManager                store
     */
    public function __construct(
        Data $configHelper,
        SessionManagerInterface $sessionManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreManagerInterface $storeManager,
        SmsSender $smsSender
    ) {
        $this->configHelper = $configHelper;
        $this->sessionManager = $sessionManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->smsSender = $smsSender;
    }

    /**
     * Send verification code
     *
     * @param string $mobileNumber mobile number
     *
     * @return bool|Phrase
     * @throws LocalizedException
     */
    public function sendVerificationCode($mobileNumber, $customerName)
    {
        $validateMobileNumberResult = $this->validateMobileNumber($mobileNumber);
        if ($validateMobileNumberResult !== true) {
            return $validateMobileNumberResult;
        }

        if ($verificationCode = $this->sendSMS($mobileNumber, $customerName)) {
            return $this->setVerificationCode($mobileNumber, $verificationCode);
        }
        return __('Can not send verification code.');
    }

    /**
     * Verify the code
     *
     * @param string $mobileNumber mobile number
     * @param string $code         verification code
     *
     * @return bool|Phrase
     */
    public function verifyCode($mobileNumber, $code)
    {
        $validateMobileNumberResult = $this->validateMobileNumber($mobileNumber);
        if ($validateMobileNumberResult !== true) {
            return $validateMobileNumberResult;
        }

        $validateVerificationCodeResult = $this->validateVerificationCode($code);
        if ($validateVerificationCodeResult !== true) {
            return $validateVerificationCodeResult;
        }

        return $this->verifySMSCode($mobileNumber, $code);
    }

    /**
     * Final verification of the whole customer information
     * This function will preform the final verification of the customer information.
     * And return following code
     * 1: If mobile number format is correct or not
     * 2: Verification Code format is correct or not
     * 3: Verification code is wrong
     * 4: If customer with same name and phone number exist
     * 5: If mobile number is used by any other member
     *
     * @param string $firstName    First Name
     * @param string $lastName     Last Name
     * @param string $mobileNumber mobile number
     * @param string $code         Mobile code
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function customerVerification($firstName, $lastName, $mobileNumber, $code)
    {
        $response = [];
        $validateMobileNumberResult = $this->validateMobileNumber($mobileNumber);
        if ($validateMobileNumberResult !== true) {
            $response['code'] = 1;
            $response['message'] = $validateMobileNumberResult;
            return $response;
        }

        $validateVerificationCodeResult = $this->validateVerificationCode($code);
        if ($validateVerificationCodeResult !== true) {
            $response['code'] = 2;
            $response['message'] = $validateVerificationCodeResult;
            return $response;
        }

        if ($this->verifySMSCode($mobileNumber,$code) === false) {
            $response['code'] = 3;
            $response['message'] = __('Verification code is wrong');
            return $response;
        }

        if ($this->isCustomerExist($firstName, $lastName, $mobileNumber) === true) {
            $response['code'] = 4;
            $cmsPage = $this->configHelper->getDuplicateMembershipCmsPage();
            if ($cmsPage) {
                $response['url'] = $this->storeManager->getStore()->getBaseUrl().$cmsPage;
            } else {
                $response['message'] = __(
                    'The requested membership information is already registered.'
                );
            }
            return $response;
        }

        if ($this->isMobileExist($mobileNumber) === true) {
            $response['code'] = 5;
            $cmsPage = $this->configHelper->getMembershipErrorCmsPage();
            if ($cmsPage) {
                $response['url'] = $this->storeManager->getStore()->getBaseUrl().$cmsPage;
            } else {
                $response['message'] = __(
                    'There is a problem with the requested subscription information. Please contact our CS Center for registration.'
                );
            }
            return $response;
        }

        $response['code'] = 6;
        return $response;
    }

    /**
     * To verify the code send to the customer against the mobile number
     * It wil verify the code send to the customer against the mobile number from the session
     *
     * @param string $mobileNumber     Mobile number
     * @param string $verificationCode Verification code
     *
     * @return bool
     */
    private function verifySMSCode($mobileNumber, $verificationCode)
    {
        $this->sessionManager->start();
        $savedMobileNumber = $this->sessionManager->getMobileNumber();
        $savedVerificationCode = $this->sessionManager->getVerificationCode();

        if ($mobileNumber == $savedMobileNumber
            && $verificationCode == $savedVerificationCode
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check whether mobile number is correct or not
     * Check whether mobile number is correct or not
     *
     * @param string $mobileNumber Mobile number
     *
     * @return bool|Phrase
     */
    private function validateMobileNumber($mobileNumber)
    {
        $mobileNumber = trim($mobileNumber);
        $mobileNumberLength = strlen($mobileNumber);

        if (is_null($mobileNumber) || empty($mobileNumber)) {
            return __('Mobile number can not be null or empty');
        } else if (!preg_match('/^\d+$/', $mobileNumber)) {
            return __('Mobile number should have only digits');
        } else if ($mobileNumberLength < $this->configHelper->getMinimumMobileNumberDigits()
        ) {
            return __(
                'Mobile number digits can not be less than %1',
                $this->configHelper->getMinimumMobileNumberDigits()
            );
        } else if($mobileNumberLength > $this->configHelper->getMaximumMobileNumberDigits()) {
            return __(
                'Mobile number digits can not be more than %1',
                $this->configHelper->getMaximumMobileNumberDigits()
            );
        }
        return true;
    }

    /**
     * Verification Code format is correct or not
     * Verification Code format is correct or not
     *
     * @param string $verificationCode verification code
     *
     * @return bool|Phrase
     */
    private function validateVerificationCode($verificationCode)
    {
        $verificationCodeLength = strlen($verificationCode);

        if (!preg_match('/^\d+$/', $verificationCode)) {
            return __('Validation Code should have only digits');
        } else if ($verificationCodeLength != 4) {
            return __('Verification code length can not be more than 4');
        }

        return true;
    }

    /**
     * Link the verfication code with the mobile number
     * It will link the verfication code with the mobile number
     * so that customer while moving to next step will not change the mobile number and code
     *
     * @param string $mobileNumber     Mobile Number
     * @param string $verificationCode Verification Code
     *
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
     *
     * @param string $mobileNumber     mobile number
     * @param string $verificationCode verification code
     *
     * @return bool
     */
    private function sendSMS($mobileNumber, $customerName)
    {
        if ($this->configHelper->getSMSVerificationEnable()) {
            return $this->smsSender->setCode($mobileNumber, $customerName);
        } else {
            return '1234';
        }
    }

    /**
     * Check whether the customer with the same information exist or not
     * Check whether the customer with the same information exist or not
     * It verify the customer on based of mobile number, first name and last name
     *
     * @param string $firstName    first name
     * @param string $lastName     last name
     * @param string $mobileNumber mobile number
     *
     * @return bool
     * @throws LocalizedException
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
     *
     * @param string $mobileNumber mobile number
     *
     * @return bool
     * @throws LocalizedException
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

    public function currentRegistrationStep($stepNumber)
    {
        $this->sessionManager->setCurrentStep($stepNumber);
    }

    public function getCurrentRegistrationStep()
    {
        if ($this->sessionManager->getCurrentStep()) {
            return $this->sessionManager->getCurrentStep();
        } else {
            return 1;
        }
    }
}