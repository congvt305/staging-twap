<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Block;

use Amasty\XmlSitemap\Model\ConfigProvider;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Amasty\XmlSitemap\Model\Sitemap;
use Amasty\XmlSitemap\Model\Sitemap\UrlProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Model\Config\Value;
use Magento\Store\Model\StoreManagerInterface;

class Robots extends AbstractBlock implements IdentityInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory,
        UrlProvider $urlProvider,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->collectionFactory = $collectionFactory;
        $this->urlProvider = $urlProvider;

        parent::__construct($context, $data);
    }

    protected function _toHtml(): string
    {
        $storesToGenerate = [];
        $website = $this->storeManager->getWebsite();

        foreach ($website->getStoreIds() as $storeId) {
            if ($this->configProvider->isSubmissionRobotsEnabled((int)$storeId)) {
                $storesToGenerate[] = (int)$storeId;
            }
        }
        $sitemapLinks = !empty($storesToGenerate) ? $this->getSitemapLinks($storesToGenerate) : [];

        return $sitemapLinks ? implode(PHP_EOL, $sitemapLinks) . PHP_EOL : '';
    }

    private function getSitemapLinks(array $storesIds): array
    {
        $sitemapLinks = [];
        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter($storesIds);

        /** @var Sitemap $sitemap */
        foreach ($collection as $sitemap) {
            $url = $this->urlProvider->getSitemapUrl($sitemap->getFilePath(), $sitemap->getStoreId());
            if ($url) {
                $sitemapLinks[] = __('Sitemap: %1', $url);
            }
        }

        return $sitemapLinks;
    }

    public function getIdentities(): array
    {
        return [
            Value::CACHE_TAG . '_' . $this->storeManager->getDefaultStoreView()->getId(),
        ];
    }
}
