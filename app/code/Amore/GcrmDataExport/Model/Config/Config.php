<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * This class is used to get the configurations from store configurations
 *
 * Class Config
 */
class Config
{
    /**#@+
     * Constants for XML Paths.
     */
    const XML_PATH_ENABLE = 'gcrmdataexport/general/enable';
    const XML_PATH_HOST = 'gcrmdataexport/herokudbconfig/heroku_host';
    const XML_PATH_DB = 'gcrmdataexport/herokudbconfig/heroku_database';
    const XML_PATH_USER = 'gcrmdataexport/herokudbconfig/heroku_user';
    const XML_PATH_PORT = 'gcrmdataexport/herokudbconfig/heroku_port';
    const XML_PATH_PASS = 'gcrmdataexport/herokudbconfig/heroku_password';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Value of Configurations from Admin
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * Get Heroku Host config value
     *
     * @param null $storeId
     * @return mixed
     */
    public function getHerokuHost($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_HOST, $storeId);
    }

    /**
     * Get heroku DB name config value
     *
     * @param null $storeId
     * @return mixed
     */
    public function getHerokuDBName($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_DB, $storeId);
    }

    /**
     * Get Heroku user config value
     *
     * @param null $storeId
     * @return mixed
     */
    public function getHerokuUser($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_USER, $storeId);
    }

    /**
     * Get Heroku DB Port config value
     *
     * @param null $storeId
     * @return mixed
     */
    public function getHerokuPort($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_PORT, $storeId);
    }

    /**
     * Get Heroku DB password config value
     *
     * @param null $storeId
     * @return mixed
     */
    public function getHerokuPassword($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_PASS, $storeId);
    }
}
