<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:38 AM
 */

namespace Eguana\Magazine\Helper;

    /**
     * Helper class get the configuration data
     *
     * Class Data
     */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Return the config from config path
     * @param $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
