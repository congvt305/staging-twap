<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntityDataInterface;
use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntitySourceInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\Sitemap\Hreflang\GenerateCombination;
use Amasty\XmlSitemap\Model\Sitemap\HreflangProvider;
use Amasty\XmlSitemap\Model\Sitemap\SitemapEntityData;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Category implements SitemapEntitySourceInterface
{
    public const ENTITY_CODE = 'category';

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var HreflangProvider
     */
    private $hreflangProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var GenerateCombination
     */
    private $generateCombination;

    /**
     * @var Url
     */
    private $frontendUrlBuilder;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        Escaper $escaper,
        DateTime $dateTime,
        HreflangProvider $hreflangProvider,
        UrlInterface $urlBuilder,
        Url $frontendUrlBuilder,
        GenerateCombination $generateCombination
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->dateTime = $dateTime;
        $this->hreflangProvider = $hreflangProvider;
        $this->urlBuilder = $urlBuilder;
        $this->generateCombination = $generateCombination;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    public function getData(SitemapInterface $sitemap): \Generator
    {
        $sitemapEntityData = $sitemap->getEntityData($this->getEntityCode());
        $storeId = (int) $sitemap->getStoreId();
        $this->frontendUrlBuilder->setScope($storeId);
        $collection = $this->getCategoryCollection($sitemapEntityData, $storeId);

        if ($sitemapEntityData->isAddHreflang()) {
            $hreflangs = $this->hreflangProvider->getData(
                $sitemap->getStoreId(),
                $this->getEntityCode(),
                $collection->getAllIds()
            );
        }

        /** @var CategoryInterface $category */
        foreach ($collection as $category) {
            if (!$category->getRequestPath()) {
                continue;
            }
            $categoryUrl = $this->frontendUrlBuilder->getDirectUrl($category->getRequestPath(), ['_scope' => $storeId]);
            $data = [
                self::LOC => $this->escaper->escapeUrl($categoryUrl),
                self::FREQUENCY => $sitemapEntityData->getFrequency(),
                self::PRIORITY => $sitemapEntityData->getPriority()
            ];

            if ($sitemapEntityData->getData('image')) {
                $imageData = $this->getImageData($sitemapEntityData, $category);
                if ($imageData) {
                    $data['image'] = $imageData;
                }
            }

            if ($sitemapEntityData->getData('last_modified')) {
                $updateTime = strtotime($category->getUpdatedAt());
                $data['last_modified'] = $this->dateTime->date($sitemap->getDateFormat(), $updateTime);
            }

            if ($sitemapEntityData->isAddHreflang() && isset($hreflangs[$category->getId()])) {
                $data['hreflang'] = $hreflangs[$category->getId()];
                $data = $this->generateCombination->execute($data);
            } else {
                $data = [$data];
            }

            yield $data;
        }
    }

    private function getCategoryCollection(
        ?SitemapEntityDataInterface $sitemapEntityData,
        int $storeId
    ): CategoryCollection {
        $collection = $this->collectionFactory->create();
        $collection->addIsActiveFilter();
        $collection->addUrlRewriteToResult();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect('image');
        $collection->addFieldToFilter('entity_id', ['nin' => $sitemapEntityData->getExcludedIds()]);
        $this->applyCollectionFilters($collection, $storeId);

        return $collection;
    }

    private function applyCollectionFilters(CategoryCollection $collection, int $storeId): void
    {
        $rootCategory = $this->getRootCategory($storeId);

        if ($rootCategory) {
            $path = $rootCategory->getPath() . '/%';
            $collection->addAttributeToFilter('path', ['like' => $path]);
        }
    }

    private function getRootCategory(int $storeId): ?CategoryInterface
    {
        try {
            $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
            $rootCategory = $this->categoryRepository->get($rootCategoryId);
        } catch (NoSuchEntityException $e) {
            $rootCategory = null;
        }

        return $rootCategory;
    }

    private function getImageData(SitemapEntityData $sitemapEntityData, CategoryInterface $category): array
    {
        $data = [];

        if ($category->getImageUrl()) {
            if ($sitemapEntityData->getData('thumbnail_caption')) {
                $title = $category->getName();

                if ($title == '') {
                    $title = $sitemapEntityData->getData('caption_template');
                }
                $data['title'] = $this->escaper->escapeHtml($title);
            }
            $data['loc'] = rtrim($this->urlBuilder->getBaseUrl(), '/') . $category->getImageUrl();
        }

        return $data;
    }

    public function getEntityCode(): string
    {
        return self::ENTITY_CODE;
    }

    public function getEntityLabel(): string
    {
        return __('Categories')->render();
    }
}
