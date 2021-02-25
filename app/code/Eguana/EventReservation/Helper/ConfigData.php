<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/10/20
 * Time: 8:58 PM
 */
namespace Eguana\EventReservation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper class to get configuration values
 *
 * Class ConfigData
 */
class ConfigData extends AbstractHelper
{
    /**#@+
     * Constants for config field paths
     */
    const SMS_TEMPLATE_PATH     = 'event_reservation/sms/reservation_template';
    const GENERAL_CONFIG        = 'event_reservation/configuration/';
    const EMAIL_CONFIG          = 'event_reservation/email/';
    const SMS_CONFIG            = 'event_reservation/sms/';
    /**#@-*/

    /**
     * Get events enabled config value
     *
     * @param $storeId
     * @return mixed
     */
    public function getEventEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_CONFIG . 'enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email sender id from
     *
     * @param $storeId
     * @return mixed
     */
    public function getEmailSenderId($storeId)
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_CONFIG . 'sender_email_identity',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get default staff email config value
     *
     * @param $storeId
     * @return mixed
     */
    public function getStaffEmail($storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CONFIG . 'staff_email',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get staff email auto config value
     *
     * @param $storeId
     * @return mixed
     */
    public function getStaffEmailEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CONFIG . 'staff_email_auto',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get default customer email auto config value
     *
     * @param $storeId
     * @return mixed
     */
    public function getCustomerEmailEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CONFIG . 'customer_email_auto',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get staff email auto config value for admin side
     *
     * @return mixed
     */
    public function getStaffEmailEnabledForAdmin($storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CONFIG . 'staff_email_auto',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get default customer email auto config value for admin side
     *
     * @return mixed
     */
    public function getCustomerEmailEnabledForAdmin($storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CONFIG . 'customer_email_auto',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config value on the bases of path
     *
     * @param $path
     * @param $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config value on the bases of email group path
     *
     * @param $id
     * @param $storeId
     * @return mixed
     */
    public function getEmailGroupConfigValue($id, $storeId)
    {
        return $this->scopeConfig->getValue(
            self::EMAIL_CONFIG . $id,
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
            self::GENERAL_CONFIG . 'privacy_policy_text',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * To get the time after which the resend email and sms button enable
     *
     * @param null $storeId
     * @return mixed
     */
    public function getTimeForResendEmailButton($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_CONFIG . 'resend_email_sms_time',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * To get sms feature is enable or not
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSendSmsEnable($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SMS_CONFIG . 'customer_sms_auto',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
