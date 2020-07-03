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
    const DEBUG
        = 'customerregistraion/pos/debug';

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
     * Get Newsletter privacy policy CMS block id
     *
     * @return mixed
     */
    public function getNewsLetterPolicyCMSBlockId()
    {
        return $this->scopeConfig->getValue(
            self::NEWSLETTER_POLICY_CMS_BLOCK,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get SMS privacy policy CMS block id
     *
     * @return mixed
     */
    public function getSMSPolicyCMSBlockId()
    {
        return $this->scopeConfig->getValue(
            self::SMS_POLICY_CMS_BLOCK,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get DM privacy policy CMS block id
     *
     * @return mixed
     */
    public function getDMPolicyCMSBlockId()
    {
        return $this->scopeConfig->getValue(
            self::DM_POLICY_CMS_BLOCK,
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
                self::SALES_OFFICE_CODE,
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
}
