<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntityDataInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Magento\Framework\Model\AbstractModel;

class Sitemap extends AbstractModel implements SitemapInterface
{
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Sitemap::class);
    }

    public function getSitemapId(): int
    {
        return (int)$this->_getData(SitemapInterface::SITEMAP_ID);
    }

    public function setSitemapId(int $id): void
    {
        $this->setData(SitemapInterface::SITEMAP_ID);
    }

    public function getName(): string
    {
        return $this->_getData(SitemapInterface::NAME);
    }

    public function setName(string $name): void
    {
        $this->setData(SitemapInterface::NAME, $name);
    }

    public function getFilePath(): string
    {
        return $this->_getData(SitemapInterface::PATH);
    }

    public function setFilePath(string $filePath): void
    {
        $this->setData(SitemapInterface::PATH, $filePath);
    }

    public function getMaxUrls(): int
    {
        return (int)$this->_getData(SitemapInterface::MAX_URLS);
    }

    public function setMaxUrls(int $maxUrls): void
    {
        $this->setData(SitemapInterface::MAX_URLS, $maxUrls);
    }

    public function getMaxFileSize(): int
    {
        return (int)$this->_getData(SitemapInterface::MAX_FILE_SIZE);
    }

    public function setMaxFileSize(int $maxFileSize): void
    {
        $this->setData(SitemapInterface::MAX_FILE_SIZE, $maxFileSize);
    }

    public function getLastGeneration(): string
    {
        return $this->_getData(SitemapInterface::LAST_GENERATION);
    }

    public function setLastGeneration(string $lastGeneration): void
    {
        $this->setData(SitemapInterface::LAST_GENERATION, $lastGeneration);
    }

    public function getStoreId(): int
    {
        return (int)$this->_getData(SitemapInterface::STORE_ID);
    }

    public function setStoreId(int $storeId): void
    {
        $this->setData(SitemapInterface::STORE_ID, $storeId);
    }

    /**
     * @return string[]
     */
    public function getUrlsToExclude(): array
    {
        $urls = (string) $this->_getData(SitemapInterface::EXCLUDE_URLS);
        $urls = $urls ? array_map('trim', explode(PHP_EOL, $urls)) : [];

        return  $urls;
    }

    public function setUrlsToExclude(array $excludeUrls): void
    {
        $this->setData(SitemapInterface::EXCLUDE_URLS, $excludeUrls);
    }

    public function getDateFormat(): string
    {
        return $this->_getData(SitemapInterface::DATE_FORMAT);
    }

    public function setDateFormat(string $dateFormat): void
    {
        $this->setData(SitemapInterface::DATE_FORMAT, $dateFormat);
    }

    public function isAdditionalInclude(): bool
    {
        return (bool) $this->_getData(SitemapInterface::IS_ADDITIONAL_INCLUDE);
    }

    public function setIsAdditionalInclude(bool $isAdditionalInclude): void
    {
        $this->setData(SitemapInterface::IS_ADDITIONAL_INCLUDE, $isAdditionalInclude);
    }

    public function isSeparateEntity(): bool
    {
        return (bool) $this->_getData(SitemapInterface::IS_SEPARATE_ENTITY);
    }

    public function setIsSeparateEntity(bool $isSeparateEntity): void
    {
        $this->setData(SitemapInterface::IS_SEPARATE_ENTITY, $isSeparateEntity);
    }

    public function isEntitiesDataLoaded(): bool
    {
        return (bool)$this->_getData(SitemapInterface::ENTITIES_LOADED_FLAG);
    }

    public function setIsEntitiesDataLoaded(bool $isLoaded): void
    {
        $this->setData(SitemapInterface::ENTITIES_LOADED_FLAG, $isLoaded);
    }

    /**
     * @return SitemapEntityDataInterface[]|null
     */
    public function getEntitiesData(): ?array
    {
        if ($this->isEntitiesDataLoaded()) {
            $entitiesData = $this->_getData(SitemapInterface::ENTITIES);
        }

        return $entitiesData ?? null;
    }

    public function getEntityData(string $entityCode): ?SitemapEntityDataInterface
    {
        $dataPath = sprintf('%s/%s', SitemapInterface::ENTITIES, $entityCode);

        return $this->getData($dataPath);
    }

    public function getWriterConfig(): ?array
    {
        return [
            SitemapInterface::MAX_URLS => $this->getMaxUrls(),
            SitemapInterface::MAX_FILE_SIZE => $this->getMaxFileSize()
        ];
    }

    public function shouldAddImages(): bool
    {
        return ($this->getEntityData('category') && $this->getEntityData('category')->getData('image'))
            || ($this->getEntityData('product') && $this->getEntityData('product')->getData('image'));
    }
}
