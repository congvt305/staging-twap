<?php

namespace CJ\CatalogProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_LIMIT_PRODUCTS = 'catalog/best_seller_products/limit_products';
    const XML_PATH_ALLOW_STORES = 'catalog/best_seller_products/allow_stores';
    const XML_PATH_USE_PRODUCT_SALE = 'catalog/best_seller_products/use_product_sale';
    const XML_PATH_ENABLE_SORT_BEST_SELLERS = 'catalog/best_seller_products/enable_sort_best_sellers';
    const XML_PATH_ENABLE_FILTER_ON_SALE = 'catalog/best_seller_products/enable_filter_on_sale';
    const ON_SALES_ATTRIBUTE_CODE = 'on_sales';
    const RANKING_ATTRIBUTE_CODE = 'ranking';
    const RANKING_STATUS_ATTRIBUTE_CODE = 'ranking_status';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getLimitProducts($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LIMIT_PRODUCTS, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getAllowStores()
    {
        $stores = $this->scopeConfig->getValue(self::XML_PATH_ALLOW_STORES);
        if ($stores && !empty($stores)){
            return explode(',', $stores);
        }
        return [];
    }

    public function getDataUseProductSale($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_USE_PRODUCT_SALE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getEnableSortBestSellers($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_SORT_BEST_SELLERS, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getEnableFilterOnSale($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_FILTER_ON_SALE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
