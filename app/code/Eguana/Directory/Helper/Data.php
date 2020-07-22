<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/3/20
 * Time: 12:48 PM
 */

namespace Eguana\Directory\Helper;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Directory\Helper\Data
{
    const XML_PATH_CITIES_REQUIRED = 'general/city/state_required';
    const XML_PATH_DISPLAY_ALL_CITIES = 'general/city/display_all';
    /**
     * @var \Eguana\Directory\Model\ResourceModel\City\CollectionFactory
     */
    protected $cityCollectionFactory;

    public function __construct(
        \Eguana\Directory\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory,
        Context $context,
        Config $configCacheType,
        Collection $countryCollection,
        CollectionFactory $regCollectionFactory,
        JsonData $jsonHelper,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory
    ) {
        parent::__construct(
            $context,
            $configCacheType,
            $countryCollection,
            $regCollectionFactory,
            $jsonHelper,
            $storeManager,
            $currencyFactory
        );
        $this->cityCollectionFactory = $cityCollectionFactory;
    }

    /**
     * Json representation of cities data
     *
     * @var string
     */
    protected $_cityJson;

    /**
     * Retrieve regions data json
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCityJson()
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
        if (!$this->_cityJson) {
            $cacheKey = 'DIRECTORY_CITY_JSON_STORE' . $this->_storeManager->getStore()->getId();
            $json = $this->_configCacheType->load($cacheKey);
            if (empty($json)) {
                $cities = $this->getCityData();
                $json = $this->jsonHelper->jsonEncode($cities);
                if ($json === false) {
                    $json = 'false';
                }
                $this->_configCacheType->save($json, $cacheKey);
            }
            $this->_cityJson = $json;
        }

        \Magento\Framework\Profiler::stop('TEST: ' . __METHOD__);
        return $this->_cityJson;
    }

    /**
     * Retrieve cities data
     *
     * @return array
     */
    public function getCityData()
    {
        $regions = $this->jsonHelper->jsonDecode($this->getRegionJson());
        $defaultCountry = $this->getDefaultCountry();
        $regionIds = [];
        if (isset($regions[$defaultCountry])) {
            foreach ($regions[$defaultCountry] as $key => $value) {
                $regionIds[] = $key;
            }
        }

        /** @var \Eguana\Directory\Model\ResourceModel\City\Collection $collection */
        $collection = $this->cityCollectionFactory->create();
//        $collection->addFieldToFilter('region_id', ['in' => $regionIds])->load();
        $collection->addAllowedCountriesFilter();
        //Todo system config
        $cities = [
            'config' => [
                'show_all_cities' => $this->isDisplayAllCities(),
                'cities_required' => $this->getCountriesWithCityRequired(),
            ],
        ];
        foreach ($collection as $city) {
            /** @var \Eguana\Directory\Model\City $city */
            if (!$city->getRegionId()) {
                continue;
            }
            $cities[$city->getRegionId()][$city->getCityId()] = [
                'code' => $city->getCode(),
                'name' => (string)__($city->getName()),
            ];
        }
        return $cities;
    }

    /**
     * @param $regionId
     * @param $cityName
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCityCodeByRegionName($regionId, $cityName)
    {
        $cityCode = 0;
        $cityData = $this->jsonHelper->jsonDecode($this->getCityJson());
        if (isset($cityData[$regionId])) {
            foreach ($cityData[$regionId] as $key => $value) {
                if ($value['name'] === $cityName) {
                    $cityCode = $value['code'];
                }
            }
        }
        return $cityCode;
    }

    public function getCountriesWithCityRequired($asJson = false)
    {
        $value = trim(
            $this->scopeConfig->getValue(
                self::XML_PATH_CITIES_REQUIRED,
                ScopeInterface::SCOPE_STORE
            )
        );
        $countryList = preg_split('/\,/', $value, 0, PREG_SPLIT_NO_EMPTY);
        if ($asJson) {
            return $this->jsonHelper->jsonEncode($countryList);
        }
        return $countryList;
    }

    /**
     * Return, whether non-required state should be shown
     *
     * @return bool
     */
    public function isDisplayAllCities()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_ALL_CITIES,
            ScopeInterface::SCOPE_STORE
        );
    }
}
