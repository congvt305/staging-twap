<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 21/10/20
 * Time: 5:00 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\CounterInterface;
use Eguana\EventReservation\Model\ResourceModel\Counter as CounterResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * This model class is used for the Curd operation of Counters
 *
 * Class Counter
 */
class Counter extends AbstractModel implements CounterInterface, IdentityInterface
{
    /**#@+
     * Counter cache tag
     */
    const CACHE_TAG = 'event-counter';
    /**#@-*/

    /**#@+
     * Counter's Statuses & close days
     */
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 0;
    const CLOSE_DAYS_LIST   = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    ];
    /**#@-*/

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Constructor to initialize ResourceModel
     */
    public function _construct()
    {
        $this->_init(CounterResource::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities() : array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Prepare counter's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses() : array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::COUNTER_ID);
    }

    /**
     * Get staff email
     *
     * @return string
     */
    public function getStaffEmail()
    {
        return $this->getData(self::STAFF_EMAIL);
    }

    /**
     * Get from date
     *
     * @return string
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * Get to date
     *
     * @return string
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * Get time slot
     *
     * @return int
     */
    public function getSlotTime()
    {
        return $this->getData(self::SLOT_TIME);
    }

    /**
     * Get break
     *
     * @return int
     */
    public function getBreak()
    {
        return $this->getData(self::BREAK);
    }

    /**
     * Get per time slot seats
     *
     * @return int
     */
    public function getPerTimeSlotSeats()
    {
        return $this->getData(self::PER_TIME_SLOT_SEATS);
    }

    /**
     * Get start time
     *
     * @return string
     */
    public function getStartTime()
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * Get end time
     *
     * @return string
     */
    public function getEndTime()
    {
        return $this->getData(self::END_TIME);
    }

    /**
     * Get close days
     *
     * @return string
     */
    public function getCloseDays()
    {
        return $this->getData(self::CLOSE_DAYS);
    }

    /**
     * Get offline store id
     *
     * @return int
     */
    public function getOfflineStoreId()
    {
        return $this->getData(self::OFFLINE_STORE_ID);
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get Total Slots
     *
     * @return int
     */
    public function getTotalSlots()
    {
        return $this->getData(self::TOTAL_SLOTS);
    }

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return CounterInterface|Counter
     */
    public function setId($id)
    {
        return $this->setData(self::COUNTER_ID, $id);
    }

    /**
     * Set staff email
     *
     * @param string $staffEmail
     * @return CounterInterface|Counter
     */
    public function setStaffEmail($staffEmail)
    {
        return $this->setData(self::STAFF_EMAIL, $staffEmail);
    }

    /**
     * Set from date
     *
     * @param string $fromDate
     * @return CounterInterface|Counter
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * Set to date
     *
     * @param string $toDate
     * @return CounterInterface|Counter
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * Set time slot
     *
     * @param int $slotTime
     * @return CounterInterface|Counter
     */
    public function setSlotTime($slotTime)
    {
        return $this->setData(self::SLOT_TIME, $slotTime);
    }

    /**
     * Set break
     *
     * @param int $break
     * @return CounterInterface|Counter
     */
    public function setBreak($break)
    {
        return $this->setData(self::BREAK, $break);
    }

    /**
     * Set per time slot seats
     *
     * @param int $perTimeSlotSeats
     * @return CounterInterface|Counter
     */
    public function setPerTimeSlotSeats($perTimeSlotSeats)
    {
        return $this->setData(self::PER_TIME_SLOT_SEATS, $perTimeSlotSeats);
    }

    /**
     * Set start time
     *
     * @param string $startTime
     * @return CounterInterface|Counter
     */
    public function setStartTime($startTime)
    {
        return $this->setData(self::START_TIME, $startTime);
    }

    /**
     * Set end time
     *
     * @param string $endTime
     * @return CounterInterface|Counter
     */
    public function setEndTime($endTime)
    {
        return $this->setData(self::END_TIME, $endTime);
    }

    /**
     * Set close days
     *
     * @param string $closeDays
     * @return CounterInterface|Counter
     */
    public function setCloseDays($closeDays)
    {
        return $this->setData(self::CLOSE_DAYS, $closeDays);
    }

    /**
     * Set offline store id
     *
     * @param int $offlineStoreId
     * @return CounterInterface|Counter
     */
    public function setOfflineStoreId($offlineStoreId)
    {
        return $this->setData(self::OFFLINE_STORE_ID, $offlineStoreId);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return CounterInterface|Counter
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set total slots
     *
     * @param int $totalSlots
     * @return CounterInterface|Counter
     */
    public function setTotalSlots($totalSlots)
    {
        return $this->setData(self::TOTAL_SLOTS, $totalSlots);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return CounterInterface|Counter
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return CounterInterface|Counter
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }
}
