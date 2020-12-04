<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 11/11/20
 * Time: 9:33 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Event\Listing\Column;

use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Model\Counter\TimeSlotSeats;
use Eguana\EventReservation\Model\ResourceModel\Counter\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Psr\Log\LoggerInterface;

/**
 * To Retrive AvailableSeats
 *
 * Class AvailableSeats
 */
class AvailableSeats extends Column
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $counterCollectionFactory;

    /**
     * @var TimeSlotSeats
     */
    private $timeSlotSeats;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LoggerInterface $logger
     * @param TimeSlotSeats $timeSlotSeats
     * @param CollectionFactory $counterCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CounterRepositoryInterface $counterRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimeSlotSeats $timeSlotSeats,
        LoggerInterface $logger,
        CollectionFactory $counterCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CounterRepositoryInterface $counterRepository,
        UserReservationRepositoryInterface $userReservationRepository,
        array $components = [],
        array $data = []
    ) {
        $this->logger = $logger;
        $this->timeSlotSeats = $timeSlotSeats;
        $this->counterRepository = $counterRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->counterCollectionFactory = $counterCollectionFactory;
        $this->userReservationRepository = $userReservationRepository;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $eventId = isset($item['event_id']) ? $item['event_id'] : 0;

                $collection = $this->counterCollectionFactory->create();
                $collection->addFieldToSelect([
                    'reservation_counter_id',
                    'from_date',
                    'to_date',
                    'slot_time',
                    'break',
                    'per_time_slot_seats',
                    'start_time',
                    'end_time',
                    'close_days',
                    'offline_store_id',
                    'total_slots'
                ])
                    ->addFieldToFilter('main_table.event_id', ['eq' => $eventId]);
                $counters = $collection->getItems();
                $totalSlots = $this->calculateSlots($counters);

                $item['available_slots'] = $totalSlots;
            }
        }
        return $dataSource;
    }

    /**
     * Calculate available slots against event
     *
     * @param $counters
     * @return array|bool|int|void
     */
    private function calculateSlots($counters)
    {
        $totalSlots = 0;
        foreach ($counters as $counter) {
            $isCounterExpired = $this->timeSlotSeats->isCounterExpired($counter);
            if (!$isCounterExpired) {
                $total = $counter->getTotalSlots();
                $totalSlots += $total;
            }
        }

        return $totalSlots;
    }
}
