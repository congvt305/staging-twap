<?php
namespace CJ\VLogicOrder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    // general
    const XML_PATH_V_LOGIC_ENABLE = 'carriers/vlogic/active';

    // sandbox
    const XML_PATH_V_LOGIC_HOST = 'vlogic/sandbox_api/host';
    const XML_PATH_V_LOGIC_STORER_CODE = 'vlogic/sandbox_api/storer_code';
    const XML_PATH_V_LOGIC_USERNAME = 'vlogic/sandbox_api/username';
    const XML_PATH_V_LOGIC_PASSWORD = 'vlogic/sandbox_api/password';
    const XML_PATH_V_LOGIC_CREATE_ORDER = 'vlogic/sandbox_api/create_order';
    const XML_PATH_V_LOGIC_SHIPMENT_ACTIVITY_URL = 'vlogic/sandbox_api/shipment_activity_rul';

    // additional
    const XML_PATH_V_LOGIC_INCOTERMS = 'vlogic/additional/incoterms';
    const XML_PATH_V_LOGIC_STORER_SITE_CODE = 'vlogic/additional/storer_site_code';
    const XML_PATH_V_LOGIC_RECIPIENT_CODE = 'vlogic/additional/recipient_code';
    const XML_PATH_V_LOGIC_FULFILlMENT_TYPE = 'vlogic/additional/fulfillment_type';

    /**
     * @return mixed
     */
    public function isVLogicEnabled($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_ENABLE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicHost($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_HOST, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicStorerCode($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_STORER_CODE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicUsername($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_USERNAME, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicCPassword($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_PASSWORD, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicUriCreateOrder($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_CREATE_ORDER, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicUriShipmentActivity($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_SHIPMENT_ACTIVITY_URL, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicIncoterms($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_INCOTERMS, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicStorerSiteCode($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_STORER_SITE_CODE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicRecipientCode($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_RECIPIENT_CODE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getVLogicFulfillmentType($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_V_LOGIC_FULFILlMENT_TYPE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }
}
