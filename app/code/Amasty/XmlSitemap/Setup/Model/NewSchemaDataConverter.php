<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Setup\Model;

use Amasty\Base\Model\Serializer;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\Sitemap\SitemapEntityData;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap as Resource;

class NewSchemaDataConverter
{
    public const SITEMAP_ENTITY_SCHEMA_MAPPING = [
        'id' => SitemapInterface::SITEMAP_ID,
        'title' => SitemapInterface::NAME,
        'folder_name' => SitemapInterface::PATH,
        'max_items' => SitemapInterface::MAX_URLS,
        'max_file_size' => SitemapInterface::MAX_FILE_SIZE,
        'store_id' => SitemapInterface::STORE_ID,
        'last_run' => SitemapInterface::LAST_GENERATION,
        'exclude_urls' => SitemapInterface::EXCLUDE_URLS
    ];
    public const OLD_ENTITIES_NAMES_MAPPING = [
        'categories' => 'category',
        'pages' => 'cms',
        'extra' => 'extra',
        'products' => 'product',
        'landing' => 'amasty_landing',
        'brands' => 'amasty_shopbybrand',
        'faq' => 'amasty_faq',
        'navigation' => 'amasty_shopby',
        'blog' => 'amasty_blog_post'
    ];
    public const OLD_ENTITIES_DEFAULT_COLUMNS_MAPPING_PATTERN = [
        '%s_priority' => SitemapEntityData::PRIORITY,
        '%s_frequency' => SitemapEntityData::FREQUENCY,
        '%s' => SitemapEntityData::ENABLED
    ];
    public const OLD_ENTITIES_ADDITIONAL_COLUMNS_MAPPING = [
        'categories' => [
            'categories_modified' => 'last_modified',
            'categories_thumbs' => 'image',
            'categories_captions' => 'caption'
        ],
        'pages' => [
            'pages_modified' => 'last_modified',
            'exclude_cms_aliases' => 'exclude_aliases',
        ],
        'extra' => [
            'extra_links' => SitemapInterface::EXTRA_LINKS
        ],
        'products' => [
            'products_thumbs' => 'image',
            'products_captions' => 'image_title',
            'products_captions_template' => 'image_template',
            'products_modified' => 'last_modified',
            'exclude_out_of_stock' => 'exclude_out_of_stock',
            'exclude_product_type' => 'exclude_product_type'
        ],

    ];
    public const HREFLANG_SCHEMA_MAPPING = [
        'cms' => 'hreflang_cms',
        'product' => 'hreflang_product',
        'category' => 'hreflang_category'
    ];

    /**
     * @var Serializer
     */
    private $jsonSerializer;

    public function __construct(
        Serializer $jsonSerializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function convert(array $flatSitemap): array
    {
        $sitemapId = $flatSitemap['id'] ?? null;
        $result = [];

        if ($sitemapId !== null) {
            $sitemapId = (int) $sitemapId;
            $result[Resource::TABLE_NAME] = $this->convertSitemapData($flatSitemap);
            $result[Resource::ENTITY_DATA_TABLE_NAME] = $this->convertSitemapEntitiesData($flatSitemap, $sitemapId);
        } else {
            throw new \RuntimeException(__('Invalid sitemap data')->render());
        }

        return $result;
    }

    private function convertSitemapData(array $flatSitemap): array
    {
        $sitemapData = [];

        foreach (self::SITEMAP_ENTITY_SCHEMA_MAPPING as $oldColumn => $newColumn) {
            $sitemapData[$newColumn] = $flatSitemap[$oldColumn];
        }

        return $sitemapData;
    }

    private function convertSitemapEntitiesData(array $flatSitemap, int $sitemapId): array
    {
        $result = [];

        foreach (self::OLD_ENTITIES_NAMES_MAPPING as $oldEntityName => $newEntityName) {
            $enabled = $flatSitemap[$oldEntityName] ?? false;
            if (!$enabled) {
                continue;
            }

            $entityData = [
                SitemapInterface::SITEMAP_ID => $sitemapId,
                SitemapEntityData::ENTITY_CODE => $newEntityName,
                SitemapEntityData::ENABLED => $enabled
            ];

            foreach (self::OLD_ENTITIES_DEFAULT_COLUMNS_MAPPING_PATTERN as $mapping => $newColumn) {
                $flatKey = sprintf($mapping, $oldEntityName);
                $entityData[$newColumn] = $flatSitemap[$flatKey];
            }

            if (isset(self::HREFLANG_SCHEMA_MAPPING[$newEntityName])) {
                $hreflangKey = self::HREFLANG_SCHEMA_MAPPING[$newEntityName];
                $entityData[SitemapEntityData::HREFLANG] = $flatSitemap[$hreflangKey] ?? 0;
            } else {
                $entityData[SitemapEntityData::HREFLANG] = 0;
            }

            $additionalColumns = self::OLD_ENTITIES_ADDITIONAL_COLUMNS_MAPPING[$oldEntityName] ?? false;

            if ($additionalColumns) {
                $entityData['additional'] = [];

                foreach ($additionalColumns as $oldColumn => $additionalDataKey) {
                    $entityData['additional'][$additionalDataKey] = $flatSitemap[$oldColumn] ?? null;
                }
                $entityData['additional'] = $this->jsonSerializer->serialize($entityData['additional']);
            } else {
                $entityData['additional'] = '';
            }

            $result[] = $entityData;
        }

        return $result;
    }
}
