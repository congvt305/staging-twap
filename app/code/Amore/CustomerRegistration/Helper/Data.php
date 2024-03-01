<?php
/**
 * Created by PhpStorm
 * User: Abbas
 * Date: 05/18/20
 * Time: 9:02 PM
 */

namespace Amore\CustomerRegistration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * This class is used for StoreSms configuration data
 *
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * Store constants
     */
    const POS_TERMS_CMS_BLOCK_ID = 'customerregistraion/general/terms_cms_block_id';
    const POS_ALERT_CMS_BLOCK_ID = 'customerregistraion/general/pos_alert_cms_block_id';
    const CODE_EXPIRATION_TIME_IN_MINUTES = 'customerregistraion/general/code_expiration_time_in_minutes';
    const MINIMUM_MOBILE_NUMBER_DIGITS = 'customerregistraion/general/minimum_mobile_number_digits';
    const MAXIMUM_MOBILE_NUMBER_DIGITS = 'customerregistraion/general/maximum_mobile_number_digits';
    const MEMBERSHIP_ERROR_CMS_PAGE = 'customerregistraion/general/membership_error_cms_page';
    const DUPLICATE_MEMBERSHIP_CMS_PAGE = 'customerregistraion/general/duplicate_membership_cms_page';
    const TERMS_AND_SERVICES_POLICY_CMS_BLOCK = 'customerregistraion/general/terms_and_services_policy_cms_block';
    const PRIVACY_POLICY_CMS_BLOCK = 'customerregistraion/general/privacy_policy_cms_block';
    const SMS_VERIFICATION_ENABLE = 'customerregistraion/general/sms_verification_enable';
    const SSL_VERIFICATION = 'customerregistraion/pos/ssl_verification';
    const DEBUG = 'customerregistraion/pos/debug';
    const XML_PATH_LIST_OF_CHARACTERS = 'customerregistraion/validation/list_of_character';
    const EXTENSION_ENABLE = 'customerregistraion/general/active';
    const BA_CODE_ENABLE = 'customerregistraion/general/ba_code_enable';
    const BA_CODE_PREFIX = 'customerregistraion/general/ba_code_prefix';
    const MINIMUM_LENGTH_BA_CODE = 'customerregistraion/general/minimum_length_ba_code';
    const MAXIMUM_LENGTH_BA_CODE = 'customerregistraion/general/maximum_length_ba_code';
    const WARNING_MESSAGE_VERIFCATION_MOBILE = 'customerregistraion/general/warning_message_verification_mobile';

    /**
     * Get cms block id set in setting
     * Get cms block id set in setting from admin setting
     *
     * @return null|string
     */
    public function getTermsCMSBlockId()
    {
        return $this->scopeConfig->getValue(
            self::POS_TERMS_CMS_BLOCK_ID,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get cms block id set in setting
     * Get cms block id set in setting from admin setting
     *
     * @return null|string
     */
    public function getPosAlertCMSBlockId()
    {
        return $this->scopeConfig->getValue(
            self::POS_ALERT_CMS_BLOCK_ID,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get CODE EXPIRATION TIME IN MINUTES
     * Get code expiration time in munites set in setting from admin setting
     *
     * @return null|int
     */
    public function getCodeExpirationTimeInMinutes()
    {
        return $this->scopeConfig->getValue(
            self::CODE_EXPIRATION_TIME_IN_MINUTES,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get minimum mobile number digits allowed
     * Get minimum mobile number digits allowed set in setting from admin setting
     *
     * @return null|int
     */
    public function getMinimumMobileNumberDigits()
    {
        return $this->scopeConfig->getValue(
            self::MINIMUM_MOBILE_NUMBER_DIGITS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get maximum mobile number digits allowed
     * Get maximum mobile number digits allowed set in setting from admin setting
     *
     * @return null|int
     */
    public function getMaximumMobileNumberDigits()
    {
        return $this->scopeConfig->getValue(
            self::MAXIMUM_MOBILE_NUMBER_DIGITS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get membership error cms page
     * It will return cms page id to show a message
     * that other customer alreay using the mobile number
     *
     * @return mixed
     */
    public function getMembershipErrorCmsPage()
    {
        return $this->scopeConfig->getValue(
            self::MEMBERSHIP_ERROR_CMS_PAGE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get membership error cms page
     * It will return cms page id to show a message
     * that customer with same information already exist
     *
     * @return mixed
     */
    public function getDuplicateMembershipCmsPage()
    {
        return $this->scopeConfig->getValue(
            self::DUPLICATE_MEMBERSHIP_CMS_PAGE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }


    /**
     * Get Terms and Services policy CMS block id
     *
     * @return mixed
     */
    public function getTermsAndServicesPolicyCMSBlockId()
    {
        return $this->scopeConfig->getValue(
            self::TERMS_AND_SERVICES_POLICY_CMS_BLOCK,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Terms and Services policy CMS block id
     *
     * @return mixed
     */
    public function getPrivacyPolicyCMSBlockId()
    {
        return $this->scopeConfig->getValue(
            self::PRIVACY_POLICY_CMS_BLOCK,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get whether SMS verification is enabled on the website or not
     *
     * @return mixed
     */
    public function getSMSVerificationEnable()
    {
        return $this->scopeConfig->getValue(
            self::SMS_VERIFICATION_ENABLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getDebug($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::DEBUG,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::DEBUG,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getSSLVerification()
    {
        return $this->scopeConfig->getValue(
            self::SSL_VERIFICATION,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Config Value
     * This Method is used to get list of not allowed characters
     * @return string
     */
    public function getNotAllowedCharList()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LIST_OF_CHARACTERS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer registration Enable Extension value
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getRegistrationModuleEnable($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::EXTENSION_ENABLE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::EXTENSION_ENABLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get BA Code Enable value
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getBaCodeEnable($websiteId = null)
    {
        $moduleEnable = $this->getRegistrationModuleEnable($websiteId);
        if ($websiteId) {
            if ($moduleEnable) {
                return $this->scopeConfig->getValue(
                    self::BA_CODE_ENABLE,
                    ScopeInterface::SCOPE_WEBSITE,
                    $websiteId
                );
            }
        } else {
            if ($moduleEnable) {
                return $this->scopeConfig->getValue(
                    self::BA_CODE_ENABLE,
                    ScopeInterface::SCOPE_WEBSITE
                );
            }
        }
        return false;
    }

    /**
     * Get BA Code Prefix
     *
     * @param null $websiteId
     * @return mixed
     */
    public function getBaCodePrefix($websiteId = null)
    {
        $moduleEnable = $this->getRegistrationModuleEnable($websiteId);

        if ($moduleEnable) {
            return $this->scopeConfig->getValue(
                self::BA_CODE_PREFIX,
                ScopeInterface::SCOPE_WEBSITE
            );
        }

        return false;
    }

    /**
     * Get minimum length for ba code
     *
     * @param $websiteId
     * @return mixed|void
     */
    public function getMinimumLengthBACode($websiteId = null) {
        $moduleEnable = $this->getRegistrationModuleEnable($websiteId);

        if ($moduleEnable) {
            return $this->scopeConfig->getValue(
                self::MINIMUM_LENGTH_BA_CODE,
                ScopeInterface::SCOPE_WEBSITE
            );
        }
    }

    /**
     * Get maximum length for ba code
     *
     * @param $websiteId
     * @return mixed|void
     */
    public function getMaximumLengthBACode($websiteId = null) {
        $moduleEnable = $this->getRegistrationModuleEnable($websiteId);

        if ($moduleEnable) {
            return $this->scopeConfig->getValue(
                self::MAXIMUM_LENGTH_BA_CODE,
                ScopeInterface::SCOPE_WEBSITE
            );
        }
    }

    /**
     * Get warning message show below mobile
     *
     * @return null|string
     */
    public function getWarningMessageVerficationMobile()
    {
        return $this->scopeConfig->getValue(
            self::WARNING_MESSAGE_VERIFCATION_MOBILE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
