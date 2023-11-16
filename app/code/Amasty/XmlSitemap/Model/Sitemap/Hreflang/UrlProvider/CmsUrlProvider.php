<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap\Hreflang\UrlProvider;

use Amasty\XmlSitemap\Model\ConfigProvider;
use Amasty\XmlSitemap\Model\ResourceModel\Hreflang\Cms\LoadUrls;
use Magento\Store\Model\Store;

class CmsUrlProvider implements UrlProviderInterface
{
    /**
     * @var LoadUrls
     */
    private $loadUrls;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetStoreBaseUrl
     */
    private $getStoreBaseUrl;

    public function __construct(LoadUrls $loadUrls, ConfigProvider $configProvider, GetStoreBaseUrl $getStoreBaseUrl)
    {
        $this->loadUrls = $loadUrls;
        $this->configProvider = $configProvider;
        $this->getStoreBaseUrl = $getStoreBaseUrl;
    }

    public function execute(array $storeIds, string $entityType, array $entityIds): array
    {
        $urlsData = $this->loadUrls->execute($entityIds, $storeIds, $this->configProvider->getHreflangCmsRelation());
        $globalUrlsData = $this->loadUrls->execute(
            $entityIds,
            [Store::DEFAULT_STORE_ID],
            $this->configProvider->getHreflangCmsRelation()
        );

        $convertUrlData = function (array $urlData) {
            $url = $this->getStoreBaseUrl->execute((int) $urlData['store_id']);
            $url .= ltrim($urlData['request_path'], '/');

            return [
                'entity_id' => $urlData['id'],
                'store_id' => (int) $urlData['store_id'],
                'url' => $url
            ];
        };

        $urls = array_map(function (array $urlData) use ($convertUrlData) {
            return $convertUrlData($urlData);
        }, $urlsData);
        foreach ($globalUrlsData as $globalUrlData) {
            foreach ($storeIds as $storeId) {
                $urlData = $globalUrlData; // clone
                $urlData['store_id'] = $storeId;
                $urls[] = $convertUrlData($urlData);
            }
        }

        return $urls;
    }
}
