<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 11/11/20
 * Time: 5:59 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\Counter;

use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Model\UserReservation;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Calculate time slots only & time slots with seats
 *
 * Class TimeSlotSeats
 */
class TimeSlotSeats
{
    /**#@+
     * Constants for date & time slots formats.
     */
    const MIN_SECONDS       = 60;
    const TIME_FORMAT       = 'H:i';
    const DAY_SECONDS       = 86400;
    const DATE_FORMAT       = 'Y-m-d';
    const COUNTER_DATE      = 'm/d/Y';
    /**#@-*/

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
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CounterRepositoryInterface $counterRepository
     * @param UserReservationRepositoryInterface $userReservationRepository
     */
    public function __construct(
        DateTime $dateTime,
        LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CounterRepositoryInterface $counterRepository,
        UserReservationRepositoryInterface $userReservationRepository
    ) {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->counterRepository = $counterRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->userReservationRepository = $userReservationRepository;
    }

    /**
     * Get available dates against counter id or data
     *
     * @param int $counterId
     * @param array $data
     * @return array|int|void
     */
    public function availableDates($counterId = 0, $data = [])
    {
        $availableDates = [];
        $closeDays = [];
        $fromDate = $toDate = 0;
        if ($counterId || $data) {
            if ($counterId) {
                try {
                    $counter = $this->counterRepository->getById($counterId);
                } catch (\Exception $e) {
                    $this->logger->info('Error while fetching counter:' . $e->getMessage());
                }

                if (isset($counter)) {
                    $closeDays  = explode(',', $counter['close_days']);
                    $fromDate = $this->dateTime->gmtTimestamp($counter['from_date']);
                    $toDate = $this->dateTime->gmtTimestamp($counter['to_date']);
                }
            } else {
                $closeDays  = explode(',', $data['close_days']);
                $fromDate = $this->dateTime->gmtTimestamp($data['from_date']);
                $toDate = $this->dateTime->gmtTimestamp($data['to_date']);
            }

            if ($fromDate && $toDate) {
                for ($currentDate = $fromDate; $currentDate <= $toDate; $currentDate += (self::DAY_SECONDS)) {
                    $currentDay = $this->dateTime->gmtDate('l', $currentDate);
                    if (!in_array($currentDay, $closeDays)) {
                        $date = $this->dateTime->gmtDate(self::COUNTER_DATE, $currentDate);

                        $disabled = 0;
                        $today = $this->dateTime->gmtDate(self::DATE_FORMAT);
                        $today = $this->dateTime->gmtTimestamp($today);
                        if ($today > $currentDate) {
                            $disabled = 1;
                        }
                        $availableDates[] = [
                            'date'      => $date,
                            'disabled'  => $disabled
                        ];
                    }
                }
            }
        }

        if ($data) {
            $size = count($availableDates);
            foreach ($availableDates as $date) {
                if ($date['disabled']) {
                    $size--;
                }
            }
            return ($size < 0) ? 0 : $size;
        }
        return $availableDates;
    }

    /**
     * Get counter time slots, remaining mins after creating slots or no of slots
     *
     * @param int $counterId
     * @param array $data
     * @param bool $remainingMins
     * @return array|int|void
     */
    public function getTimeSlots($counterId = 0, $data = [], $remainingMins = false)
    {
        $timeSlots  = [];
        $start_time = $end_time = $slot_time = $break = 0;
        if ($counterId || $data) {
            if ($counterId) {
                try {
                    $counter = $this->counterRepository->getById($counterId);
                } catch (\Exception $e) {
                    $this->logger->info('Error while fetching counter:' . $e->getMessage());
                }
                if (isset($counter)) {
                    $start_time = $counter['start_time'];
                    $end_time = $counter['end_time'];
                    $slot_time = $counter['slot_time'];
                    $break = $counter['break'];
                }
            } else {
                $start_time = $data['start_time'];
                $end_time = $data['end_time'];
                $slot_time = $data['slot_time'];
                $break = $data['break'];
            }

            $break = $break ? $break : 0;
            if ($start_time && $end_time && $slot_time) {
                $startTime = $this->dateTime->gmtTimestamp($start_time);
                $endTime = $this->dateTime->gmtTimestamp($end_time);
                $duration = (int) $slot_time + (int) $break;
                $addMins = $duration * self::MIN_SECONDS;

                while ($startTime < $endTime) {
                    $slotStart = $this->dateTime->gmtDate(self::TIME_FORMAT, $startTime);
                    $end = $startTime + ((int) $slot_time * self::MIN_SECONDS);
                    if ($end > $endTime) {
                        break;
                    }
                    $slotEnd = $this->dateTime->gmtDate(self::TIME_FORMAT, $end);
                    $timeSlots[] = $slotStart . ' - ' . $slotEnd;
                    $startTime += $addMins;
                }
            }
        }

        if ($data) {
            if ($remainingMins) {
                return (isset($startTime) && ($startTime - ($break * self::MIN_SECONDS)) < $endTime) ? true : false;
            }
            return count($timeSlots);
        }
        return $timeSlots;
    }

    /**
     * Time slots with seats available
     *
     * @param $counterId
     * @param $date
     * @return array
     */
    public function timeSlotsWithSeats($counterId, $date) : array
    {
        $timeSlots  = [];
        try {
            $counter = $this->counterRepository->getById($counterId);
        } catch (\Exception $e) {
            $this->logger->info('Error while fetching counter:' . $e->getMessage());
        }

        if ($counterId && isset($counter) && $date) {
            $date = $this->dateTime->gmtDate(self::DATE_FORMAT, $date);
            $slots = $this->getTimeSlots($counterId);
            foreach ($slots as $slot) {
                $search = $this->searchCriteriaBuilder
                    ->addFilter('main_table.status', UserReservation::STATUS_CANCELED, 'neq')
                    ->addFilter('main_table.counter_id', $counterId, 'eq')
                    ->addFilter('main_table.date', $date, 'eq')
                    ->addFilter('main_table.time_slot', $slot, 'eq')
                    ->create();
                $reserved = $this->userReservationRepository->getList($search)->getTotalCount();
                $total = $counter->getPerTimeSlotSeats();
                $seats = (int) $total - (int) $reserved;
                $seats = ($seats < 0) ? 0 : $seats;
                $timeSlots[] = ['slot' => $slot, 'seats' => $seats];
            }
        }
        return $timeSlots;
    }

    /**
     * Return total slots against a counter
     *
     * @param array $data
     * @return int
     */
    public function totalSlots(array $data) : int
    {
        $totalSlots = 0;
        $fromDate = (isset($data['from_date']) && $data['from_date']) ? $data['from_date'] : '';
        $toDate = (isset($data['to_date']) && $data['to_date']) ? $data['to_date'] : '';
        $closeDays = (isset($data['close_days']) && $data['close_days']) ? $data['close_days'] : '';
        $startTime = (isset($data['start_time']) && $data['start_time']) ? $data['start_time'] : '';
        $endTime = (isset($data['end_time']) && $data['end_time']) ? $data['end_time'] : '';
        $slotTime = (isset($data['slot_time']) && $data['slot_time']) ? $data['slot_time'] : 0;
        $break = (isset($data['break']) && $data['break']) ? $data['break'] : 0;

        if ($fromDate && $toDate && $startTime && $endTime && $slotTime) {
            $datesData = [
                'from_date'     => $fromDate,
                'to_date'       => $toDate,
                'close_days'    => $closeDays
            ];
            $availableDatesCount = $this->availableDates(0, $datesData);

            $slotsData = [
                'start_time'    => $startTime,
                'end_time'      => $endTime,
                'slot_time'     => $slotTime,
                'break'         => $break
            ];
            $availableSlotsCount = $this->getTimeSlots(0, $slotsData);

            $totalSlots = $availableDatesCount * $availableSlotsCount;
        }

        return $totalSlots;
    }

    /**
     * To check event is expired or not
     *
     * @param $counters
     * @return bool
     */
    public function isEventExpired($counters) : bool
    {
        $expire = true;
        $size = count($counters);
        $today = $this->dateTime->gmtDate(self::DATE_FORMAT);
        $current = $this->dateTime->gmtTimestamp($today);

        foreach ($counters as $counter) {
            $toDate = $this->dateTime->gmtTimestamp($counter['to_date']);
            if ($current > $toDate) {
                $size--;
            }
        }
        return $size ? false : true;
    }

    /**
     * To check counter is expired or not
     *
     * @param $counter
     * @return bool
     */
    public function isCounterExpired($counter) : bool
    {
        $expire = true;
        if (isset($counter['to_date'])) {
            $toDate     = $this->dateTime->gmtTimestamp($counter['to_date']);
            $today      = $this->dateTime->gmtDate(self::DATE_FORMAT);
            $current    = $this->dateTime->gmtTimestamp($today);
            $expire = $current > $toDate ? true : false;
        }
        return $expire;
    }
}
