<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 21/10/20
 * Time: 7:37 PM
 */
namespace Eguana\EventReservation\Api\Data;

/**
 * Interface class having getter\setter
 *
 * Interface CounterReservation
 */
interface CounterInterface
{
    /**#@+
     * Constants for keys of data array.
     */
    const COUNTER_ID                = 'reservation_counter_id';
    const STAFF_EMAIL               = 'staff_email';
    const FROM_DATE                 = 'from_date';
    const TO_DATE                   = 'to_date';
    const SLOT_TIME                 = 'slot_time';
    const BREAK                     = 'break';
    const PER_TIME_SLOT_SEATS       = 'per_time_slot_seats';
    const START_TIME                = 'start_time';
    const END_TIME                  = 'end_time';
    const CLOSE_DAYS                = 'close_days';
    const STATUS                    = 'status';
    const OFFLINE_STORE_ID          = 'offline_store_id';
    const TOTAL_SLOTS               = 'total_slots';
    const CREATION_TIME             = 'creation_time';
    const UPDATE_TIME               = 'update_time';
    /**#@-*/

    /**
     * Get counter ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get staff email
     *
     * @return string|null
     */
    public function getStaffEmail();

    /**
     * Get from date
     *
     * @return string|null
     */
    public function getFromDate();

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate();

    /**
     * Get slot time
     *
     * @return int|null
     */
    public function getSlotTime();

    /**
     * Get break
     *
     * @return int|null
     */
    public function getBreak();

    /**
     * Get per time slot seats
     *
     * @return int|null
     */
    public function getPerTimeSlotSeats();

    /**
     * Get start time
     *
     * @return string|null
     */
    public function getStartTime();

    /**
     * Get end time
     *
     * @return string|null
     */
    public function getEndTime();

    /**
     * Get close days
     *
     * @return string|null
     */
    public function getCloseDays();

    /**
     * Get offline store id
     *
     * @return int|null
     */
    public function getOfflineStoreId();

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Total Slots
     *
     * @return int|null
     */
    public function getTotalSlots();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set counter ID
     *
     * @param int $id
     * @return CounterInterface
     */
    public function setId($id);

    /**
     * Set staff email
     *
     * @param string $title
     * @return CounterInterface
     */
    public function setStaffEmail($title);

    /**
     * Set from date
     *
     * @param string $fromDate
     * @return CounterInterface
     */
    public function setFromDate($fromDate);

    /**
     * Set to date
     *
     * @param string $toDate
     * @return CounterInterface
     */
    public function setToDate($toDate);

    /**
     * Set slot time
     *
     * @param int $slotTime
     * @return CounterInterface
     */
    public function setSlotTime($slotTime);

    /**
     * Set break
     *
     * @param int $break
     * @return CounterInterface
     */
    public function setBreak($break);

    /**
     * Set per time slot seats
     *
     * @param int $perTimeSlotSeats
     * @return CounterInterface
     */
    public function setPerTimeSlotSeats($perTimeSlotSeats);

    /**
     * Set start time
     *
     * @param string $startTime
     * @return CounterInterface
     */
    public function setStartTime($startTime);

    /**
     * Set end time
     *
     * @param string $endTime
     * @return CounterInterface
     */
    public function setEndTime($endTime);

    /**
     * Set close days
     *
     * @param string $closeDays
     * @return CounterInterface
     */
    public function setCloseDays($closeDays);

    /**
     * Set offline store id
     *
     * @param int|bool $offlineStoreId
     * @return CounterInterface
     */
    public function setOfflineStoreId($offlineStoreId);

    /**
     * Set status
     *
     * @param int $status
     * @return CounterInterface
     */
    public function setStatus($status);

    /**
     * Set Total Slots
     *
     * @param int $totalSlots
     * @return CounterInterface
     */
    public function setTotalSlots($totalSlots);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return CounterInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return CounterInterface
     */
    public function setUpdateTime($updateTime);
}
