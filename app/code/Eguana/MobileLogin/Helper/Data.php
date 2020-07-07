<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 22/6/20
 * Time: 6:19 PM
 */
namespace Eguana\MobileLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * Helper class
 */
class Data extends AbstractHelper
{
    const XML_MOBILE_LOGIN_ENABLE = 'MobileLogin/general/mobilelogin_mod_enable';

    /**
     * Check if module is enabled or not
     * @param null $store
     * @return bool
     */
    public function isEnabledInFrontend($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_MOBILE_LOGIN_ENABLE,
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }
}
