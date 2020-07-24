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
    const URL_ACTIVE_XML_PATH = 'sap/general/active';

    const SAP_SOURCE_ID_XML_PATH = 'sap/mall_info/source';

    const SAP_MALL_ID_XML_PATH = 'sap/mall_info/mall_id';

    const SAP_CLIENT_XML_PATH = 'sap/mall_info/client';

    const SAP_CVS_SUPPLY_CONTRACTOR_XML_PATH = 'sap/mall_info/cvs_supply_contractor';

    const SAP_HOME_DELIVERY_CONTRACTOR_XML_PATH = 'sap/mall_info/home_delivery_contractor';

    const SAP_SALES_ORG_XML_PATH = 'sap/mall_info/sales_org';

    const SAP_LOGGING_XML_PATH = 'sap/general/logging';

    const SAP_SSL_VERIFICATION_XML_PATH = 'sap/general/ssl_verify_host';

    const SAP_EINVOICE_ENABLE_XML_PATH = 'sap/general/einvoice';

    const SAP_ADDRESS_ENABLE_XML_PATH = 'sap/general/address';

    const SAP_CREDITMEMO_ENABLE_XML_PATH = 'sap/general/creditmemo';

    const SAP_RMA_ENABLE_XML_PATH = 'sap/general/rma';

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

    public function getActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::URL_ACTIVE_XML_PATH, $type, $storeId);
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

    public function getHomeDeliveryContractor($type, $storeId)
    {
        return $this->getValue(self::SAP_HOME_DELIVERY_CONTRACTOR_XML_PATH, $type, $storeId);
    }

    public function getSupplyContractor($type, $storeId)
    {
        return $this->getValue(self::SAP_CVS_SUPPLY_CONTRACTOR_XML_PATH, $type, $storeId);
    }

    public function getSalesOrg($type, $storeId)
    {
        return $this->getValue(self::SAP_SALES_ORG_XML_PATH, $type, $storeId);
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

    public function getSslVerification($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_SSL_VERIFICATION_XML_PATH, $type, $storeId);
    }

    public function getEInvoiceActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_EINVOICE_ENABLE_XML_PATH, $type, $storeId);
    }

    public function getCreditmemoActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_CREDITMEMO_ENABLE_XML_PATH, $type, $storeId);
    }

    public function getAddressActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_ADDRESS_ENABLE_XML_PATH, $type, $storeId);
    }

    public function getRmaActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_RMA_ENABLE_XML_PATH, $type, $storeId);
    }
}
