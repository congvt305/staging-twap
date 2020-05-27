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

    public function __construct(Data $configHelper,
                                SessionManagerInterface $sessionManager)
    {
        $this->configHelper = $configHelper;
        $this->sessionManager = $sessionManager;
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
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
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
            $result['message'] = __('Verification code is wrong');
            return $response;
        }

        if($this->isCustomerExist($firstName, $lastName, $mobileNumber) === true)
        {
            $response['code'] = 4;
            $result['url'] = 'https://www.google.com';
            return $response;
        }

        if($this->isMobileExist($mobileNumber) === true)
        {
            $response['code'] = 5;
            $result['url'] = 'https://www.yahoo.com';
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

    private function setVerificationCode($mobileNumber, $verificationCode)
    {
        $this->sessionManager->start();
        $this->sessionManager->setVerificationCode($verificationCode);
        $this->sessionManager->setMobileNumber($mobileNumber);
        return true;
    }

    private function sendSMS($mobileNumber, $verificationCode)
    {
        return true;
    }

    private function isCustomerExist($firstName, $lastName, $mobileNumber)
    {
        return false;
    }

    private function isMobileExist($mobileNumber)
    {
        return false;
    }
}