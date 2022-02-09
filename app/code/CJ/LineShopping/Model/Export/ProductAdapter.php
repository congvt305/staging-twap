<?php

namespace CJ\LineShopping\Model\Export;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsSalableWithReservationsCondition;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Framework\Url;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use CJ\LineShopping\Logger\Logger;
use Magento\Framework\UrlInterface;

class ProductAdapter
{
    const DEFAULT_BRAND = 'Amore Pacific';
    const DEFAULT_AGE_GROUP = 'normal';
    const DEFAULT_PRODUCT_TYPE = 'normal';
    const DEFAULT_IN_STOCK = 'in stock';
    const DEFAULT_DISCONTINUES = 'discontinued';

    /**
     * @var string[]
     */
    protected $productFeedMapping = [
        'product_id' => 'sku',
        'product_name' => 'name',
        'price' => 'price',
        'l_description' => 'short_description',
        'description' => 'short_description'
    ];

    /**
     * @var Url
     */
    protected Url $url;

    /**
     * @var LinkManagementInterface
     */
    protected LinkManagementInterface $linkManagement;

    /**
     * @var IsSalableWithReservationsCondition
     */
    protected IsSalableWithReservationsCondition $isSalableWithReservationsCondition;

    /**
     * @var StockResolverInterface
     */
    protected StockResolverInterface $stockResolver;

    /**
     * @var ProductLinkManagementInterface
     */
    protected ProductLinkManagementInterface $productLinkManagement;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param Url $url
     * @param LinkManagementInterface $linkManagement
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param IsSalableWithReservationsCondition $isSalableWithReservationsCondition
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        Logger $logger,
        Url $url,
        LinkManagementInterface $linkManagement,
        ProductLinkManagementInterface $productLinkManagement,
        IsSalableWithReservationsCondition $isSalableWithReservationsCondition,
        StockResolverInterface $stockResolver
    ) {
        $this->logger = $logger;
        $this->stockResolver = $stockResolver;
        $this->isSalableWithReservationsCondition = $isSalableWithReservationsCondition;
        $this->linkManagement = $linkManagement;
        $this->productLinkManagement = $productLinkManagement;
        $this->url = $url;
    }

    /**
     * @param $products
     * @param $website
     * @return array
     */
    public function export($products, $website): array
    {
        $listProduct = [];
        foreach ($products as $product) {
            try {
                switch ($product->getTypeId()) {
                    case 'simple':
                        $data = $this->getDataFromSimpleProduct($product, $website);
                        break;
                    case 'configurable':
                        $data = $this->getDataFromConfigurableProduct($product, $website);
                        break;
                    case 'bundle':
                        $data = $this->getDataFromBundleProduct($product, $website);
                        break;
                    default:
                        break;
                }
                if ($data && count($data) > 0) {
                    $data['price'] = (string)round($data['price']);
                    $data['image_link'] = $this->getImageLinkProduct($product, $website);
                    $data['availability'] = self::DEFAULT_IN_STOCK;
                    $categories = $product->getCategoryIds();
                    if ($categories) {
                        $data['product_category_value'] = implode(',', $categories);
                    } else {
                        $data['product_category_value'] = $this->assignToDummyCategory($website->getCode());
                    }
                    if (!$data['l_description']) {
                        $data['l_description'] = $product->getName();
                    }
                    if (!$data['description']) {
                        $data['description'] = $product->getName();
                    }
                    $data['link'] = $this->getProductUrl($product, $website->getDefaultStore());
                    $data['age_group'] =  self::DEFAULT_AGE_GROUP;
                    $data['brand'] =  self::DEFAULT_BRAND;
                    $data['product_type'] =  self::DEFAULT_PRODUCT_TYPE;
                    $listProduct[] = $data;
                }
            } catch (\Exception $exception) {
                $this->logger->error(Logger::EXPORT_FEED_DATA,
                    [
                        'type' => 'Get Data Export',
                        'message' => $exception->getMessage()
                    ]
                );
            }
            $data = [];
        }
        return $listProduct;
    }

    /**
     * @param $websiteCode
     * @return string
     */
    protected function assignToDummyCategory($websiteCode): string
    {
        switch ($websiteCode) {
            case CategoryAdapter::TW_SULWHASOO_WEBSITE_CODE:
                return '01_SULWHASOO';
            case CategoryAdapter::TW_LANEIGE_WEBSITE_CODE:
                return '01_LANEIGE';
            default:
                return '';
        }

    }

    /**
     * @param $product
     * @param $store
     * @return string
     */
    protected function getProductUrl($product, $store): string
    {
        if (strpos($product->getProductUrl(), 'catalog/product/view') !== false || strpos($product->getProductUrl(), 'catalog\/product\/view') !== false) {
            $routeParams = ['_nosid' => true, '_query' => ['___store' => $store->getCode()]];
            $routeParams['id'] = $product->getId();
            $routeParams['s'] = $product->getUrlKey();
            $routeParams['_scope'] = $store;
            $routeParams['_secure'] = true;
            $productUrl = $this->url->getUrl('catalog/product/view', $routeParams);
            return $this->reConstructUrl($productUrl);
        }
        $productUrl = $product->getUrlModel()->getUrl($product, ['_secure' => true]);
        return $this->reConstructUrl($productUrl);
    }
    /**
     * Remove url param
     *
     * @param string $url
     * @return string
     */
    protected function reConstructUrl($url): string
    {
        $url = explode('?', $url);
        return $url[0];
    }

    /**
     * @param $product
     * @param $website
     * @return string
     */
    public function getImageLinkProduct($product, $website): string
    {
        return $website->getDefaultStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true) . 'catalog/product' . $product->getImage();
    }

    /**
     * @param $product
     * @param $website
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getDataFromSimpleProduct($product, $website): array
    {
        //Ignore product not visible, price = 0 and out of stock
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE || $product->getPrice() == 0 || !$this->getSalableForSimpleProduct($product, $website)) {
            return [];
        }
        $data = [];
        foreach ($this->productFeedMapping as $key => $value) {
            $data[$key] = $product->getData($value);
        }
        if ($product->getFinalPrice() && $product->getFinalPrice() > 0 && $product->getFinalPrice() < $product->getPrice()) {
            $data['sale_price'] = (string)round($product->getFinalPrice());
        }
        return $data;
    }

    /**
     * @param $product
     * @param $website
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getDataFromConfigurableProduct($product, $website): array
    {
        //Ignore product not visible and out of stock
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE || !$this->getSalableForConfigurableProduct($product, $website)) {
            return [];
        }
        $data = [];
        foreach ($this->productFeedMapping as $key => $value) {
            if ($key == 'price') {
                $price = 0;
                $childrens = $product->getTypeInstance()->getUsedProducts($product);

                /** @var Product $children */
                foreach ($childrens as $children) {
                    if (!$this->getSalableForSimpleProduct($children, $website) || !$children->getStatus()) {
                        continue;
                    }
                    if (!in_array($website->getId(), $children->getWebsiteIds())) {
                        continue;
                    }
                    //get max price from child
                    if ($price <= 0 || $children->getFinalPrice() > $price) {
                        $price = $children->getPrice();
                    }
                }
                if ($price == 0) {
                    return [];
                }
                $data['price'] = $price;
            } else {
                $data[$key] = $product->getData($value);
            }
        }
        return $data;
    }

    /**
     * @param $product
     * @param $website
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getDataFromBundleProduct($product, $website): array
    {
        $data = [];
        foreach ($this->productFeedMapping as $key => $value) {
            if ($key == 'price') {
                $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
                $specialPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
                //Ignore product not visible and out of stock
                if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE || $regularPrice == 0 || !$this->getSalableForBundleProduct($product, $website)) {
                    return [];
                }
                $data['price'] = $regularPrice;
                if($specialPrice < $regularPrice && $specialPrice != 0) {
                    $data['sale_price'] = (string)round($specialPrice);
                }
            } else {
                $data[$key] = $product->getData($value);
            }
        }
        return $data;
    }

    /**
     * @param $product
     * @param $website
     * @param int $requestQty
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getSalableForSimpleProduct($product, $website, int $requestQty = 1): bool
    {
        $sku = $product->getSku();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());
        $stockId = $stock->getStockId();
        $result = $this->isSalableWithReservationsCondition->execute($sku, $stockId, $requestQty);

        return $result->isSalable();
    }

    /**
     * @param $product
     * @param $website
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getSalableForConfigurableProduct($product, $website): bool
    {
        $isSalable = false;
        $products = $this->linkManagement->getChildren($product->getSku());
        if ($products) {
            foreach ($products as $item) {
                $salable = $this->getSalableForSimpleProduct($item, $website);
                if ($salable) {
                    $isSalable = true;
                    break;
                }
            }
        }
        return $isSalable;
    }

    /**
     * @param $product
     * @param $website
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getSalableForBundleProduct($product, $website): bool
    {
        $isSalable = true;
        $products = $this->productLinkManagement->getChildren($product->getSku());
        if ($products) {
            foreach ($products as $item) {
                $salable = $this->getSalableForSimpleProduct($item, $website, $item->getQty());
                if (!$salable) {
                    $isSalable = false;
                    break;
                }
            }
        }
        return $isSalable;
    }
}

