<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Redirect\Product;

use Amasty\SeoToolkitLite\Model\Category\PathGetter;
use Amasty\SeoToolkitLite\Model\ConfigProvider;
use Amasty\SeoToolkitLite\Model\Redirect\CreateRedirect;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Takes existing redirects before deletion and creates new ones in our table
 */
class ProcessBeforeDeletion
{
    /**
     * @var UrlRewriteFinder
     */
    private $urlRewriteFinder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CreateRedirect
     */
    private $createRedirect;

    /**
     * @var PathGetter
     */
    private $categoryPathGetter;

    public function __construct(
        UrlRewriteFinder $urlRewriteFinder,
        ConfigProvider $configProvider,
        CreateRedirect $createRedirect,
        PathGetter $categoryPathGetter
    ) {
        $this->urlRewriteFinder = $urlRewriteFinder;
        $this->configProvider = $configProvider;
        $this->createRedirect = $createRedirect;
        $this->categoryPathGetter = $categoryPathGetter;
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @return void
     */
    public function execute(int $entityId, int $storeId): void
    {
        if ($this->configProvider->isRedirectsForDeletedProductsEnabled((int)$storeId)) {
            $currentUrlRewrites = $this->urlRewriteFinder->findAllByData(
                $entityId,
                $storeId,
                ProductUrlRewriteGenerator::ENTITY_TYPE
            );

            foreach ($currentUrlRewrites as $urlRewrite) {
                $this->createRedirect($urlRewrite);
            }
        }
    }

    private function createRedirect(UrlRewrite $urlRewrite): void
    {
        $storeId = (int)$urlRewrite->getStoreId();
        $redirectType = $this->configProvider->getRedirectTypeForProducts($storeId);
        $targetPath = $this->getCategoryPath($urlRewrite);
        $lifetime = $this->configProvider->getRedirectLifetimeForProducts($storeId);
        $this->createRedirect->execute($urlRewrite, $targetPath, $redirectType, $lifetime);
    }

    private function getCategoryPath(UrlRewrite $urlRewrite): string
    {
        $categoryPath = '/';

        if ($metaData = $urlRewrite->getMetadata()) {
            $categoryId = $metaData['category_id'] ?? null;

            if ($categoryId) {
                $categoryPath = $this->categoryPathGetter->execute((int)$categoryId, (int)$urlRewrite->getStoreId());
            }
        }

        return $categoryPath;
    }
}
