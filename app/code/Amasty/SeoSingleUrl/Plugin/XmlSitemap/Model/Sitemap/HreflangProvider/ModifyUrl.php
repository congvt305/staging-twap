<?php

declare(strict_types=1);

namespace Amasty\SeoSingleUrl\Plugin\XmlSitemap\Model\Sitemap\HreflangProvider;

use Amasty\SeoSingleUrl\Helper\Data;
use Amasty\SeoSingleUrl\Model\ConfigProvider;
use Amasty\SeoSingleUrl\Model\Source\Type;
use Amasty\XmlSitemap\Model\Sitemap\Hreflang\UrlProvider\RewriteUrlProvider;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

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

    /**
     * @var string[]
     */
    private $baseUrl = [];

    public function __construct(
        Data $helper,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    public function aroundGetRewriteUrl(
        RewriteUrlProvider $subject,
        \Closure $proceed,
        UrlRewrite $urlRewrite
    ): string {
        $storeId = (int) $urlRewrite->getStoreId();

        return $this->isNeedModifyUrl($urlRewrite, $storeId)
            ? $this->getUrl($urlRewrite, $storeId)
            : $proceed($urlRewrite);
    }

    private function getUrl(UrlRewrite $urlRewrite, ?int $storeId = null): string
    {
        if (!isset($this->baseUrl[$storeId])) {
            $this->baseUrl[$storeId] = $this->storeManager->getStore($storeId)->getBaseUrl();
        }

        return $this->baseUrl[$storeId] . $this->helper->generateSeoUrl($urlRewrite->getEntityId(), $storeId);
    }

    private function isNeedModifyUrl(UrlRewrite $urlRewrite, ?int $storeId = null): bool
    {
        return $urlRewrite->getEntityType() === UrlRewriteFinder::ENTITY_TYPE_PRODUCT
            && $this->configProvider->isProductUseCategories($storeId)
            && $this->configProvider->getProductUrlType($storeId) !== Type::DEFAULT_RULES;
    }
}
