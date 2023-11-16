<?php

namespace Amasty\SeoRichData\Block;

use Amasty\SeoRichData\Helper\Config as ConfigHelper;
use Amasty\SeoRichData\Model\ConfigProvider;
use Amasty\SeoRichData\Model\Review\GetAggregateRating;
use Amasty\SeoRichData\Model\Review\GetReviews;
use Amasty\SeoRichData\Model\Source\Product\Description as DescriptionSource;
use Amasty\SeoRichData\Model\Source\Product\OfferItemCondition as OfferItemConditionSource;
use DateTime;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

class Product extends AbstractBlock
{
    public const IN_STOCK = 'https://schema.org/InStock';

    public const OUT_OF_STOCK = 'https://schema.org/OutOfStock';

    public const MPN_IDENTIFIER = 'mpn';
    public const SKU_IDENTIFIER = 'sku';

    public const VISIBILITY = [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH];

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Amasty\SeoRichData\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OfferItemConditionSource
     */
    private $offerItemConditionSource;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var GetReviews
     */
    private $getReviews;

    /**
     * @var GetAggregateRating
     */
    private $getAggregateRating;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        ConfigHelper $configHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        ConfigProvider $configProvider,
        OfferItemConditionSource $offerItemConditionSource,
        ProductResource $productResource,
        GetAggregateRating $getAggregateRating,
        GetReviews $getReviews,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->pageConfig = $pageConfig;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->configHelper = $configHelper;
        $this->imageHelper = $imageHelper;
        $this->dateTime = $dateTime;
        $this->configProvider = $configProvider;
        $this->offerItemConditionSource = $offerItemConditionSource;
        $this->productResource = $productResource;
        $this->getReviews = $getReviews;
        $this->getAggregateRating = $getAggregateRating;
    }

    protected function _toHtml()
    {
        if (!$this->configProvider->isEnabledForProduct()) {
            return '';
        }

        $resultArray = $this->getResultArray();
        $json = json_encode($resultArray);
        $result = "<script type=\"application/ld+json\">{$json}</script>";

        return $result;
    }

    /**
     * @return array
     */
    public function getResultArray()
    {
        /** @var ProductModel $product */
        $product = $this->getProduct();

        if (!$product) {
            $product = $this->coreRegistry->registry('current_product');
        }

        $offers = $this->prepareOffers($product);
        $offers = $this->unsetUnnecessaryData($offers);
        $image = $this->imageHelper->init(
            $product,
            'product_page_image_medium_no_frame',
            ['type' => 'image']
        )->getUrl();
        $resultArray = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->getName(),
            'description' => $this->stripTags($this->getProductDescription($product)),
            'image' => $image,
            'offers' => $offers,
            'url' => $product->getProductUrl()
        ];

        if ($this->configProvider->isShowRating()) {
            $resultArray['aggregateRating'] = $this->getAggregateRating->execute($product);
            $resultArray['review'] = $this->getReviews->execute((int) $product->getId());
        }

        if ($brandInfo = $this->getBrandInfo($product)) {
            $resultArray['brand'] = $brandInfo;
        }

        if ($manufacturerInfo = $this->getManufacturerInfo($product)) {
            $resultArray['manufacturer'] = $manufacturerInfo;
        }

        $this->updateCustomProperties($resultArray, $product);

        return $resultArray;
    }

    protected function prepareOffers($product)
    {
        $offers = [];
        $priceCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $orgName = $this->storeManager->getStore()->getFrontendName();
        $productType = $product->getTypeId();

        switch ($productType) {
            case ConfigurableType::TYPE_CODE:
            case GroupedType::TYPE_CODE:
                if ($this->configHelper->showAggregate($productType)) {
                    $offers[] = $this->generateAggregateOffers(
                        $this->getSimpleProducts($product),
                        $priceCurrency
                    );
                } elseif ($this->configHelper->showAsList($productType)) {
                    foreach ($this->getSimpleProducts($product) as $child) {
                        $offers[] = $this->generateOffers($child, $priceCurrency, $orgName, $product);
                    }
                } else {
                    $offers[] = $this->generateOffers($product, $priceCurrency, $orgName);
                }
                break;
            default:
                $offers[] = $this->generateOffers($product, $priceCurrency, $orgName);
        }

        return $offers;
    }

    /**
     * @param ProductModel $product
     *
     * @return array
     */
    private function getSimpleProducts($product)
    {
        $list = [];
        $typeInstance = $product->getTypeInstance();

        switch ($product->getTypeId()) {
            case ConfigurableType::TYPE_CODE:
                $list = $typeInstance->getUsedProducts($product);
                break;
            case GroupedType::TYPE_CODE:
                $list = $typeInstance->getAssociatedProducts($product);
                break;
        }

        return $list;
    }

    /**
     * @param $listOfSimples
     * @param string $priceCurrency
     *
     * @return array
     */
    private function generateAggregateOffers($listOfSimples, $priceCurrency)
    {
        $minPrice = INF;
        $maxPrice = 0;
        $offerCount = 0;

        foreach ($listOfSimples as $child) {
            $childPrice = $child->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            $minPrice = min($minPrice, $childPrice);
            $maxPrice = max($maxPrice, $childPrice);
            $offerCount++;
        }

        return [
            '@type' => 'AggregateOffer',
            'lowPrice' => round($minPrice, 2),
            'highPrice' => round($maxPrice, 2),
            'offerCount' => $offerCount,
            'priceCurrency' => $priceCurrency
        ];
    }

    protected function unsetUnnecessaryData($offers)
    {
        if (!$this->configProvider->isShowAvailability()) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['availability'])) {
                    unset($offers[$key]['availability']);
                }
            }
        }

        if (!$this->configHelper->showCondition()) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['itemCondition'])) {
                    unset($offers[$key]['itemCondition']);
                }
            }
        }

        return $offers;
    }

    protected function generateOffers(
        ProductModel $product,
        string $priceCurrency,
        string $orgName,
        ?ProductModel $parentProduct = null
    ): array {
        if ($parentProduct
            && !in_array($this->getProductVisibility($product), self::VISIBILITY)
        ) {
            $productUrl = $parentProduct->getProductUrl();
        } else {
            $productUrl = $product->getProductUrl();
        }

        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $itemConditionValue = $product->hasData(OfferItemConditionSource::ATTRIBUTE_CODE)
            ? (int) $product->getData(OfferItemConditionSource::ATTRIBUTE_CODE)
            : OfferItemConditionSource::NEW_CONDITION;
        $offers = [
            '@type' => 'Offer',
            'priceCurrency' => $priceCurrency,
            'price' => round($price, 2),
            'availability' => $this->getAvailabilityCondition($product),
            'itemCondition' => $this->offerItemConditionSource->getConditionValue($itemConditionValue),
            'seller' => [
                '@type' => 'Organization',
                'name' => $orgName
            ],
            'url' => $productUrl
        ];

        $this->updateCustomProperties($offers, $product);

        if ($this->configProvider->isReplacePriceValidUntil()
            && $product->getSpecialPrice()
            && $this->dateTime->timestamp() < $this->dateTime->timestamp($product->getSpecialToDate())
        ) {
            $offers['priceValidUntil'] = $this->dateTime->date(DateTime::ATOM, $product->getSpecialToDate());
        } elseif ($this->configProvider->getDefaultPriceValidUntil()) {
            $offers['priceValidUntil'] = $this->dateTime->date(
                DateTime::ATOM,
                $this->configProvider->getDefaultPriceValidUntil()
            );
        }

        return $offers;
    }

    private function getProductVisibility(ProductModel $product): int
    {
        $visibility = $product->getVisibility();
        if ($visibility === null) {
            $visibility = $this->productResource->getAttributeRawValue(
                $product->getId(),
                ProductInterface::VISIBILITY,
                $this->storeManager->getStore()->getId()
            );
        }

        return (int) $visibility;
    }

    /**
     * @param ProductModel $product
     *
     * @return array|null
     */
    private function getBrandInfo($product): ?array
    {
        $brand = $this->configHelper->getBrandAttribute();
        if (!$brand) {
            return null;
        }

        $attributeValue = $product->getAttributeText($brand) ?: $product->getData($brand);
        if ($attributeValue) {
            $info = [
                '@type' => 'Brand',
                'name' => $attributeValue
            ];
        }

        return $info ?? null;
    }

    /**
     * @param ProductModel $product
     *
     * @return array|null
     */
    private function getManufacturerInfo($product)
    {
        $info = null;
        $manufacturer = $this->configHelper->getManufacturerAttribute();

        if ($manufacturer && $attributeValue = $product->getAttributeText($manufacturer)) {
            $info = [
                '@type' => 'Organization',
                'name' => $attributeValue
            ];
        }

        return $info;
    }

    /**
     * @param ProductModel $product
     *
     * @return string
     */
    public function getAvailabilityCondition($product)
    {
        $availabilityCondition = $this->stockRegistry->getProductStockStatus($product->getId())
            ? self::IN_STOCK
            : self::OUT_OF_STOCK;

        return $availabilityCondition;
    }

    /**
     * @param array $result
     * @param ProductModel $product
     */
    private function updateCustomProperties(&$result, $product)
    {
        foreach ($this->configHelper->getCustomAttributes() as $pair) {
            $snippetProperty = isset($pair[0]) ? trim($pair[0]) : null;
            $attributeCode = isset($pair[1]) ? trim($pair[1]) : $snippetProperty;

            if ($snippetProperty && $attributeCode) {
                if ($product->getData($attributeCode)) {
                    $result[$snippetProperty] = $product->getAttributeText($attributeCode)
                        ? $product->getAttributeText($attributeCode)
                        : $product->getData($attributeCode);
                }
            }
        }
    }

    /**
     * @param ProductModel $product
     *
     * @return string
     */
    private function getProductDescription($product)
    {
        $description = '';

        switch ($this->configProvider->getProductDescriptionMode()) {
            case DescriptionSource::SHORT_DESCRIPTION:
                $description = $this->getMetaData($product, 'short_description') ?: $product->getShortDescription();
                break;
            case DescriptionSource::FULL_DESCRIPTION:
                $description = $this->getMetaData($product, 'description') ?: $product->getDescription();
                break;
            case DescriptionSource::META_DESCRIPTION:
                $description =  $this->getMetaData($product, 'meta_description')
                    ?: $this->pageConfig->getDescription();
                break;
        }

        return $description;
    }

    /**
     * Value of this method resolved in Amasty_Meta
     *
     * @param ProductModel $product
     * @param string $key
     *
     * @return string
     */
    public function getMetaData($product, $key)
    {
        return '';
    }
}
