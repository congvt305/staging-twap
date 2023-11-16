<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source;

use Amasty\XmlSitemap\Model\Sitemap\Hreflang\GenerateCombination;
use Amasty\XmlSitemap\Model\Sitemap\SitemapEntityData;
use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntitySourceInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\Source\Product\ImageDataProvider;
use Amasty\XmlSitemap\Model\Source\Product\VariablesResolver;
use Amasty\XmlSitemap\Model\Sitemap\HreflangProvider;
use Generator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Product implements SitemapEntitySourceInterface
{
    public const PAGE_SIZE = 500;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Visibility
     */
    private $visibilityOptionSource;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ImageDataProvider
     */
    private $imageDataProvider;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var VariablesResolver
     */
    private $variablesResolver;

    /**
     * @var HreflangProvider
     */
    private $hreflangProvider;

    /**
     * @var GenerateCombination
     */
    private $generateCombination;

    /**
     * @var Stock
     */
    private $stockHelper;

    public function __construct(
        CollectionFactory $collectionFactory,
        Visibility $visibilityOptionSource,
        Escaper $escaper,
        ImageDataProvider $imageDataProvider,
        DateTime $dateTime,
        VariablesResolver $variablesResolver,
        HreflangProvider $hreflangProvider,
        GenerateCombination $generateCombination,
        Stock $stockHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->visibilityOptionSource = $visibilityOptionSource;
        $this->escaper = $escaper;
        $this->imageDataProvider = $imageDataProvider;
        $this->dateTime = $dateTime;
        $this->variablesResolver = $variablesResolver;
        $this->hreflangProvider = $hreflangProvider;
        $this->generateCombination = $generateCombination;
        $this->stockHelper = $stockHelper;
    }

    public function getData(SitemapInterface $sitemap): Generator
    {
        $sitemapEntityData = $sitemap->getEntityData($this->getEntityCode());
        $collection = $this->getProductCollection();
        $this->applyCollectionFilters($collection, $sitemapEntityData, $sitemap->getStoreId());

        $lastPageNumber = $collection->getLastPageNumber();

        for ($currentPage = 1; $currentPage <= $lastPageNumber; $currentPage++) {
            $collection->setCurPage($currentPage);

            if ($sitemapEntityData->isAddHreflang()) {
                $hreflangs = $this->hreflangProvider->getData(
                    $sitemap->getStoreId(),
                    $this->getEntityCode(),
                    $collection->getAllIds()
                );
            }

            foreach ($collection as $product) {
                /** @var ProductModel $product */
                $data = [
                    self::LOC => $this->escaper->escapeUrl($this->getProductUrl($product, (int)$sitemap->getStoreId())),
                    self::FREQUENCY => $sitemapEntityData->getFrequency(),
                    self::PRIORITY => $sitemapEntityData->getPriority()
                ];

                if ($sitemapEntityData->getData('image')) {
                    $imageData = $this->imageDataProvider->getData($product);

                    if ($sitemapEntityData->getData('image_title') && !empty($imageData)) {
                        if (!isset($imageData['title']) || $imageData['title'] == '') {
                            $template = (string) $sitemapEntityData->getData('image_template');
                            $imageData['title'] = $this->variablesResolver->resolveString($product, $template);
                        }
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
                        $imageData['title'] = htmlspecialchars($imageData['title'], ENT_XML1);
                    } else {
                        unset($imageData['title']);
                    }

                    if ($imageData) {
                        $data['image'] = $imageData;
                    }
                }

                if ($sitemapEntityData->getData('last_modified')) {
                    $updateTime = strtotime($product->getUpdatedAt());
                    $data['last_modified'] = $this->dateTime->date($sitemap->getDateFormat(), $updateTime);
                }

                if ($sitemapEntityData->isAddHreflang() && isset($hreflangs[$product->getId()])) {
                    $data[self::HREFLANG] = $hreflangs[$product->getId()];
                    $data = $this->generateCombination->execute($data);
                } else {
                    $data = [$data];
                }

                yield $data;
            }
            $collection->clear();
        }
    }

    /**
     * point for plugin \Amasty\SeoSingleUrl\Plugin\XmlSitemap\Model\Source\Product\ModifyUrl
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    public function getProductUrl(ProductInterface $product, int $storeId): string
    {
        return (string) $product->getProductUrl(true);
    }

    private function getProductCollection(): ProductCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->setVisibility($this->visibilityOptionSource->getVisibleInSiteIds());
        $collection->setPageSize(self::PAGE_SIZE);
        $collection->addUrlRewrite();
        $collection->addAttributeToSelect('name');

        return $collection;
    }

    private function applyCollectionFilters(
        ProductCollection $collection,
        SitemapEntityData $sitemapEntityData,
        int $storeId
    ): void {
        $collection->setStoreId($storeId);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);

        if ($sitemapEntityData->getData('exclude_out_of_stock')) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }

        if ($sitemapEntityData->getData('exclude_product_type')) {
            $collection->addAttributeToFilter(
                'type_id',
                ['nin' => $sitemapEntityData->getData('exclude_product_type')]
            );
        }
    }

    public function getEntityCode(): string
    {
        return 'product';
    }

    public function getEntityLabel(): string
    {
        return __('Products')->render();
    }
}
