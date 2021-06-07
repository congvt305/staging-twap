<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 2/6/21
 * Time: 10:45 PM
 */
namespace Eguana\CODInvoice\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class to get system configuration values
 *
 * Class ConfigData
 */
class ConfigData extends AbstractHelper
{
    /**#@+
     * Constants for config field paths
     */
    const GENERAL_CONFIG = 'eguana_cod_invoice/general/';
    /**#@-*/

    /**
     * Get module enabled config value
     *
     * @return mixed
     */
    public function getEventEnabled()
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_CONFIG . 'enabled',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
