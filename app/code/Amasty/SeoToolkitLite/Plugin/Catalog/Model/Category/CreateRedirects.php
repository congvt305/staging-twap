<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Plugin\Catalog\Model\Category;

use Amasty\SeoToolkitLite\Model\Category\PathGetter;
use Amasty\SeoToolkitLite\Model\ConfigProvider;
use Amasty\SeoToolkitLite\Model\Redirect\CreateRedirect;
use Amasty\SeoToolkitLite\Model\Redirect\Query\GetListByTargetPathInterface;
use Amasty\SeoToolkitLite\Model\Repository\RedirectRepository;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class CreateRedirects
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
     * @var GetListByTargetPathInterface
     */
    private $getListByTargetPath;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedirectRepository
     */
    private $redirectRepository;

    /**
     * @var PathGetter
     */
    private $categoryPathGetter;

    public function __construct(
        UrlRewriteFinder $urlRewriteFinder,
        ConfigProvider $configProvider,
        CreateRedirect $createRedirect,
        GetListByTargetPathInterface $getListByTargetPath,
        StoreManagerInterface $storeManager,
        RedirectRepository $redirectRepository,
        PathGetter $categoryPathGetter
    ) {
        $this->urlRewriteFinder = $urlRewriteFinder;
        $this->configProvider = $configProvider;
        $this->createRedirect = $createRedirect;
        $this->getListByTargetPath = $getListByTargetPath;
        $this->storeManager = $storeManager;
        $this->redirectRepository = $redirectRepository;
        $this->categoryPathGetter = $categoryPathGetter;
    }

    /**
     * @see Category::beforeDelete()
     *
     * @param Category $category
     * @return void
     */
    public function beforeBeforeDelete(Category $category): void
    {
        foreach ($category->getStoreIds() as $storeId) {
            if ($this->configProvider->isRedirectsForDeletedCategoriesEnabled((int)$storeId)) {
                $currentUrlRewrites = $this->urlRewriteFinder->findAllByData(
                    $category->getEntityId(),
                    $storeId,
                    CategoryUrlRewriteGenerator::ENTITY_TYPE
                );

                foreach ($currentUrlRewrites as $urlRewrite) {
                    $this->createRedirect($urlRewrite, $category);
                }
            }
        }

        $this->updateRedirects($category);
    }

    private function createRedirect(UrlRewrite $urlRewrite, $category): void
    {
        $storeId = (int)$urlRewrite->getStoreId();
        $redirectType = $this->configProvider->getRedirectTypeForCategories($storeId);
        $targetPath = $this->getParentCategoryPath($category, $storeId);
        $lifetime = $this->configProvider->getRedirectLifetimeForCategories($storeId);
        $this->createRedirect->execute($urlRewrite, $targetPath, $redirectType, $lifetime);
    }

    private function getParentCategoryPath(Category $category, int $storeId): string
    {
        $categoryPath = '/';
        $parentCategoryId = (int)$category->getParentId();

        if (!in_array($parentCategoryId, $this->getAllRootIds())) {
            $categoryPath = $this->categoryPathGetter->execute($parentCategoryId, $storeId);
        }

        return $categoryPath;
    }

    private function updateRedirects(Category $category): void
    {
        $path = $category->getUrlPath();
        if ($path != null) {
            $collection = $this->getListByTargetPath->execute($path);

            foreach ($collection as $redirect) {
                $storeId = (int)explode(',', $redirect->getStoreIds())[0];
                $parentCategoryPath = $this->getParentCategoryPath($category, $storeId);
                $redirect->setTargetPath($parentCategoryPath);
                $this->redirectRepository->save($redirect);
            }
        }
    }

    private function getAllRootIds(): array
    {
        $ids = [];

        foreach ($this->storeManager->getGroups() as $group) {
            $ids[] = $group->getRootCategoryId();
        }

        return $ids;
    }
}
