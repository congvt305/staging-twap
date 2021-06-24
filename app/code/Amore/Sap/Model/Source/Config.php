<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-26
 * Time: 오후 2:45
 */

namespace Amore\Sap\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const URL_ACTIVE_XML_PATH = 'sap/general/active';

    const SAP_SOURCE_ID_XML_PATH = 'sap/mall_info/source';

    const SAP_MALL_ID_XML_PATH = 'sap/mall_info/mall_id';

    const SAP_CLIENT_XML_PATH = 'sap/mall_info/client';

    const SAP_CVS_FAMILY_MART_XML_PATH = 'sap/mall_info/familymart';

    const SAP_CVS_SEVEN_ELEVEN_XML_PATH = 'sap/mall_info/seveneleven';

    const SAP_HOME_DELIVERY_CONTRACTOR_XML_PATH = 'sap/mall_info/home_delivery_contractor';

    const SAP_SALES_ORG_XML_PATH = 'sap/mall_info/sales_org';

    const SAP_LOGGING_XML_PATH = 'sap/general/logging';

    const SAP_SSL_VERIFICATION_XML_PATH = 'sap/general/ssl_verify_host';

    const SAP_EINVOICE_ENABLE_XML_PATH = 'sap/general/einvoice';

    const SAP_ADDRESS_ENABLE_XML_PATH = 'sap/general/address';

    const SAP_CREDITMEMO_ENABLE_XML_PATH = 'sap/general/creditmemo';

    const SAP_RMA_ENABLE_XML_PATH = 'sap/general/rma';

    const SAP_PRODUCT_STOCK_ENABLE_XML_PATH = 'sap/general/product_stock';

    const SAP_PRODUCT_INFO_ENABLE_XML_PATH = 'sap/general/product_info';

    const SAP_PRODUCT_PRICE_ENABLE_XML_PATH = 'sap/general/product_price';

    const SAP_API_BASE_URL_XML_PATH = 'sap/general/url';

    const SAP_INVENTORY_STOCK_URL_XML_PATH = 'sap/url_path/inventory_stock_path';

    const SAP_SKU_PREFIX_XML_PATH = 'sap/mall_info/sku_prefix';

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

    public function getFamilyMartCode($type, $storeId)
    {
        return $this->getValue(self::SAP_CVS_FAMILY_MART_XML_PATH, $type, $storeId);
    }

    public function getSevenElevenCode($type, $storeId)
    {
        return $this->getValue(self::SAP_CVS_SEVEN_ELEVEN_XML_PATH, $type, $storeId);
    }

    public function getSalesOrg($type, $storeId)
    {
        return $this->getValue(self::SAP_SALES_ORG_XML_PATH, $type, $storeId);
    }

    public function getDefaultValue($path)
    {
        return $this->scopeConfig->getValue($path, "default");
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

    public function getProductStockActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_PRODUCT_STOCK_ENABLE_XML_PATH, $type, $storeId);
    }

    public function getProductInfoActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_PRODUCT_INFO_ENABLE_XML_PATH, $type, $storeId);
    }

    public function getProductPriceActiveCheck($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::SAP_PRODUCT_PRICE_ENABLE_XML_PATH, $type, $storeId);
    }

    /**
     * Get Sap Api's Base URL
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSapApiBaseUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SAP_API_BASE_URL_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Sap Inventory Stcok API URL
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSapInventoryStcokUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SAP_INVENTORY_STOCK_URL_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get complete sap stock info api url
     *
     * @param null $storeId
     * @return string
     */
    public function getSapInventoryStcokInfoUrl($storeId = null)
    {
        $url = '';
        $baseUrl = $this->getSapApiBaseUrl($storeId);
        if ($baseUrl) {
            $stockInfoUrl = $this->getSapInventoryStcokUrl($storeId);
            $url = $stockInfoUrl ? $baseUrl . $stockInfoUrl : '';
        }
        return $url;
    }

    /**
     * Get SAP SKU Prefix
     *
     * @param null $storeId
     * @return mixed
     */
    public function getSapSkuPrefix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SAP_SKU_PREFIX_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
