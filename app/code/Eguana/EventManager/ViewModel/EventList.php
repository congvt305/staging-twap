<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 30/6/20
 * Time: 12:50 PM
 */
namespace Eguana\EventManager\ViewModel;

use Eguana\EventManager\Model\ResourceModel\EventManager\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Eguana\EventManager\Model\EventManagerConfiguration\EventManagerConfiguration;

/**
 * This class used to add breadcrumbs and title
 *
 * Class EventList
 * Eguana\EventManager\Block
 */
class EventList implements ArgumentInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constant
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var CollectionFactory
     */
    private $eventManagerCollectionFactory;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var EventManagerConfiguration
     */
    private $eventConfiguration;

    /**
     * EventManager constructor.
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlInterface
     * @param StoreManagerInterface $storeManager
     * @param EventManagerRepositoryInterface $eventManagerRepository
     * @param RequestInterface $requestInterface
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param array $data
     * @param EventManagerConfiguration $eventConfiguration
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlInterface,
        EventManagerRepositoryInterface $eventManagerRepository,
        StoreManagerInterface $storeManager,
        RequestInterface $requestInterface,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        EventManagerConfiguration $eventConfiguration,
        array $data = []
    ) {
        $this->eventManagerCollectionFactory = $collectionFactory;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->eventManagerRepository = $eventManagerRepository;
        $this->requestInterface = $requestInterface;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->eventConfiguration = $eventConfiguration;
    }

    /**
     * get store id
     * @return int
     */
    public function getStoreId()
    {
        try {
            return $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
    /**
     * Get Current Event
     *
     * @return mixed
     */
    public function getEventCollection($condition)
    {
        $currentDate = $this->timezone->date()->format(self::DATE_FORMAT);
        $param = $this->requestInterface->getParam('count');
        $sortOrder = 'asc';
        $sortOrderConfigValue = $this->eventConfiguration->getConfigValue(
            EventManagerConfiguration::XML_PATH_SORT_ORDER_FIELD
        );
        if ($sortOrderConfigValue == 1) {
            $sortOrder = 'desc';
        }
        $count =  $this->eventConfiguration->getConfigValue(EventManagerConfiguration::XML_PATH_LOAD_MORE_FIELD);
        if (isset($param)) {
            $count = $param * $count;
        }
        $eventManagerCollection = $this->eventManagerCollectionFactory->create();
        $storeId = $this->storeManager->getStore()->getId();

        $eventManagerCollection->addFieldToFilter(
            "is_active",
            ["eq" => true]
        )->addStoreFilter($storeId)->addFieldToFilter(
            "end_date",
            [$condition => $currentDate]
        )->addFieldToFilter(
            "start_date",
            ['lteq' => $currentDate]
        )->setOrder(
            "entity_id",
            $sortOrder
        );
        $eventManagerCollection->setPageSize($count);
        return $eventManagerCollection;
    }

    /**
     * To get relative url
     * This function will return the full relative url for EventManager
     * @param $urlkey
     * @return string
     */
    public function getEventManagerUrl($urlkey)
    {
        return $this->urlInterface->getUrl() . 'events/detail/index/id/' . $urlkey;
    }

    /**
     * It will return the thumbanil image URL
     *
     * @return string
     */
    public function getThumbnailImageURL($file)
    {
        if ($file == '') {
            return '';
        }
        return $this->getMediaUrl($file);
    }

    /**
     * Get file url
     * @param $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        try {
            $file = ltrim(str_replace('\\', '/', $file), '/');
            return $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $file;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * This method is used to change the date format
     * @param $date
     * @return string
     */
    public function changeDateFormat($date)
    {
        return $this->timezone->date($date)->format(self::DATE_FORMAT);
    }

    /**
     * getEventCondition
     * This method is used to check that which controller is called the return required condition
     * @return string
     */
    public function getEventCondition()
    {
        $controllerName = $this->requestInterface->getFullActionName();
        if ($controllerName == "events_index_index") {
            $condition = "gteq";
        } elseif ($controllerName == "events_previous_index") {
            $condition = "lt";
        }
        return $condition;
    }

    /**
     * getConfigLoadMoreValue
     * This method is used to get value that how many events to load next
     * @return mixed
     */
    public function getConfigLoadMoreValue()
    {
        return $this->eventConfiguration->getConfigValue(
            EventManagerConfiguration::XML_PATH_LOAD_MORE_FIELD
        );
    }
}
