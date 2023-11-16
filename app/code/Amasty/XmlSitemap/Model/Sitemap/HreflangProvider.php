<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

use Amasty\XmlSitemap\Model\Sitemap\Hreflang\LanguageCodeProvider;
use Amasty\XmlSitemap\Model\Sitemap\Hreflang\UrlProvider\UrlProviderInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

class HreflangProvider
{
    /**
     * @var LanguageCodeProvider
     */
    private $languageCodeProvider;

    /**
     * @var UrlProviderInterface
     */
    private $urlProvider;

    /**
     * @var string[]
     */
    private $languageCodes;

    public function __construct(
        UrlProviderInterface $urlProvider,
        LanguageCodeProvider $languageCodeProvider
    ) {
        $this->urlProvider = $urlProvider;
        $this->languageCodeProvider = $languageCodeProvider;
    }

    public function getData(int $storeId, string $entityType, array $entityIds): array
    {
        $storeIds = $this->getAffectedStoresIds($storeId);
        $result = [];

        if (empty($storeId)) {
            return $result;
        }
        $urls = $this->urlProvider->execute($storeIds, $entityType, $entityIds);

        foreach ($urls as $urlData) {
            $entityId = $urlData['entity_id'];
            $storeId = $urlData['store_id'];
            $language = $this->getLanguageCode($storeId);

            $result[$entityId][$language] = [
                XmlMetaProvider::ATTRIBUTES => [
                    'hreflang' => $language,
                    'rel' => 'alternate',
                    'href' => $urlData['url']
                ]
            ];
        }

        return $result;
    }

    public function getRewriteUrl(UrlRewrite $urlRewrite): string
    {
        return $urlRewrite->getData('url') ?? '';
    }

    private function getLanguageCode(int $storeId): string
    {
        if (!isset($this->languageCodes)) {
            $this->languageCodes = $this->languageCodeProvider->getData($storeId);
        }

        return $this->languageCodes[$storeId];
    }

    private function getAffectedStoresIds(int $storeId): array
    {
        if (!isset($this->languageCodes)) {
            $this->languageCodes = $this->languageCodeProvider->getData($storeId);
        }

        return array_keys($this->languageCodes);
    }
}
