<?php
namespace CJ\Middleware\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_MIDDLEWARE_ENABLE = 'middleware/general/active';
    const XML_PATH_MIDDLEWARE_URL = 'middleware/general/url';
    const XML_PATH_MIDDLEWARE_USERNAME = 'middleware/general/api_user_id';
    const XML_PATH_MIDDLEWARE_AUTH_KEY = 'middleware/general/auth_key';
    const XML_PATH_MIDDLEWARE_SAP_ORDER_CONFIRM = 'middleware/sap_interface_ids/order_confirm_path';
    const XML_PATH_MIDDLEWARE_SAP_ORDER_CANCEL = 'middleware/sap_interface_ids/order_cancel_path';
    const XML_PATH_MIDDLEWARE_SAP_STOCK_INFO = 'middleware/sap_interface_ids/inventory_stock_path';
    const XML_PATH_MIDDLEWARE_POS_MEMBER_SEARCH = 'middleware/pos_interface_ids/member_search';
    const XML_PATH_MIDDLEWARE_POS_REDEEM_SEARCH = 'middleware/pos_interface_ids/redeem_search';
    const XML_PATH_MIDDLEWARE_POS_CUSTOMER_ORDER = 'middleware/pos_interface_ids/customer_order';
    const XML_PATH_MIDDLEWARE_POS_POINT_SEARCH = 'middleware/pos_interface_ids/point_search';
    const XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_INFO = 'middleware/customer_interface_ids/member_info';
    const XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_JOIN = 'middleware/customer_interface_ids/member_join';
    const XML_PATH_MIDDLEWARE_CUSTOMER_BACODE_INFO = 'middleware/customer_interface_ids/bacode_info';

    public function isNewMiddlewareEnabled($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_ENABLE, $type, $storeId);
    }

    public function getNewMiddlewareURL($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_URL, $type, $storeId);
    }

    public function getMiddlewareUsername($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_USERNAME, $type, $storeId);
    }

    public function getMiddlewareAuthKey($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_AUTH_KEY, $type, $storeId);
    }

    public function getOrderConfirmInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SAP_ORDER_CONFIRM, $type, $storeId);
    }

    public function getOrderCancelInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SAP_ORDER_CANCEL, $type, $storeId);
    }

    public function getStockInfoInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_SAP_STOCK_INFO, $type, $storeId);
    }

    public function getMemberSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_MEMBER_SEARCH, $type, $storeId);
    }

    public function getRedeemSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_REDEEM_SEARCH, $type, $storeId);
    }

    public function getCustomerSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_CUSTOMER_ORDER, $type, $storeId);
    }

    public function getPointSearchInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_POS_POINT_SEARCH, $type, $storeId);
    }

    public function getMemberInfoInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_INFO, $type, $storeId);
    }

    public function getMemberJoinInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_CUSTOMER_MEMBER_JOIN, $type, $storeId);
    }

    public function getBacodeInfoInterfaceId($type, $storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MIDDLEWARE_CUSTOMER_BACODE_INFO, $type, $storeId);
    }
}
