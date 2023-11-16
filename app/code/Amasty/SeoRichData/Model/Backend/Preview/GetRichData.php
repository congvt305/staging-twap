<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Backend\Preview;

use Amasty\SeoRichData\Model\ConfigProvider;
use Amasty\SeoRichData\Model\Review\GetAggregateRating;
use Amasty\SeoRichData\Model\Source\Product\Description;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class GetRichData
{
    private const DESCRIPTION_MODE_KEY = 'description_mode';
    private const RATING_KEY = 'rating';
    private const BASE_URL_KEY = 'base_url';
    private const URL_SUFFIX_KEY = 'url_suffix';
    private const PRICE_KEY = 'price';
    private const AVAILABILITY_KEY = 'availability';

    private const DESCRIPTION_MODE_NONE = 'empty_description';
    private const DESCRIPTION_MODE_SHORT = 'product_short_description';
    private const DESCRIPTION_MODE_FULL = 'product_description';
    private const DESCRIPTION_MODE_META = 'meta_description';

    private const RATING_BEST_KEY = 'best_rating';
    private const RATING_VALUE_KEY = 'rating_value';
    private const RATING_REVIEW_COUNT_KEY = 'review_count';

    private const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetAggregateRating
     */
    private $getAggregateRating;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        ConfigProvider $configProvider,
        GetAggregateRating $getAggregateRating,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StockRegistry $stockRegistry,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->configProvider = $configProvider;
        $this->getAggregateRating = $getAggregateRating;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param ProductInterface|Product $product
     * @param int $storeId
     */
    public function execute(ProductInterface $product, int $storeId): array
    {
        $currentStore = $this->storeManager->getStore($storeId);

        $richData = [
            self::DESCRIPTION_MODE_KEY => $this->getDescriptionMode($storeId),
            self::BASE_URL_KEY => $currentStore->getBaseUrl(UrlInterface::URL_TYPE_LINK),
            self::URL_SUFFIX_KEY => (string) $this->scopeConfig->getValue(
                self::XML_PATH_PRODUCT_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        ];

        if ($this->configProvider->isShowRating($storeId)
            && ($aggregateRating = $this->getAggregateRating->execute($product))
        ) {
            $richData[self::RATING_KEY] = [
                self::RATING_VALUE_KEY => $aggregateRating['ratingValue'],
                self::RATING_BEST_KEY => $aggregateRating['bestRating'],
                self::RATING_REVIEW_COUNT_KEY => $aggregateRating['reviewCount']
            ];
        } else {
            $richData[self::RATING_KEY] = [];
        }

        if ($product->isComposite()) {
            if ($storeId === Store::DEFAULT_STORE_ID) {
                $store = $this->storeManager->getDefaultStoreView();
                if ($store !== null) {
                    $product->setStoreId($store->getId());
                }
            } else {
                $product->setStoreId($storeId);
            }
            $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            $richData[self::PRICE_KEY] = $this->priceCurrency->format($price, false);
        } else {
            $richData[self::PRICE_KEY] = 0;
        }

        if ($this->configProvider->isShowAvailability($storeId)) {
            $richData[self::AVAILABILITY_KEY] = $this->stockRegistry->getProductStockStatus(
                (int) $product->getId(),
                (int) $currentStore->getWebsiteId()
            );
        } else {
            $richData[self::AVAILABILITY_KEY] = null;
        }

        return $richData;
    }

    private function getDescriptionMode(int $storeId): string
    {
        $productDescriptionMode = $this->configProvider->getProductDescriptionMode($storeId);
        switch ($productDescriptionMode) {
            case Description::SHORT_DESCRIPTION:
                return self::DESCRIPTION_MODE_SHORT;
            case Description::FULL_DESCRIPTION:
                return self::DESCRIPTION_MODE_FULL;
            case Description::META_DESCRIPTION:
                return self::DESCRIPTION_MODE_META;
            default:
                return self::DESCRIPTION_MODE_NONE;
        }
    }
}
