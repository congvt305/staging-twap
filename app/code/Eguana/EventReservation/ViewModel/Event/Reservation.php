<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/10/20
 * Time: 10:36 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\ViewModel\Event;

use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Model\Event;
use Eguana\EventReservation\Model\Counter\TimeSlotSeats;
use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This ViewModel is used to Event Reservation Form
 *
 * Class Reservation
 */
class Reservation implements ArgumentInterface
{
    /**#@+
     * Constants for date format.
     */
    const DB_DATE_FORMAT        = 'Y-m-d';
    const COUNTER_DATE_FORMAT   = 'm/d';
    /**#@-*/

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimeSlotSeats
     */
    private $timeSlotSeats;

    /**
     * @var CollectionFactory
     */
    private $storeInfoCollectionFactory;

    /**
     * @param Http $request
     * @param DateTime $dateTime
     * @param FilterProvider $filterProvider
     * @param TimeSlotSeats $timeSlotSeats
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     * @param RequestInterface $requestInterface
     * @param CollectionFactory $storeInfoCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EventRepositoryInterface $eventRepository
     * @param BlockRepositoryInterface $blockRepository
     * @param CounterRepositoryInterface $counterRepository
     * @param StoreInfoRepositoryInterface $storeInfoRepository
     */
    public function __construct(
        Http $request,
        DateTime $dateTime,
        FilterProvider $filterProvider,
        TimeSlotSeats $timeSlotSeats,
        LoggerInterface $logger,
        TimezoneInterface $timezone,
        RequestInterface $requestInterface,
        CollectionFactory $storeInfoCollectionFactory,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EventRepositoryInterface $eventRepository,
        BlockRepositoryInterface $blockRepository,
        CounterRepositoryInterface $counterRepository,
        StoreInfoRepositoryInterface $storeInfoRepository
    ) {
        $this->logger                       = $logger;
        $this->request                      = $request;
        $this->dateTime                     = $dateTime;
        $this->timezone                     = $timezone;
        $this->storeManager                 = $storeManager;
        $this->timeSlotSeats                = $timeSlotSeats;
        $this->filterProvider               = $filterProvider;
        $this->eventRepository              = $eventRepository;
        $this->blockRepository              = $blockRepository;
        $this->requestInterface             = $requestInterface;
        $this->counterRepository            = $counterRepository;
        $this->storeInfoRepository          = $storeInfoRepository;
        $this->searchCriteriaBuilder        = $searchCriteriaBuilder;
        $this->storeInfoCollectionFactory   = $storeInfoCollectionFactory;
    }

    /**
     * Get Event ID
     *
     * @return mixed|null
     */
    public function getEventId()
    {
        return $this->request->getParam('id');
    }

    /**
     * Get Event
     *
     * @return Event
     */
    public function getEvent() : Event
    {
        /** @var Event $event */
        $event = $this->eventRepository->getById($this->getEventId());
        return $event;
    }

    /**
     * Get Cms Block Identifier
     *
     * @param $id
     * @return string
     */
    public function getCmsBlockIdentifier($id) : string
    {
        $identifier = '';
        try {
            $block = $this->blockRepository->getById($id);
            $identifier = $block->getIdentifier();
        } catch (\Exception $exception) {
            $this->logger->info('Block identifier error:' . $exception->getMessage());
        }
        return $identifier;
    }

    /**
     * Get Event Image Url
     *
     * @param $file
     * @return string
     */
    public function getImageUrl($file) : string
    {
        $url = '';
        if ($file == '') {
            return $url;
        }
        try {
            $file = ltrim(str_replace('\\', '/', $file), '/');
            $url = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $file;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $url;
    }

    /**
     * This will get the page builder content/description and make it renderable at frontend.
     *
     * @param $content
     * @return mixed
     */
    public function contentFiltering($content)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($content);
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId() : int
    {
        $storeId = 0;
        try {
            $storeId = $this->_storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $storeId;
    }

    /**
     * Get counters against event
     *
     * @return array|ExtensibleDataInterface[]
     */
    public function getCounters()
    {
        $counters = [];
        $search = $this->searchCriteriaBuilder
            ->addFilter('main_table.status', 1, 'eq')
            ->addFilter('main_table.event_id', $this->getEventId(), 'eq')
            ->create();
        try {
            $counters = $this->counterRepository->getList($search)->getItems();
        } catch (\Exception $e) {
            $this->logger->info('Error while fetching counters:' . $e->getMessage());
        }
        return $counters;
    }

    /**
     * Get Counter Name
     *
     * @param $id
     * @return mixed
     */
    private function getCounterName($id)
    {
        $counterDetail = $this->storeInfoRepository->getById($id);
        return $counterDetail->getTitle();
    }

    /**
     * Method used to get counter dropdown text
     *
     * @param $data
     * @return string
     */
    public function counterDropdownText($data) : string
    {
        $text = '';
        if (isset($data['offline_store_id']) && isset($data['from_date']) && isset($data['to_date'])) {
            $text = $this->getCounterName($data['offline_store_id']) . ' (' .
                $this->dateTime->gmtDate(self::COUNTER_DATE_FORMAT, $data['from_date']) . '-' .
                $this->dateTime->gmtDate(self::COUNTER_DATE_FORMAT, $data['to_date']) . ')';
        }
        return $text;
    }

    /**
     * Get counter dates
     *
     * @return array
     */
    public function getCounterDates() : array
    {
        $dates = [];
        $counterId = $this->requestInterface->getParam('counter_id');

        if ($counterId) {
            $dates = $this->timeSlotSeats->availableDates($counterId);
        }

        return $dates;
    }

    /**
     * Get counter time slots
     *
     * @return array
     */
    public function getCounterTimeSlots() : array
    {
        $timeSlots  = [];
        $date       = $this->requestInterface->getParam('date');
        $counterId  = $this->requestInterface->getParam('counter_id');

        if ($counterId && $date) {
            $timeSlots = $this->timeSlotSeats->timeSlotsWithSeats($counterId, $date);
        }

        return $timeSlots;
    }

    /**
     * Get form action URL for POST Counter request
     *
     * @return string
     */
    public function getFormAction() : string
    {
        return $this->storeManager->getStore()->getUrl('event/reservation/index/');
    }

    /**
     * To check counter is expired or not
     *
     * @param $counter
     * @return bool
     */
    public function isCounterExpired($counter) : bool
    {
        return $this->timeSlotSeats->isCounterExpired($counter);
    }

    /**
     * To check event is expired or not
     *
     * @param $counters
     * @return bool
     */
    public function isEventExpired($counters) : bool
    {
        return $this->timeSlotSeats->isEventExpired($counters);
    }

    /**
     * To check if the current counter exists in this store
     *
     * @param $counterId
     * @return bool
     */
    public function checkStoreLocatorInWebsite($counterId)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $storeCollection = $this->storeInfoCollectionFactory->create();
            $storeCollection->addFieldToFilter(
                "entity_id",
                ["eq" => $counterId]
            );
            $storeCount = $storeCollection->addStoreFilter($storeId)->count();
            if ($storeCount > 0) {
                return true;
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return false;
    }
}
