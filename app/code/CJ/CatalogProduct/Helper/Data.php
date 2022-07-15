<?php

namespace CJ\CatalogProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package CJ\CatalogProduct\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_LIMIT_PRODUCTS = 'catalog/best_seller_products/limit_products';
    const XML_PATH_ALLOW_STORES = 'catalog/best_seller_products/allow_stores';
    const XML_PATH_USE_PRODUCT_SALE = 'catalog/best_seller_products/use_product_sale';
    const ON_SALES_ATTRIBUTE_CODE = 'on_sales';
    const RANKING_ATTRIBUTE_CODE = 'ranking';
    const RANKING_STATUS_ATTRIBUTE_CODE = 'ranking_status';

    /**
     * @param $storeId
     * @return mixed
     */
    public function getLimitProducts($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LIMIT_PRODUCTS, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return array|false|string[]
     */
    public function getAllowStores()
    {
        $stores = $this->scopeConfig->getValue(self::XML_PATH_ALLOW_STORES);
        if ($stores) {
            return explode(',', $stores);
        }
        return [];
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getDataUseProductSale($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_USE_PRODUCT_SALE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
