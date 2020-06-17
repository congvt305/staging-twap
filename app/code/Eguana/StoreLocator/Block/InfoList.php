<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */
namespace Eguana\StoreLocator\Block;

use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Helper\ConfigData;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory as StoreCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;

/**
 * InfoList block
 *
 * Class InfoList
 *  Eguana\StoreLocator\Block
 */
class InfoList extends Template
{
    /**
     * @var $storeInfoCollection
     */
    private $storeInfoCollection;

    /**
     * @var RegionCollection
     */
    private $_regionCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ConfigData
     */
    private $storesHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepo;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var CollectionFactory
     */
    private $countryCollection;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var StoreCollectionFactory
     */
    private $storeCollectionFactory;

    /**
     * InfoList constructor.
     * @param Context $context
     * @param ConfigData $storesHelper
     * @param RegionCollection $regionCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreInfoRepositoryInterface $storeInfoRepo
     * @param WorkTimeRepositoryInterface $workTimeRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param CollectionFactory $countryCollection
     * @param CountryFactory $countryFactory
     * @param Curl $curl
     * @param Json $json
     * @param ManagerInterface $messageManager
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigData $storesHelper,
        RegionCollection $regionCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreInfoRepositoryInterface $storeInfoRepo,
        SortOrderBuilder $sortOrderBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        CollectionFactory $countryCollection,
        CountryFactory $countryFactory,
        Curl $curl,
        Json $json,
        ManagerInterface $messageManager,
        StoreCollectionFactory $storeCollectionFactory,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->storesHelper = $storesHelper;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeInfoRepo = $storeInfoRepo;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->countryCollection = $countryCollection->create();
        $this->countryFactory = $countryFactory;
        $this->curl = $curl;
        $this->json = $json;
        $this->messageManager = $messageManager;
        $this->storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * This function will return stores collection
     * @return StoreCollectionFactory
     */
    public function getStoresCollection()
    {
        //$types = $this->getSelectedStoreTypes();
        $city = $this->getSelectedCityName();
        $id = 0;
        try {
            $id  = $this->_storeManager->getStore()->getId();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        $storeCollection = $this->storeCollectionFactory->create();
        $storeCollection->addFieldToFilter('store_id', ['in' => [0, (int)$id]]);
        $storeCollection->addFieldToFilter('area', ['eq' => $this->getStoreCountryCode()]);
        $isAddCity = $this->addCityFiters($city);
        if ($isAddCity) {
            $storeCollection->addFieldToFilter('city', ['eq' => $city]);
        }
        $currentLocationPoint = $this->getCustomerLocationPoints();
        if (!empty($currentLocationPoint)) {
            if ($currentLocationPoint['lat'] && $currentLocationPoint['long']) {
                $storeCollection->addDistance($currentLocationPoint, $this->getradius());
                $storeCollection->addOrder('distance', 'ASC');
            } else {
                $this->messageManager->addErrorMessage(__('Please search valid location'));
            }
        }

        return $storeCollection;
    }

    public function getCitiesFromStores()
    {
        $cities = [];
        $storeCollection = $this->storeCollectionFactory->create()->addFieldToSelect('city');
        foreach ($storeCollection as $storeItem) {
            $cities[] = $storeItem['city'];
        }
        return array_unique($cities);
    }

    /**
     * This function will return stores collection after types filters
     * @param $type
     * @param $types
     * @param $storeCollection
     * @return StoreCollectionFactory
     */
    /*private function addTypesFiters($type, $types, $storeCollection)
    {
        if ($types['shop'] || $type == 'shop') {
            $storeCollection->addFilter('type', 'shop', 'eq');
        }
        if ($types['outlet'] || $type == 'outlet') {
            $storeCollection->addFilter('type', 'outlet', 'eq');
        }
        if ($types['retail'] || $type == 'retail') {
            $storeCollection->addFilter('type', 'retail', 'eq');
        }
        if ($types['dutyfree'] || $type == 'dutyfree') {
            $storeCollection->addFilter('type', 'dutyfree', 'eq');
        }

        return $storeCollection;
    }*/

    /**
     * Check city name in collection if exists or not
     * @param $city
     * @return bool
     */
    private function addCityFiters($city)
    {
        $storeCollection = $this->storeCollectionFactory->create();
        $storeCollection->addFieldToSelect('city');
        $citiesList = [];
        foreach ($storeCollection->getData() as $stores) {
            if ($stores['city'] == $city) {
                return true;
            }
        }
        return false;
    }

    /**
     * This function is used too get native pager
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * This function will return location point of customer
     * @return array
     */
    public function getCustomerLocationPoints()
    {
        $currentLat = $this->getSelectedLat();
        $currentLng = $this->getSelectedLng();
        $useCurrentLocation = $this->getUseMyLocation();
        $search = $this->getSelectedSearchTerm();
        $latLong = null;
        $currentLatLong = [];
        if ($search || $useCurrentLocation) {
            if ($search) {
                $search = str_replace(' ', '%20', $search);
                //this is for calculating distance between searced term and stores
                $latLong = $this->getLatLongFromApi($search);
                /*                if ($this->getStoreCountryCode() == 'KR') {
                                    $latLong = $this->getLatLongFromApiNaver($search);
                                }*/
                //if Api response fails due to wrong api key
                if ($latLong == 'error') {
                    $this->messageManager->addErrorMessage(__('your Api key is not valid'));
                    return;
                }
                $currentLatLong['lat'] = $latLong['lat'];
                $currentLatLong['long'] = $latLong['lng'];
            } elseif ($useCurrentLocation) {
                $latLong = true;
                $currentLatLong['lat'] = $currentLat;
                $currentLatLong['long'] = $currentLng;
            }
        }

        return  $currentLatLong;
    }

    /**
     * get image url for displaying on frontend
     * @param $imageName
     * @return string
     */
    public function getImageUrl($imageName)
    {
        $store = null;
        $mediaPath = $this->storesHelper->getMediaPath();
        try {
            $store = $this->_storeManager->getStore();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        if ($store != null) {
            return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $mediaPath . '/' . $imageName;
        }
    }

    /**
     * @param $storeInfoId
     * @return string
     */
    public function getViewUrl($storeInfoId)
    {
        return $this->urlBuilder
            ->getUrl(
                '*/*/view',
                ['storeinfo_id' => $storeInfoId]
            );
    }

    /**
     * This function return number of stores per page
     * @return int
     */
    public function getStoresPerPageLimit()
    {
        $limit = 0;
        try {
            $storeId =$this->_storeManager->getStore()->getId();
            $limit = $this->storesHelper->getPaginationLimit($storeId);
        } catch (\Exception $exception) {
            $this->_logger->error($e->getMessage());
        }

        return $limit;
    }
    /**
     * Take parameters from request
     * @return mixed
     */
    public function getSelectedAreaOption()
    {
        return $this->getRequest()->getParam('location');
    }

    /**
     * Take parameters from request
     * @return mixed
     */
    /*public function getSelectedStoreTypes()
    {
        $shop =  $this->getRequest()->getParam('shop');
        $outlet =  $this->getRequest()->getParam('outlet');
        $retail =  $this->getRequest()->getParam('retail');
        $dutyfree =  $this->getRequest()->getParam('dutyfree');
        $types = ['shop' => $shop, 'outlet' => $outlet, 'retail' => $retail, 'dutyfree' => $dutyfree];
        return $types;
    }*/

    /**
     * Take parameter of search
     * @return mixed
     */
    public function getSelectedSearchTerm()
    {
        if ($this->getUseMyLocation()) {
            /*if ($this->getStoreCountryCode() == 'KR') {
                return $this->getLocationFromApiNaver($this->getSelectedLat(), $this->getSelectedLng());
            }*/
            return $this->getLocationFromApi($this->getSelectedLat(), $this->getSelectedLng());
        }
        return  $this->getRequest()->getParam('search');
    }

    /**
     * Take parameters from request
     * @return mixed
     */
    public function getSelectedAzimuthOption()
    {
        return $this->getRequest()->getParam('azimuth');
    }

    /**
     * fetch the time for each store
     * @param $storeInfoId
     * @return \Eguana\StoreLocator\Api\Data\WorkTimeInterface[]
     */
    public function getStoreWorkTimeCollection($storeInfoId)
    {
        $sortOrder = $this->sortOrderBuilder->setField('sort')->setDirection('ASC')->create();
        $this->searchCriteriaBuilder->addFilter('store_info_id', $storeInfoId, 'eq')->setSortOrders([$sortOrder]);
        $workTime = $this->workTimeRepo->getList($this->searchCriteriaBuilder->create())->getItems();
        return $workTime;
    }

    /**
     * Take parameters from request
     * @return mixed
     */
    public function getMainTitle()
    {
        return $this->storesHelper->getFrontMainTitle();
    }

    /**
     * Take parameters from request
     * @return mixed
     */
    public function getSubTitle()
    {
        return $this->storesHelper->getFrontSubTitle();
    }

    /**
     * get current store default country
     * @return mixed
     */
    public function getStoreCountryCode()
    {
        return $this->storesHelper->getCurrentCountry();
    }

    /**
     * get api key from config field
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->storesHelper->getApiKey();
    }

    /**
     * get api key from config field
     * @return mixed
     */
    public function getNaverClientId()
    {
        return $this->storesHelper->getNaverClientId();
    }

    /**
     * get api secret key from config field
     * @return mixed
     */
    public function getNaverSecretKey()
    {
        return $this->storesHelper->getNaverSecretId();
    }

    /**
     * get id of store which will be used to get individual store
     * @return \Eguana\StoreLocator\Api\StoreInfo
     */
    public function getStore()
    {
        $id = $this->_request->getParam('id');
        $store = null;
        try {
            $store = $this->storeInfoRepo->getById($id);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $store;
    }

    /**
     * get latitude and longitude from gievn serach term
     * @param $search
     * @return bool|mixed
     */
    public function getLatLongFromApi($search)
    {
        try {
            $storeId  = $this->_storeManager->getStore()->getId();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        $key = '&key=';
        $key = $key . $this->storesHelper->getGeoCodeApiKey($storeId);
        $address = 'address=' . $search;
        $apiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?' . $address . $key;
        $header = [
            "accept: application/json",
            "content-type: application/json"
        ];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HTTPHEADER, $header);
        $this->curl->get($apiUrl);
        $response = $this->curl->getBody();
        $response = $this->json->unserialize($response);
        if ($response['status'] == 'REQUEST_DENIED') {
            return "error";
        }
        if ($this->curl->getStatus() == 200 && $response['status'] != 'ZERO_RESULTS') {
            return $response['results'][0]['geometry']['location'];
        } else {
            return false;
        }
    }

    /**
     * get latitude and longitude from gievn serach term
     * @param $search
     * @return bool|mixed
     */
    public function getLocationFromApi($lat, $lng)
    {
        $key = '&key=';
        $key = $key . $this->storesHelper->getApiKey();
        $latlng = 'latlng=' . $lat . ',' . $lng;
        $apiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?' . $latlng . $key;
        $header = [
            "accept: application/json",
            "content-type: application/json"
        ];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HTTPHEADER, $header);
        $this->curl->get($apiUrl);

        $response = $this->curl->getBody();
        $response = $this->json->unserialize($response);
        //check either api success or fails
        if ($response['status'] == 'REQUEST_DENIED') {
            $this->messageManager->addErrorMessage(__('your Api key is not valid'));
            return false;
        }
        if ($this->curl->getStatus() == 200 && $response['status'] != 'ZERO_RESULTS') {
            return $response['results'][0]['formatted_address'];
        } else {
            return false;
        }
    }

    /**
     * get location name from lat long
     * @param $search
     * @return bool|mixed
     */
    /*public function getLocationFromApiNaver($lat, $lng)
    {

        $clientId = $this->storesHelper->getNaverClientId();
        $secretKey = $this->storesHelper->getNaverSecretId();
        $key = '&key=';
        $key = $key . $this->storesHelper->getApiKey();
        //this accept long lat instead of lat long as google maps
        $latlng = $lng . ','. $lat;
        $naverApiUlr = "https://naveropenapi.apigw.ntruss.com/map-reversegeocode/v2/gc";
        $apiUrl = $naverApiUlr . "?coords=" . $latlng. '&output=json';
        $header = [
            "accept: application/json",
            "X-NCP-APIGW-API-KEY-ID: " . $clientId,
            "X-NCP-APIGW-API-KEY: ". $secretKey
        ];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HTTPHEADER, $header);
        $this->curl->get($apiUrl);

        $response = $this->curl->getBody();
        $response = $this->json->unserialize($response);
        if (isset($response['error'])) {
            return false;
        }
        if ($response['status']['name'] == 'ok' && count($response['results']) > 0) {
            return $response['results'][0]['region']['area1']['name'];
        } else {
            return false;
        }
    }*/

    /**
     * get radius for fetching stores in this radius of location
     * @return mixed
     */
    public function getRadiusLimit()
    {
        return $this->storesHelper->getRadiusLimit();
    }

    /**
     * get selected latitude
     * @return mixed
     */
    public function getSelectedLat()
    {
        return $this->_request->getParam('current_lat');
    }

    /**
     * get selected longitude
     * @return mixed
     */
    public function getSelectedLng()
    {
        return $this->_request->getParam('current_lng');
    }

    /**
     * get ue my location
     * @return mixed
     */
    public function getUseMyLocation()
    {
        return $this->_request->getParam('use_my_location');
    }

    /**
     * get radius from param
     * @return mixed
     */
    public function getradius()
    {
        return $this->_request->getParam('radius');
    }

    /**
     * get radius from param
     * @return mixed
     */
    public function getSelectedCityName()
    {
        return $this->_request->getParam('city-name');
    }

    /**
     * get default country name
     * @return mixed
     */
    public function getDefaultCountryName()
    {
        $code = $this->getStoreCountryCode();
        return $this->countryFactory->create()->loadByCode($code)->getName();
    }

    /**
     * get lat long of search term
     * @param $search
     * @return array|bool|string
     */
    /*public function getLatLongFromApiNaver($search)
    {
        $clientId = $this->storesHelper->getNaverClientId();
        $secretKey = $this->storesHelper->getNaverSecretId();
        $search = str_replace('%20', '+', $search);
        $search = str_replace(',', '+', $search);
        $data = [
            'query' => $search
        ];
        $naverApiUlr = "https://naveropenapi.apigw.ntruss.com/map-geocode/v2/geocode";
        $apiUrl =  $naverApiUlr . "?" . http_build_query($data, '');
        $header = [
            "accept: application/json",
            "X-NCP-APIGW-API-KEY-ID: " . $clientId,
            "X-NCP-APIGW-API-KEY: ". $secretKey
        ];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HTTPHEADER, $header);
        $this->curl->get($apiUrl);
        $response = $this->curl->getBody();
        $response = $this->json->unserialize($response);
        if (isset($response['error'])) {

            return "error";
        }
        if ($response['status'] == 'OK' && $response['meta']['totalCount'] > 0) {
            return [
                'lng' => $response['addresses'][0]['x'],
                'lat' => $response['addresses'][0]['y']
            ];
        } else {
            return false;
        }
    }*/

    /**
     * this function will return code of website
     * @return string
     */
    public function getWebsitCode()
    {
        $websiteCode = '';
        try {
            $websiteCode  = $this->_storeManager->getWebsite()->getCode();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $websiteCode;
    }

    /**
     * Get status customerse seach store with adress or not
     * @return int
     **/
    public function isCustomerSearchLocation()
    {
        $useCurrentLocation = $this->getUseMyLocation();
        $search = $this->getSelectedSearchTerm();
        $result = 0;
        if ($search || $useCurrentLocation) {
            $result = 1;
        }

        return $result;
    }
}
