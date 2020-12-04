<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 22/9/20
 * Time: 4:53 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * get data of configuration fields by unig there ids
 *
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * Constant
     */
    const XML_PATH_NOTICE = 'ticket_managment/';

    /**
     * That class get configuration values
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
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
        return $this->getConfigValue(self::XML_PATH_NOTICE . $code, $storeId);
    }

    /**
     * That class return the value of the field
     *
     * @param $code
     * @param  $storeId
     * @return mixed
     */
    public function getCustomerEmailEnableValue($code, $storeId)
    {
        return $this->getConfigValue(self::XML_PATH_NOTICE . $code, $storeId);
    }

    /**
     * That class return the value of the field
     *
     * @param $code
     * @param $storeId
     * @return mixed
     */
    public function getEmail($code, $storeId)
    {
        return $this->getConfigValue($code, $storeId);
    }
}
