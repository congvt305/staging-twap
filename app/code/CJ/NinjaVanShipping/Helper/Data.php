<?php
namespace CJ\NinjaVanShipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    // general
    const XML_PATH_NINJAVAN_ENABLE = 'carriers/ninjavan/active';
    const XML_PATH_NINJAVAN_TRACKING_URL = 'carriers/ninjavan/tracking_url';
    const XML_PATH_NINJAVAN_TITLE = 'carriers/ninjavan/title';
    const XML_PATH_NINJAVAN_NAME = 'carriers/ninjavan/name';
    const XML_PATH_NINJAVAN_INCLUDE_VIRTUAL_PRICE = 'carriers/ninjavan/include_virtual_price';
    const XML_PATH_NINJAVAN_CONDITION_NAME = 'carriers/ninjavan/condition_name';
    const XML_PATH_NINJAVAN_SPECIFICERMSG = 'carriers/ninjavan/specificerrmsg';

    // sandbox
    const XML_PATH_NINJAVAN_SANDBOXMODE = 'ninjavan/sandbox_api/sandbox_mode';
    const XML_PATH_NINJAVAN_HOST = 'ninjavan/sandbox_api/host';
    const XML_PATH_NINJAVAN_HOST_LIVE = 'ninjavan/sandbox_api/host_live';
    const XML_PATH_NINJAVAN_COUNTRYCODE = 'ninjavan/sandbox_api/country_code';
    const XML_PATH_NINJAVAN_CLIENTID = 'ninjavan/sandbox_api/client_id';
    const XML_PATH_NINJAVAN_CLIENTKEY = 'ninjavan/sandbox_api/client_key';
    const XML_PATH_NINJAVAN_CREATE_ORDER = 'ninjavan/sandbox_api/create_order';
    const XML_PATH_NINJAVAN_CANCEL_ORDER = 'ninjavan/sandbox_api/cancel_order';
    const XML_PATH_NINJAVAN_TRACK_URL = 'ninjavan/sandbox_api/track_url';
    const XML_PATH_NINJAVAN_NUM_OF_RETRY = 'ninjavan/sandbox_api/number_of_retry';
    const XML_PATH_NINJAVAN_GENERATE_WAYBILL = 'ninjavan/sandbox_api/generate_waybill';

    const XML_PATH_NINJAVAN_SEND_FROM = 'ninjavan/additional/send_from';
    const XML_PATH_NINJAVAN_PHONE_FROM = 'ninjavan/additional/phone_from';
    const XML_PATH_NINJAVAN_MAIL_FROM = 'ninjavan/additional/mail_from';
    const XML_PATH_NINJAVAN_ADDRESS_FROM = 'ninjavan/additional/address_from';
    const XML_PATH_NINJAVAN_POSTCODE_FROM = 'ninjavan/additional/postcode_from';

    const XML_PATH_NINJAVAN_ALLOWS_ORDER_STATUS_CAN_CANCEL = 'ninjavan/cancel_order_with_nv/allows_order_status_can_cacel';

    public function isNinjaVanEnabled($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isNinjaVanSandboxModeEnabled($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_SANDBOXMODE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanHost($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_HOST, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanHostLive($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_HOST_LIVE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanCountryCode($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_COUNTRYCODE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanUriCreateOrder($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_CREATE_ORDER, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanUriCancelOrder($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_CANCEL_ORDER, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanClientId($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_CLIENTID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanClientKey($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_CLIENTKEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanTrackingUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_TRACKING_URL, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanTitle()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_TITLE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_NAME, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanIncludeVirtualPrice()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_INCLUDE_VIRTUAL_PRICE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanConditionName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_CONDITION_NAME, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanSpecificerrmsg()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_SPECIFICERMSG, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanSendFrom($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_SEND_FROM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanPhoneFrom($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_PHONE_FROM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanMailFrom($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_MAIL_FROM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanAddressFrom($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_ADDRESS_FROM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanPostcodeFrom($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_POSTCODE_FROM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanTrackUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_TRACK_URL, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanNumberRetry()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_NUM_OF_RETRY, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getNinjaVanAllowsOrderStatusCancel($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_ALLOWS_ORDER_STATUS_CAN_CANCEL, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getNinjaVanUriGenerateWaybill()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NINJAVAN_GENERATE_WAYBILL, ScopeInterface::SCOPE_WEBSITE);
    }
}
