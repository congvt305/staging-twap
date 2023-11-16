<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntityDataInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntitySourceInterface;
use Magento\Framework\Escaper;

class Extra implements SitemapEntitySourceInterface
{
    public const ENTITY_CODE = 'extra';

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    public function getData(SitemapInterface $sitemap): \Generator
    {
        $sitemapEntityData = $sitemap->getEntityData($this->getEntityCode());

        foreach ($this->getExtraLinks($sitemapEntityData) as $link) {
            yield [
                [
                    self::LOC => $this->escaper->escapeUrl(trim($link)),
                    self::FREQUENCY => $sitemapEntityData->getFrequency(),
                    self::PRIORITY => $sitemapEntityData->getPriority()
                ]
            ];
        }
    }

    private function getExtraLinks(SitemapEntityDataInterface $sitemapEntityData): array
    {
        return $sitemapEntityData->getData(SitemapInterface::EXTRA_LINKS)
            ? explode(PHP_EOL, (string) $sitemapEntityData->getData(SitemapInterface::EXTRA_LINKS))
            : [];
    }

    public function getEntityCode(): string
    {
        return self::ENTITY_CODE;
    }

    public function getEntityLabel(): string
    {
        return __('Extra')->render();
    }
}
