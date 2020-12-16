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
    const CODE_EXPIRATION_TIME_IN_MINUTES
        = 'customerregistraion/general/code_expiration_time_in_minutes';
    const MINIMUM_MOBILE_NUMBER_DIGITS
        = 'customerregistraion/general/minimum_mobile_number_digits';
    const MAXIMUM_MOBILE_NUMBER_DIGITS
        = 'customerregistraion/general/maximum_mobile_number_digits';
    const MEMBERSHIP_ERROR_CMS_PAGE
        = 'customerregistraion/general/membership_error_cms_page';
    const DUPLICATE_MEMBERSHIP_CMS_PAGE
        = 'customerregistraion/general/duplicate_membership_cms_page';
    const NEWSLETTER_POLICY_CMS_BLOCK
        = 'customerregistraion/general/newsletter_policy_cms_block';
    const SMS_POLICY_CMS_BLOCK
        = 'customerregistraion/general/sms_policy_cms_block';
    const DM_POLICY_CMS_BLOCK
        = 'customerregistraion/general/dm_policy_cms_block';
    const CALL_POLICY_CMS_BLOCK
        = 'customerregistraion/general/call_policy_cms_block';
    const TERMS_AND_SERVICES_POLICY_CMS_BLOCK
        = 'customerregistraion/general/terms_and_services_policy_cms_block';
    const SMS_VERIFICATION_ENABLE
        = 'customerregistraion/general/sms_verification_enable';
    const POS_BASE_URL
        = 'customerregistraion/pos/base_url';
    const POS_MEMBER_INFO_URL
        = 'customerregistraion/pos/member_info';
    const POS_MEMBER_JOIN_URL
        = 'customerregistraion/pos/member_join';
    const SALES_ORGANIZATION_CODE
        = 'customerregistraion/pos/sales_organization_code';
    const SALES_OFFICE_CODE
        = 'customerregistraion/pos/sales_office_code';
    const PARTNER_ID
        = 'customerregistraion/pos/partner_id';
    const SSL_VERIFICATION
        = 'customerregistraion/pos/ssl_verification';
    const DEBUG
        = 'customerregistraion/pos/debug';
    const XML_PATH_LIST_OF_CHARACTERS
        = 'customerregistraion/validation/list_of_character';
    const EXTENSION_ENABLE = 'customerregistraion/general/active';
    const BA_CODE_ENABLE = 'customerregistraion/general/ba_code_enable';

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

    /**
     * It will return the full url of the POS API to get the customer information
     * @return string
     */
    public function getMemberInfoURL()
    {
        $baseURL = $this->getPOSBaseURL();
        $memberInfoURL = $this->scopeConfig->getValue(
            self::POS_MEMBER_INFO_URL,
            ScopeInterface::SCOPE_WEBSITE
        );

        return $baseURL.$memberInfoURL;
    }

    /**
     * It will return the POS System URL for join customer
     * @return string
     */
    public function getMemberJoinURL()
    {
        $baseURL = $this->getPOSBaseURL();
        $memberJoinURL = $this->scopeConfig->getValue(
            self::POS_MEMBER_JOIN_URL,
            ScopeInterface::SCOPE_WEBSITE
        );

        return $baseURL.$memberJoinURL;
    }

    /**
     * Get the base url of the POS system
     * @return mixed
     */
    private function getPOSBaseURL()
    {
        return $this->scopeConfig->getValue(
            self::POS_BASE_URL,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getOrganizationSalesCode($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::SALES_ORGANIZATION_CODE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::SALES_ORGANIZATION_CODE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getOfficeSalesCode($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::SALES_OFFICE_CODE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::SALES_OFFICE_CODE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getPartnerId($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::PARTNER_ID,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::PARTNER_ID,
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


}
