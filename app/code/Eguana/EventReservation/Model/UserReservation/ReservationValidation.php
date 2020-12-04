<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 10/11/20
 * Time: 8:53 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\UserReservation;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Model\UserReservation;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory as StoreCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

/**
 * Validation checkes before reserving an event
 *
 * Class ReservationValidation
 */
class ReservationValidation
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @var StoreCollectionFactory
     */
    private $storeInfoCollectionFactory;

    /**
     * @param LoggerInterface $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreCollectionFactory $storeInfoCollectionFactory
     * @param EventRepositoryInterface $eventRepository
     * @param CounterRepositoryInterface $counterRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     */
    public function __construct(
        LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreCollectionFactory $storeInfoCollectionFactory,
        EventRepositoryInterface $eventRepository,
        CounterRepositoryInterface $counterRepository,
        UserReservationRepositoryInterface $userReservationRepository
    ) {
        $this->logger = $logger;
        $this->eventRepository = $eventRepository;
        $this->counterRepository = $counterRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->userReservationRepository = $userReservationRepository;
        $this->storeInfoCollectionFactory = $storeInfoCollectionFactory;
    }

    /**
     * Check that user can reserve an event
     *
     * @param $data
     * @return bool
     */
    public function canReserveEvent($data) : bool
    {
        $count = 1;
        $counterId = isset($data['counter_id']) ? $data['counter_id'] : 0;
        $date = isset($data['date']) ? $data['date'] : '';
        $timeSlot = isset($data['time_slot']) ? $data['time_slot'] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $storeId = isset($data['store_id']) ? $data['store_id'] : '';

        if ($counterId && $date && $timeSlot && $email && $storeId) {
            $search = $this->searchCriteriaBuilder
                ->addFilter('main_table.status', UserReservation::STATUS_CANCELED, 'neq')
                ->addFilter('main_table.counter_id', $counterId, 'eq')
                ->addFilter('main_table.date', $date, 'eq')
                ->addFilter('main_table.time_slot', $timeSlot, 'eq')
                ->addFilter('main_table.email', $email, 'eq')
                ->addFilter('main_table.store_id', $storeId, 'eq')
                ->create();
            $count = $this->userReservationRepository->getList($search)->getTotalCount();
        }
        return $count ? false : true;
    }

    /**
     * Check that user can reserve an event on basis of seats available
     *
     * @param $data
     * @param string $callFor
     * @return bool
     */
    public function seatsAvailable($data, $callFor = '') : bool
    {
        $seats = 0;
        $reserved = 0;
        $counterId = isset($data['counter_id']) ? $data['counter_id'] : 0;
        $date = isset($data['date']) ? $data['date'] : '';
        $timeSlot = isset($data['time_slot']) ? $data['time_slot'] : '';

        if ($counterId && $date && $timeSlot) {
            try {
                $counter = $this->counterRepository->getById($counterId);
            } catch (\Exception $e) {
                $this->logger->error('Error while fetching counter:' . $e->getMessage());
            }

            if (isset($counter) && !empty($counter)) {
                $seats = $counter->getPerTimeSlotSeats();

                if ($callFor == 'confirm') {
                    $search = $this->searchCriteriaBuilder
                        ->addFilter('main_table.status', UserReservation::STATUS_APPROVED, 'eq')
                        ->addFilter('main_table.counter_id', $counterId, 'eq')
                        ->addFilter('main_table.date', $date, 'eq')
                        ->addFilter('main_table.time_slot', $timeSlot, 'eq')
                        ->create();
                } else {
                    $search = $this->searchCriteriaBuilder
                        ->addFilter('main_table.status', UserReservation::STATUS_CANCELED, 'neq')
                        ->addFilter('main_table.counter_id', $counterId, 'eq')
                        ->addFilter('main_table.date', $date, 'eq')
                        ->addFilter('main_table.time_slot', $timeSlot, 'eq')
                        ->create();
                }
                $reserved = $this->userReservationRepository->getList($search)->getTotalCount();
            }
        }

        return ($seats && ($reserved < $seats)) ? true : false;
    }

    /**
     * Get available store locators against event id
     *
     * @param $eventId
     * @return array
     */
    public function availableCountersForEvent($eventId)
    {
        $counterIds = [];
        try {
            $event = $this->eventRepository->getById($eventId);
        } catch (\Exception $e) {
            $this->logger->error('Error while fetching event:' . $e->getMessage());
        }

        if (isset($event)) {
            $storeIds = $event->getData('store_id');

            $storeCollection = $this->storeInfoCollectionFactory->create();
            $storeCollection->addFieldToFilter(
                "available_for_events",
                ["eq" => 1]
            );
            $storeCollection->addStoreFilter($storeIds);
            $stores = $storeCollection->getItems();

            foreach ($stores as $store) {
                $counterIds[] = $store->getId();
            }
        }

        return $counterIds;
    }
}
