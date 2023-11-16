<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

class XmlMetaProvider
{
    /**
     * @description Keys, which used specifically in generation process
     */
    public const WRAPPER = 'wrapper';
    public const ATTRIBUTES = 'attributes';
    public const SELF_CLOSING_TAG = 'self_closing';

    /**
     * @description 'keyInSource' => 'tag/attribute title in xml'
     */
    public const DEFAULT_ITEM_META = [
        self::WRAPPER => 'url',
        'loc' => 'loc',
        'frequency' => 'changefreq',
        'priority' => 'priority',
        'hreflang' => [
            self::WRAPPER => 'xhtml:link',
            self::SELF_CLOSING_TAG => true,
            self::ATTRIBUTES => [
                'rel' => 'rel',
                'hreflang' => 'hreflang',
                'href' => 'href'
            ]
        ],
    ];

    public const INDEX_META = [
        self::WRAPPER => 'sitemap',
        'loc' => 'loc',
        'lastmod' => 'lastmod'
    ];

    /**
     * @var array
     */
    private $meta;

    public function __construct(
        array $meta = [],
        array $additionalMeta = []
    ) {
        $this->meta = array_merge($meta, $additionalMeta);
    }

    public function getMeta(string $entityType): array
    {
        $entityMeta = $this->meta[$entityType] ?? [];

        return array_merge($entityMeta, self::DEFAULT_ITEM_META);
    }

    public function getIndexMeta(): array
    {
        return self::INDEX_META;
    }
}
