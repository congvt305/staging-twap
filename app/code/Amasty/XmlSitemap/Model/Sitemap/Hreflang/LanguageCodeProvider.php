<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap\Hreflang;

use Amasty\XmlSitemap\Model\ConfigProvider;
use Amasty\XmlSitemap\Model\OptionSource\Country as CountrySource;
use Amasty\XmlSitemap\Model\OptionSource\Language as LanguageSource;
use Magento\Store\Model\StoreManagerInterface;

class LanguageCodeProvider
{
    public const SCOPE_GLOBAL = 0;
    private const CODE_X_DEFAULT = 'x-default';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider
    ) {
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
    }

    public function getData(int $storeId): array
    {
        $languageCodes = [];
        $storesIds = $this->configProvider->isHreflangScopeGlobal()
            ? $this->getAllStoresIds()
            : $this->getAdjacentStoresIds($storeId);
        $languages = $this->getLanguagesByStoreIds($storesIds);
        $countryCodes = $this->getCountriesByStoresIds($storesIds);
        $xDefaultStoreId = $this->getXDefaultStoreId($storeId);

        foreach ($languages as $storeId => $language) {
            if (isset($countryCodes[$storeId]) && $storeId !== $xDefaultStoreId) {
                $language = sprintf('%s-%s', $language, $countryCodes[$storeId]);
            } else {
                $language = self::CODE_X_DEFAULT;
            }
            $languageCodes[$storeId] = $language;
        }

        return $languageCodes;
    }

    private function getXDefaultStoreId(int $currentStoreId): int
    {
        $websiteId = $this->configProvider->isHreflangScopeGlobal()
            ? 0
            : (int) $this->storeManager->getStore($currentStoreId)->getWebsiteId();

        return $this->configProvider->getHreflangXDefaultStoreId($websiteId);
    }

    private function getAllStoresIds(): array
    {
        $stores = $this->storeManager->getStores();
        $stores = $this->removeInactiveStores($stores);

        return array_keys($stores);
    }

    private function getAdjacentStoresIds(int $storeId): array
    {
        $stores = $this->storeManager->getStore($storeId)->getWebsite()->getStores();
        $stores = $this->removeInactiveStores($stores);

        return array_keys($stores);
    }

    private function removeInactiveStores(array $stores): array
    {
        return array_filter($stores, function ($store) {
            return (bool)$store->getIsActive();
        });
    }

    private function getLanguagesByStoreIds(array $storesIds): array
    {
        $languageCodes = [];

        foreach ($storesIds as $storeId) {
            $languageCode = $this->configProvider->getHreflangLanguageCode($storeId);

            if ($languageCode == LanguageSource::DEFAULT_VALUE) {
                $currentLocale = $this->configProvider->getDefaultLocaleCode($storeId);
                $currentLocalArray = explode('_', $currentLocale);
                $languageCode = array_shift($currentLocalArray);
            }

            $languageCodes[$storeId] = $languageCode;
        }

        return $languageCodes;
    }

    private function getCountriesByStoresIds(array $storeIds): array
    {
        $countryCodes = [];

        foreach ($storeIds as $storeId) {
            $countryCode = $this->configProvider->getHreflangCountriesByStoreId($storeId);

            if ($countryCode === CountrySource::DONT_ADD) {

                continue;
            } elseif ($countryCode == CountrySource::DEFAULT_VALUE) {
                $countryCode = $this->configProvider->getDefaultCountryCode($storeId);
            }
            $countryCodes[$storeId] = $countryCode;
        }

        return $countryCodes;
    }
}
