<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntityDataInterface;
use Magento\Framework\DataObject;

class SitemapEntityData extends DataObject implements SitemapEntityDataInterface
{
    public function isEnabled(): bool
    {
        return (bool)$this->_getData(SitemapEntityDataInterface::ENABLED);
    }

    public function getCode(): string
    {
        return $this->_getData(SitemapEntityDataInterface::ENTITY_CODE);
    }

    public function isAddHreflang(): bool
    {
        return (bool)$this->_getData(SitemapEntityDataInterface::HREFLANG);
    }

    public function getPriority(): float
    {
        return (float)$this->_getData(SitemapEntityDataInterface::PRIORITY);
    }

    public function getFrequency(): string
    {
        return $this->_getData(SitemapEntityDataInterface::FREQUENCY);
    }

    public function getFilename(): ?string
    {
        return $this->hasData(SitemapEntityDataInterface::FILENAME)
            ? (string) $this->_getData(SitemapEntityDataInterface::FILENAME)
            : null;
    }

    public function getExcludedIds(): ?array
    {
        return $this->_getData(SitemapEntityDataInterface::EXCLUDED_IDS);
    }
}
