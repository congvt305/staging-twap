<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Api;

use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntityDataInterface;

/**
 * @api
 */
interface SitemapInterface
{
    public const SITEMAP_ID = 'sitemap_id';
    public const NAME = 'name';
    public const PATH = 'path';
    public const MAX_URLS = 'max_urls';
    public const MAX_FILE_SIZE = 'max_file_size';
    public const LAST_GENERATION = 'last_generation';
    public const STORE_ID = 'store_id';
    public const EXCLUDE_URLS = 'exclude_urls';
    public const DATE_FORMAT = 'date_format';
    public const IS_ADDITIONAL_INCLUDE = 'is_additional_include';
    public const IS_SEPARATE_ENTITY = 'is_separate_entity';

    public const EXTRA_LINKS = 'extra_links';
    public const ENTITIES = 'entities';
    public const ENTITIES_LOADED_FLAG = 'entities_loaded';

    public const PERSIST_NAME = 'amasty_xml_sitemap';
    public const SITEMAP_GENERATION = 'amasty_xml_sitemap_generation';

    /**
     * @return int
     */
    public function getSitemapId(): int;

    /**
     * @param int
     */
    public function setSitemapId(int $id): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getFilePath(): string;

    /**
     * @param string
     */
    public function setFilePath(string $filePath): void;

    /**
     * @return int
     */
    public function getMaxUrls(): int;

    /**
     * @param int
     */
    public function setMaxUrls(int $maxUrls): void;

    /**
     * @return int
     */
    public function getMaxFileSize(): int;

    /**
     * @param int
     */
    public function setMaxFileSize(int $maxFileSize): void;

    /**
     * @return string
     */
    public function getLastGeneration(): string;

    /**
     * @param string
     */
    public function setLastGeneration(string $lastGeneration): void;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int
     */
    public function setStoreId(int $storeId): void;

    /**
     * @return array
     */
    public function getUrlsToExclude(): array;

    /**
     * @param array
     */
    public function setUrlsToExclude(array $excludeUrls): void;

    /**
     * @return string
     */
    public function getDateFormat(): string;

    /**
     * @param string
     */
    public function setDateFormat(string $dateFormat): void;

    /**
     * @return bool
     */
    public function isAdditionalInclude(): bool;

    /**
     * @param bool $isAdditionalInclude
     * @return void
     */
    public function setIsAdditionalInclude(bool $isAdditionalInclude): void;

    /**
     * @return bool
     */
    public function isSeparateEntity(): bool;

    /**
     * @param bool $isSeparateEntity
     * @return void
     */
    public function setIsSeparateEntity(bool $isSeparateEntity): void;

    /**
     * @return bool
     */
    public function isEntitiesDataLoaded(): bool;

    /**
     * @param bool
     */
    public function setIsEntitiesDataLoaded(bool $isLoaded): void;

    /**
     * @return null|SitemapEntityDataInterface[]
     */
    public function getEntitiesData(): ?array;

    /**
     * @return array|null
     */
    public function getWriterConfig(): ?array;

    /**
     * @return bool
     */
    public function shouldAddImages(): bool;

    /**
     * @param string $entityCode
     * @return SitemapEntityDataInterface|null
     */
    public function getEntityData(string $entityCode): ?SitemapEntityDataInterface;
}
