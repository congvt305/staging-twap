<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntitySourceInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Magento\Framework\ObjectManagerInterface;

class SourceProvider
{
    private const DEFAULT_SOURCES = [
        'product',
        'category',
        'cms',
        'extra'
    ];

    /**
     * @var string[]
     */
    private $sources;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array $sources = []
    ) {
        $this->sources = $sources;
        $this->objectManager = $objectManager;
        $this->init();
    }

    private function init(): void
    {
        $this->sources = array_map(function ($className) {
            return $this->objectManager->get($className);
        }, $this->sources);
    }

    public function getSourcesToGeneration(SitemapInterface $sitemap): array
    {
        return array_filter($this->sources, function ($sourceName) use ($sitemap) {
            $sitemapEntityData = $sitemap->getEntityData($sourceName);

            return $sitemapEntityData && $sitemapEntityData->isEnabled();
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return SitemapEntitySourceInterface[]
     */
    public function getAllSources(): array
    {
        return $this->sources;
    }

    /**
     * @return SitemapEntitySourceInterface|null
     */
    public function getSourceByCode(string $code)
    {
        return $this->sources[$code] ?? null;
    }

    public function getCustomSourcesCodes(): array
    {
        $sources = $this->getSourcesCodes();

        return array_diff($sources, self::DEFAULT_SOURCES);
    }

    public function getDefaultSourcesCodes(): array
    {
        return self::DEFAULT_SOURCES;
    }

    public function getSourcesCodes(): array
    {
        return array_keys($this->sources);
    }
}
