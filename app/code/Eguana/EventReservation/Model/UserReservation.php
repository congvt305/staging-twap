<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 05:10 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\UserReservationInterface;
use Eguana\EventReservation\Model\ResourceModel\UserReservation as UserReservationResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * This model class is used for the Curd operation of User Reservations
 *
 * Class UserReservation
 */
class UserReservation extends AbstractModel implements UserReservationInterface, IdentityInterface
{
    /**#@+
     * UserReservation cache tag
     */
    const CACHE_TAG = 'event-user-reservation';
    /**#@-*/

    /**#@+
     * UserReservation's Statuses
     */
    const STATUS_PENDING        = 0;
    const STATUS_APPROVED       = 1;
    const STATUS_CANCELED       = 2;
    const AGREEMENT_DISAGREE    = 0;
    const AGREEMENT_AGREE       = 1;
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
        $this->_init(UserReservationResource::class);
    }

    /**
     * Prepare UserReservation's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses() : array
    {
        return [
            self::STATUS_PENDING    => __('Pending'),
            self::STATUS_APPROVED   => __('Approved'),
            self::STATUS_CANCELED   => __('Canceled')
        ];
    }

    /**
     * Prepare UserReservation Agreement options
     *
     * @return array
     */
    public function getAgreementOptions()
    {
        return [
            self::AGREEMENT_AGREE       => __('Agree'),
            self::AGREEMENT_DISAGREE    => __('Disagree')
        ];
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
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::USER_RESERVE_ID);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Is time slot
     *
     * @return string
     */
    public function getTimeSlot()
    {
        return $this->getData(self::TIME_SLOT);
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
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
     * Get meta title
     *
     * @return bool
     */
    public function getAgreement()
    {
        return (bool)$this->getData(self::AGREEMENT);
    }

    /**
     * Get event id
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * Get counter id
     *
     * @return int
     */
    public function getCounterId()
    {
        return $this->getData(self::COUNTER_ID);
    }

    /**
     * Get auth token
     *
     * @return string
     */
    public function getAuthToken()
    {
        return $this->getData(self::AUTH_TOKEN);
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
     * Get customer email action
     *
     * @return int
     */
    public function getEmailAction()
    {
        return $this->getData(self::EMAIL_ACTION);
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
     * @return UserReservationInterface|UserReservation
     */
    public function setId($id)
    {
        return $this->setData(self::USER_RESERVE_ID, $id);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return UserReservationInterface|UserReservation
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return UserReservationInterface|UserReservation
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set time slot
     *
     * @param string $timeSlot
     * @return UserReservationInterface|UserReservation
     */
    public function setTimeSlot($timeSlot)
    {
        return $this->setData(self::TIME_SLOT, $timeSlot);
    }

    /**
     * Set date
     *
     * @param string $date
     * @return UserReservationInterface|UserReservation
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return UserReservationInterface|UserReservation
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return UserReservationInterface|UserReservation
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set agreement
     *
     * @param int|bool $agreement
     * @return UserReservationInterface|UserReservation
     */
    public function setAgreement($agreement)
    {
        return $this->setData(self::AGREEMENT, $agreement);
    }

    /**
     * Set event id
     *
     * @param int $eventId
     * @return UserReservationInterface|UserReservation
     */
    public function setEventId($eventId)
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * Set counter id
     *
     * @param int $counterId
     * @return UserReservationInterface|UserReservation
     */
    public function setCounterId($counterId)
    {
        return $this->setData(self::COUNTER_ID, $counterId);
    }

    /**
     * Set offline store id
     *
     * @param int $offlineStoreId
     * @return UserReservationInterface|UserReservation
     */
    public function setOfflineStoreId($offlineStoreId)
    {
        return $this->setData(self::OFFLINE_STORE_ID, $offlineStoreId);
    }

    /**
     * Set auth token
     *
     * @param string $authToken
     * @return UserReservationInterface|UserReservation
     */
    public function setAuthToken($authToken)
    {
        return $this->setData(self::AUTH_TOKEN, $authToken);
    }

    /**
     * Set customer email action
     *
     * @param int $emailAction
     * @return UserReservationInterface|UserReservation
     */
    public function setEmailAction($emailAction)
    {
        return $this->setData(self::EMAIL_ACTION, $emailAction);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return UserReservationInterface|UserReservation
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return UserReservationInterface|UserReservation
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }
}
