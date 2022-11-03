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
    const XML_PATH_IS_ZIPCODE_AUTOFILLED = 'general/country/is_zipcode_autofilled';
    /**
     * @var \Eguana\Directory\Model\ResourceModel\City\CollectionFactory
     */
    protected $cityCollectionFactory;

    /**
     * @var \Eguana\Directory\Model\CityFactory
     */
    protected $cityFactory;

    /**
     * @var \Eguana\Directory\Model\WardFactory
     */
    protected $wardFactory;

    /**
     * @var \Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory
     */
    protected $wardCollectionFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @param \Eguana\Directory\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory
     * @param \Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory $wardCollectionFactory
     * @param \Eguana\Directory\Model\WardFactory $wardFactory
     * @param \Eguana\Directory\Model\CityFactory $cityFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param Context $context
     * @param Config $configCacheType
     * @param Collection $countryCollection
     * @param CollectionFactory $regCollectionFactory
     * @param JsonData $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Eguana\Directory\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory,
        \Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory $wardCollectionFactory,
        \Eguana\Directory\Model\WardFactory $wardFactory,
        \Eguana\Directory\Model\CityFactory $cityFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
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
        $this->wardCollectionFactory = $wardCollectionFactory;
        $this->wardFactory = $wardFactory;
        $this->cityFactory = $cityFactory;
        $this->addressFactory = $addressFactory;
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
                'code' => $city->getCityId(),
                'zipcode' => $city->getCode(),
                'pos_code' => $city->getPosCode(),
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
    public function getCityIdByRegionName($regionId, $cityName)
    {
        $cityId = 0;
        $cityData = $this->jsonHelper->jsonDecode($this->getCityJson());
        if (isset($cityData[$regionId])) {
            foreach ($cityData[$regionId] as $key => $value) {
                if (trim($value['name']) === trim($cityName)) {
                    $cityId = $value['code'];
                }
            }
        }
        return $cityId;
    }

    /**
     * @param $asJson
     * @return array|false|string|string[]
     */
    public function getCountriesWithCityRequired($asJson = false)
    {
        $value = trim(
            $this->scopeConfig->getValue(
                self::XML_PATH_CITIES_REQUIRED,
                ScopeInterface::SCOPE_STORE
            ) ?? ''
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

    /**
     * @return bool
     */
    public function isZipCodeAutofilled()
    {
        $defaultCountry = $this->scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_WEBSITE
        );
        $autofilledCountries = $this->scopeConfig->getValue(
            self::XML_PATH_IS_ZIPCODE_AUTOFILLED,
            ScopeInterface::SCOPE_STORE
        );

        $autofilledCountries = explode(',', $autofilledCountries);

        return in_array($defaultCountry, $autofilledCountries);
    }

    /**
     * Get Customer addrerss by address_id
     * @param $address_id
     * @return mixed
     */
    public function loadCustomerAddress($address_id){
        $address = $this->addressFactory->create()->load($address_id);
        return $address;
    }

    /**
    * Get Wards
    * @param int $cityId
    * @return mixed
    */
    public function getAllWard($cityId = 0){
        $collection = $this->wardCollectionFactory->create()
            ->addFieldToSelect('ward_id')
            ->addFieldToSelect('default_name')
            ->addFieldToSelect('city_id')
            ->setOrder('city_id','ASC')
            ->setOrder('default_name','ASC');
        if($cityId) {
            $collection->addFieldToFilter('city_id', $cityId);
        }
        $collection->load();
        return $collection;
    }

    /**
     * Get Url Ajax to get Ward list
     * @return string
     */
    public function getAjaxWardUrl() {
        return $this->_storeManager->getStore()->getBaseUrl().'custom_directory/account/getWard';
    }

    /**
     * @param $cityId
     * @return string
     */
    public function getDistrictCode($cityId) {
        $cityCode = '';

        if ($cityId) {
            $city = $this->cityFactory->create()->load($cityId);
            if ($city && $city->getId()) {
                $cityCode = $city->getGhnCode();
            }
        }
        return $cityCode;
    }

    /**
     * @param $wardId
     * @return string
     */
    public function getWardCode($wardId) {
        $wardCode = '';

        if ($wardId) {
            $ward = $this->wardFactory->create()->load($wardId);
            if ($ward && $ward->getId()) {
                $wardCode = $ward->getCode();
            }
        }

        return $wardCode;
    }
}
