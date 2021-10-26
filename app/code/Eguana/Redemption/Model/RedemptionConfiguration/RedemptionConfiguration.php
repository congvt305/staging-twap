<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 28/10/20
 * Time: 8:19 PM
 */
namespace Eguana\Redemption\Model\RedemptionConfiguration;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * This class is used to get configuration values from Admin Configuration
 *
 * Class RedemptionConfiguration
 */
class RedemptionConfiguration
{
    /**
     * Constant
     */
    const XML_GENERAL_PATH = 'redemption/configuration/';
    const XML_PATH_SEND_SMS_ACTIVE = 'redemption/configuration/send_sms_to_customer_automatically';
    const XML_PATH_MESSAGE_TEMPLATE = 'redemption/configuration/registration_sms_templates';
    const XML_PATH_ENABLED_FRONTEND_RECAPTCHA = 'redemption/configuration/enabled_recaptcha';
    const XML_PATH_RESEND_BUTTON_TIME = 'redemption/configuration/resend_email_sms_time';
    const XML_PATH_MINIMUM_MOBILE_NUMBER_DIGITS = 'redemption/configuration/minimum_mobile_number_digits';
    const XML_PATH_MAXIMUM_MOBILE_NUMBER_DIGITS = 'redemption/configuration/maximum_mobile_number_digits';
    const XML_PATH_PRIVACY_POLICY_TEXT = 'redemption/configuration/privacy_policy_text';
    const XML_PATH_HOME_DELIVERY_ENABLED = 'redemption/configuration/home_delivery_enabled';
    const XML_PATH_FIXED_TEXT_BANNER_ENABLED = 'redemption/configuration/fixed_text_banner_enabled';

    /**
     * @var ScopeInterface
     */
    private $scopeConfig;


    /**
     * Constructor
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * This method is used to check if the option is enable or disable
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getEnableValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Config Value
     * This Method is used to get configuration value on the bases of field parameter
     * @param $field
     * @param null $storeId
     * @return int
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * That class return the value of the field
     *
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getEmail($code, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $code,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * That class return the value of the field
     *
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue($code, $storeId);
    }

    /**
     * This method is used to check if sms is enable or not
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSendSmsActive($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEND_SMS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * This method is used to get the sms template
     *
     * @param null $storeId
     * @return mixed
     */
    public function getMessageTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MESSAGE_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * This method is used to check if the Google Recaptcha is enable for Redemption
     *
     * @return bool
     */
    public function isEnabledFrontendRecaptcha()
    {
        //To-do check module recaptcha
        return false;

        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_FRONTEND_RECAPTCHA,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * This method is used to get the time after which the resend email and sms button enable
     *
     * @param null $storeId
     * @return string
     */
    public function getTimeForResendEmailButton($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RESEND_BUTTON_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * This method is used to get the minimum mobile number digits
     *
     * @param null $storeId
     * @return string
     */
    public function getMinimumMobileNumberDigits($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MINIMUM_MOBILE_NUMBER_DIGITS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * This method is used to get the maximum mobile number digits
     *
     * @param null $storeId
     * @return string
     */
    public function getMaximumMobileNumberDigits($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAXIMUM_MOBILE_NUMBER_DIGITS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * To get privacy policy
     *
     * @param null $storeId
     * @return string
     */
    public function getPrivacyPolicy($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRIVACY_POLICY_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getHomeDeliveryEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOME_DELIVERY_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getFixedTextBannerEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FIXED_TEXT_BANNER_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
