<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap\Hreflang\UrlProvider;

interface UrlProviderInterface
{
    /**
     * @return array [['entity_id' => 1, 'store_id' => 1, 'url' => ''], ...]
     */
    public function execute(array $storeIds, string $entityType, array $entityIds): array;
}
