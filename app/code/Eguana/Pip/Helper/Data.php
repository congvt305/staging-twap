<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 24/7/20
 * Time: 4:38 PM
 */
namespace Eguana\Pip\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * Helper class
 */
class Data extends AbstractHelper
{
    const XML_PIP_ENABLE = 'Pip/general/pip_mod_enable';

    /**
     * Check if module is enabled or not
     * @param null $store
     * @return bool
     */
    public function isEnabledInFrontend($store = null)
    {
        $isEnabled = true;
        $enabled = $this->scopeConfig->getValue(self::XML_PIP_ENABLE, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }
}
