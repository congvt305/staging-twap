<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-26
 * Time: 오후 2:45
 */

namespace Amore\Sap\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const SAP_SOURCE_ID_XML_PATH = 'sap/mall_info/source';

    const SAP_MALL_ID_XML_PATH = 'sap/mall_info/mall_id';

    const SAP_CLIENT_XML_PATH = 'sap/mall_info/client';

    const SAP_CVS_SUPPLY_CONTRACTOR_XML_PATH = 'sap/mall_info/cvs_supply_contractor';

    const SAP_LOGGING_XML_PATH = 'sap/general/logging';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getValue($path, $type, $storeId)
    {
        return $this->scopeConfig->getValue($path, $type, $storeId);
    }

    public function getSourceByStore($type ,$storeId)
    {
        return $this->getValue(self::SAP_SOURCE_ID_XML_PATH, $type, $storeId);
    }

    public function getMallId($type, $storeId)
    {
        return $this->getValue(self::SAP_MALL_ID_XML_PATH, $type, $storeId);
    }

    public function getClient($type, $storeId)
    {
        return $this->getValue(self::SAP_CLIENT_XML_PATH, $type, $storeId);
    }

    public function getSupplyContractor($type, $storeId)
    {
        return $this->getValue(self::SAP_CVS_SUPPLY_CONTRACTOR_XML_PATH, $type, $storeId);
    }

    public function getDefaultValue($path)
    {
        return $this->scopeConfig->getValue($path, "default");
    }

    public function checkTestMode()
    {
        return $this->scopeConfig->getValue("sap/test/test_active", "default");
    }

    public function getLoggingCheck()
    {
        return $this->scopeConfig->getValue(self::SAP_LOGGING_XML_PATH, 'default');
    }
}
