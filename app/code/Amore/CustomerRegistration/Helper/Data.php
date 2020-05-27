<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
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
 *
 */
class Data extends AbstractHelper
{
    /**
     * store constants
     */
    const POS_TERMS_CMS_BLOCK_ID = 'customerregistraion/general/terms_cms_block_id';
    const CODE_EXPIRATION_TIME_IN_MINUTES = 'customerregistraion/general/code_expiration_time_in_minutes';
    const MINIMUM_MOBILE_NUMBER_DIGITS = 'customerregistraion/general/minimum_mobile_number_digits';
    const MAXIMUM_MOBILE_NUMBER_DIGITS = 'customerregistraion/general/maximum_mobile_number_digits';

    /**
     * Get cms block id set in setting
     * Get cms block id set in setting from admin setting
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
     * @return null|int
     */
    public function getMaximumMobileNumberDigits()
    {
        return $this->scopeConfig->getValue(
            self::MAXIMUM_MOBILE_NUMBER_DIGITS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

}
