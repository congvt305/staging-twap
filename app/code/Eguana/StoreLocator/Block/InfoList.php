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

use Eguana\StoreLocator\Api\StoreInfo;
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
use Eguana\StoreLocator\Model\StoreInfo as StoreInfoModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * InfoList block
 *
 * Class InfoList
 *  Eguana\StoreLocator\Block
 */
class InfoList extends Template implements IdentityInterface
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
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [StoreInfoModel::CACHE_TAG];
    }

    /**
     * Added breadcrumbs
     * @return $this|InfoList
     */
    public function _prepareLayout()
    {
        try {
            parent::_prepareLayout();

            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Home'),
                        'link' => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                );
                $this->pageConfig->getTitle()->set(__($this->storesHelper->getTitle()));

                $breadcrumbsBlock->addCrumb(
                    $this->storesHelper->getTitle(),
                    [
                        'label' => __($this->storesHelper->getTitle()),
                        'title' => __($this->storesHelper->getTitle())
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $this;
    }

    /**
     * This function will return stores collection
     * @return StoreCollectionFactory
     */
    public function getStoresCollection()
    {
        $id = 0;
        try {
            $id  = $this->_storeManager->getStore()->getId();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        $storeCollection = $this->storeCollectionFactory->create();
        $storeCollection->addStoreFilter($id);
        $storeCollection->addFieldToFilter('area', ['eq' => $this->getStoreCountryCode()]);
        $search = $this->getSelectedSearchTerm();
        $isAddName = $this->addStoreNameFilter($search);
        if ($isAddName) {
            $storeCollection->addFieldToFilter('title', ['like' => '%' . $search . '%']);
        } else {
            $isAddAddress = $this->addStoreAddressFilter($search);
            if ($isAddAddress) {
                $storeCollection->addFieldToFilter('address', ['like' => '%' . $search . '%']);
            } else {
                $storeCollection->addFieldToFilter('title', ['like' => '%' . $search . '%']);
            }
        }
        return $storeCollection;
    }

    /**
     * Search store by address
     * @param $search
     * @return bool
     */
    private function addStoreAddressFilter($search)
    {
        $storeCollection = $this->storeCollectionFactory->create();
        $storeCollection->addFieldToFilter('address', ['like' => '%' . $search . '%']);
        if ($storeCollection->getSize() >= 1) {
            return true;
        }
        return false;
    }

    /**
     * Search store by name
     * @param $search
     * @return bool
     */
    private function addStoreNameFilter($search)
    {
        $storeCollection = $this->storeCollectionFactory->create();
        $storeCollection->addFieldToFilter('title', ['like' => '%' . $search . '%']);
        if ($storeCollection->getSize() >= 1) {
            return true;
        }
        return false;
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
     * Take parameters from request
     * @return mixed
     */
    public function getSelectedAreaOption()
    {
        return $this->getRequest()->getParam('location');
    }

    /**
     * Take parameter of search
     * @return mixed
     */
    public function getSelectedSearchTerm()
    {
        return  $this->getRequest()->getParam('search');
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
     * get id of store which will be used to get individual store
     * @return StoreInfo
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
     * get default country name
     * @return mixed
     */
    public function getDefaultCountryName()
    {
        $code = $this->getStoreCountryCode();
        return $this->countryFactory->create()->loadByCode($code)->getName();
    }

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
     * Get bottom block id
     * @return mixed
     */
    public function getBottomBlockIdValue()
    {
        return $this->storesHelper->getBottomBlockId();
    }
}
