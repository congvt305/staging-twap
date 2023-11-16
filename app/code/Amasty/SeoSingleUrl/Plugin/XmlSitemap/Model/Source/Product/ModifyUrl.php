<?php

declare(strict_types=1);

namespace Amasty\SeoSingleUrl\Plugin\XmlSitemap\Model\Source\Product;

use Amasty\SeoSingleUrl\Helper\Data;
use Amasty\SeoSingleUrl\Model\ConfigProvider;
use Amasty\SeoSingleUrl\Model\Source\Type;
use Amasty\XmlSitemap\Model\Source\Product as ProductSource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;

class ModifyUrl
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Data $helper,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    public function aroundGetProductUrl(
        ProductSource $subject,
        \Closure $proceed,
        ProductInterface $product,
        int $storeId
    ): string {
        return $this->isNeedModifyUrl($storeId)
            ? $this->getUrl($product, $storeId)
            : $proceed($product, $storeId);
    }

    private function getUrl(ProductInterface $product, int $storeId): string
    {
        if (!isset($this->baseUrl[$storeId])) {
            $this->baseUrl[$storeId] = $this->storeManager->getStore($storeId)->getBaseUrl();
        }

        return $this->baseUrl[$storeId] . $this->helper->generateSeoUrl($product->getEntityId(), $storeId);
    }

    private function isNeedModifyUrl(int $storeId): bool
    {
        return $this->configProvider->isProductUseCategories($storeId)
            && $this->configProvider->getProductUrlType($storeId) !== Type::DEFAULT_RULES;
    }
}
