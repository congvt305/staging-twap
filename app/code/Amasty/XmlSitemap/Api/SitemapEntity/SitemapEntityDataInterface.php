<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Api\SitemapEntity;

/**
 * @api
 */
interface SitemapEntityDataInterface
{
    public const ENTITY_CODE = 'entity_code';
    public const ENABLED = 'enabled';
    public const HREFLANG = 'hreflang';
    public const PRIORITY = 'priority';
    public const FREQUENCY = 'frequency';
    public const ADDITIONAL = 'additional';
    public const FILENAME = 'filename';
    public const EXCLUDED_IDS = 'excluded_ids';

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return bool
     */
    public function isAddHreflang(): bool;

    /**
     * @return float
     */
    public function getPriority(): float;

    /**
     * @return string
     */
    public function getFrequency(): string;

    /**
     * @return null|string
     */
    public function getFilename(): ?string;

    /**
     * @return null|array
     */
    public function getExcludedIds(): ?array;
}
