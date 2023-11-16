<?php

namespace Amasty\SeoSingleUrl\Helper;

use Amasty\SeoSingleUrl\Model\ConfigProvider;
use Amasty\SeoSingleUrl\Model\Source\By;
use Amasty\SeoSingleUrl\Model\Source\Type;
use Amasty\SeoSingleUrl\Model\UrlRewrite\Storage;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private const MODULE_PATH = 'amasty_seourl/';

    /**
     * @var null|array
     */
    protected $categoryData;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var Storage
     */
    private $urlFinder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        CollectionFactory $categoryCollectionFactory,
        Storage $urlFinder,
        ConfigProvider $configProvider
    ) {
        parent::__construct($context);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->urlFinder = $urlFinder;
        $this->configProvider = $configProvider;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(self::MODULE_PATH . $path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $product
     * @param $storeId
     *
     * @return string
     */
    public function getSeoUrl($product, $storeId)
    {
        $requestPath = $this->generateSeoUrl($product->getId(), $storeId);
        if ($requestPath) {
            $product->setRequestPath($requestPath);
        }

        return $requestPath;
    }

    /**
     * @param int $productId
     * @param int $storeId
     *
     * @return string
     */
    public function generateSeoUrl($productId, $storeId)
    {
        $filterData = [
            UrlRewrite::ENTITY_ID => $productId,
            UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $storeId,
        ];

        if ($this->configProvider->getProductUrlType($storeId) == Type::NO_CATEGORIES) {
            $rewrite = $this->urlFinder->getUrlWithoutCategory($filterData);

            return $rewrite ? $rewrite->getRequestPath() : '';
        }
        $rewrites = $this->urlFinder->findAllByDataWithoutCategory($filterData);

        $ulrVariants = [];
        $simplePath = '';
        foreach ($rewrites as $rewrite) {
            if ($rewrite->getRedirectType() != '0') {
                continue;//remove old pages with 301 302 redirect
            }

            $path = $rewrite->getRequestPath();
            if (!$simplePath) {
                $simplePath = $path;
            }

            $path = ltrim($path, '/');
            $path = $this->replaceExcludedCategories($path, $storeId);
            if (strpos($path, '/') === false) {
                continue;
            }

            $ulrVariants[] = $path;
        }

        $requestPath = '';
        if ($ulrVariants) {
            $requestPath = $this->getVariantBySetting($ulrVariants, $storeId);
        }

        if (!$requestPath) {
            $requestPath = $simplePath;
        }

        return $requestPath;
    }

    private function getCategoryData($storeId)
    {
        if ($this->categoryData === null) {
            $this->categoryData = [];
            $collection = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('url_key')
                ->addFieldToFilter('entity_id', ['in' => $this->getExcludedCategoryIds()])
                ->setStoreId($storeId);
            foreach ($collection as $category) {
                if ($category->getUrlKey()) {
                    $this->categoryData[] = $category->getUrlKey();
                }
            }
        }

        return $this->categoryData;
    }

    private function replaceExcludedCategories($path, $storeId)
    {
        $categoryUrls = $this->getCategoryData($storeId);
        if ($categoryUrls) {
            $pathArray = explode('/', $path);
            foreach ($categoryUrls as $categoryUrl) {
                $key = array_search($categoryUrl, $pathArray);
                if ($key !== false) {
                    $path = '';
                    break;
                }
            }
        }

        return $path;
    }

    private function getExcludedCategoryIds(): array
    {
        $ids = (string) $this->getModuleConfig('general/exclude');
        $ids = str_replace(' ', '', $ids);

        return explode(',', $ids);
    }

    private function getVariantBySetting(array $urlVariants, ?int $storeId = null): ?string
    {
        $result = [];
        foreach ($urlVariants as $url) {
            $key = ($this->getModuleConfig('general/by') == By::CHARACTER_NUMBER)
                ? strlen($url)
                : count(explode('/', $url));
            if (!isset($result[$key])) {
                $result[$key] = $url;
            }
        }

        ksort($result);
        if ($this->configProvider->getProductUrlType($storeId) == Type::LONGEST) {
            $result = array_reverse($result);
        }

        return reset($result) ?? null;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @deprecated Use ConfigProvider::isProductUseCategories
     * @return bool
     */
    public function isUseCategoriesPath()
    {
        return (bool) $this->getModuleConfig('general/product_use_categories');
    }
}
