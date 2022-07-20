<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class PaymentGatewayConfig
{
    const DEFAULT_PRICE_DIVIDER_PLUGIN_URL = 'https://gateway.apaylater.com/plugins/magento2/price-divider.js';

    const ACTIVE = 'active';
    const PRICE_DIVIDER_PRODUCT_LIST = 'price_divider_product_list';
    const PRICE_DIVIDER_PRODUCT_DETAIL = 'price_divider_product_detail';
    const PRICE_DIVIDER_PLUGIN_URL = 'price_divider_plugin_url';
    const Country = 'country';
    const API_ENV = 'api_env';
    const API_URL = 'api_url';
    const WEB_URL = 'web_url';
    const MERCHANT_API_KEY = 'merchant_api_key';
    const MERCHANT_API_SECRET = 'merchant_api_secret';
    const PAYMENT_ACTION = 'payment_action';
    const DEBUG_MODE = 'debug_mode';
    const EXCLUDE_CATEGORY = 'exclude_category';
    const ORDER_EMAIL_SEND_BY = 'order_email_send_by';
    const ORDER_STATUS = 'order_status';
    const ORDER_CREATED_WHEN = 'order_created_when';
    const DELETE_ORDERS_WITHOUT_PAYING = 'delete_orders_without_paying';
    const CLEAR_CART_WITHOUT_PAYING = 'clear_cart_without_paying';
    const MAX_SPEND = 'max_spend';

    // const MIN_ORDER_TOTAL = 'min_order_total';
    // const MAX_ORDER_TOTAL = 'max_order_total';
    
    protected $scopeConfig;
    protected $magentoState;
    protected $storeId;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $magentoState
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->magentoState = $magentoState;
        $this->storeId = null;
    }

    public function setStoreId($storeId = null)
    {
        $this->storeId = $storeId;
    }

    public function getApiUrl($path, $query = [])
    {
        return $this->buildUrl(self::API_URL, $path, $query);
    }

    public function getWebUrl($path, $query = [])
    {
        return $this->buildUrl(self::WEB_URL, $path, $query);
    }

    protected function buildUrl($type, $path, $query)
    {
        $apiEnv = $this->getApiEnv();
        $url = $this->getAtomeUrl($apiEnv, $type);
        if (substr($url, -1) !== '/') {
            $url .= '/';
        }
        if ($path && $path[0] === '/') {
            $url .= substr($path, 1);
        } else {
            $url .= $path;
        }
        if ($query) {
            $url = $url . '?' . http_build_query($query, '', '&');
        }
        return $url;
    }

    public function getAtomeUrl($apiEnv, $type)
    {
        $apiUrlArr = [
            'test'       => 'https://api.apaylater.net/v1/',
            'production' => 'https://api.apaylater.com/v1/',
        ];

        if ($type == self::WEB_URL) {
            if ($apiEnv == 'test') {
                $url = 'https://www.apaylater.net/';
            } elseif ($apiEnv == 'production') {
                $url = 'https://www.atome.sg/';
            }
        }else if ($type == self::API_URL) {
            $url  = isset($apiUrlArr[$apiEnv]) ? $apiUrlArr[$apiEnv] : $apiUrlArr['production'];
        }

        if (!isset($url)) {
            throw new \RuntimeException("unknown url type $type/$apiEnv");
        }
        return $url;
    }

    protected function getScopeConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            'payment/' . \Atome\MagentoPayment\Model\PaymentGateway::METHOD_CODE . '/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }
    public function getCountry()
    {
        return $this->getScopeConfigValue(self::Country) ?: 'sg';
    }

    public function getApiEnv()
    {
        return $this->getScopeConfigValue(self::API_ENV);
    }

    public function getMerchantApiKey()
    {
        return $this->cleanString($this->getScopeConfigValue(self::MERCHANT_API_KEY));
    }

    public function getMerchantApiSecret()
    {
        return $this->cleanString($this->getScopeConfigValue(self::MERCHANT_API_SECRET));
    }

    public function getPaymentAction()
    {
        return $this->getScopeConfigValue(self::PAYMENT_ACTION);
    }

    public function isDebugEnabled()
    {
        return (bool)(int)$this->getScopeConfigValue(self::DEBUG_MODE);
    }

    public function isActive()
    {
        return (bool)(int)$this->getScopeConfigValue(self::ACTIVE);
    }

    public function isPriceDividerProductListVisible()
    {
        $v = $this->getScopeConfigValue(self::PRICE_DIVIDER_PRODUCT_LIST);
        return $v === null || (bool)(int)$v;
    }

    public function isPriceDividerProductDetailVisible()
    {
        $v = $this->getScopeConfigValue(self::PRICE_DIVIDER_PRODUCT_DETAIL);
        return $v === null || (bool)(int)$v;
    }

    public function getOrderEmailSendBy()
    {
        return $this->getScopeConfigValue(self::ORDER_EMAIL_SEND_BY);
    }

    public function getOrderStatus()
    {
        return $this->getScopeConfigValue(self::ORDER_STATUS);
    }

    public function getOrderCreatedWhen()
    {
        return $this->getScopeConfigValue(self::ORDER_CREATED_WHEN);
    }

    public function getMaxSpend()
    {
        return (float)$this->cleanString($this->getScopeConfigValue(self::MAX_SPEND));
    }

    public function getDeleteOrdersWithoutPaying()
    {
        $v = $this->getScopeConfigValue(self::DELETE_ORDERS_WITHOUT_PAYING);
        return $v === null || (bool)(int)$v;
    }

    public function getClearCartWithoutPaying()
    {
        $v = $this->getScopeConfigValue(self::CLEAR_CART_WITHOUT_PAYING);
        return (bool)(int)$v;
    }

    public function getMaxOrderTotal()
    {
        // return (int)$this->getScopeConfigValue(self::MAX_ORDER_TOTAL);
        return null;
    }

    public function getMinOrderTotal()
    {
        // return (int)$this->getScopeConfigValue(self::MIN_ORDER_TOTAL);
        return 30; // FIXME: min order total is SGD 30;
    }

    public function getPriceDividerPluginUrl()
    {
        return $this->getScopeConfigValue(self::PRICE_DIVIDER_PLUGIN_URL);
    }

    public function getExcludedCategories()
    {
        return $this->getScopeConfigValue(self::EXCLUDE_CATEGORY);
    }

    private function cleanString($string)
    {
        return preg_replace("/[^-.\w]+/", "", $string);
    }
}
