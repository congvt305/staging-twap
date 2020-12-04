<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 04:10 PM
 */
namespace Eguana\EventReservation\Api\Data;

/**
 * Interface class having getter\setter
 *
 * UserReservation Interface
 */
interface UserReservationInterface
{
    /**#@+
     * Constants for keys of data array.
     */
    const USER_RESERVE_ID   = 'user_reserve_id';
    const NAME              = 'name';
    const EMAIL             = 'email';
    const TIME_SLOT         = 'time_slot';
    const DATE              = 'date';
    const PHONE             = 'phone';
    const STATUS            = 'status';
    const AGREEMENT         = 'agreement';
    const EVENT_ID          = 'event_id';
    const COUNTER_ID        = 'counter_id';
    const OFFLINE_STORE_ID  = 'offline_store_id';
    const AUTH_TOKEN        = 'auth_token';
    const EMAIL_ACTION      = 'email_action';
    const CREATION_TIME     = 'creation_time';
    const UPDATE_TIME       = 'update_time';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Get time slot
     *
     * @return string|null
     */
    public function getTimeSlot();

    /**
     * Get date
     *
     * @return string|null
     */
    public function getDate();

    /**
     * Get phone
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get agreement
     *
     * @return bool|null
     */
    public function getAgreement();

    /**
     * Get event id
     *
     * @return int|null
     */
    public function getEventId();

    /**
     * Get counter id
     *
     * @return int|null
     */
    public function getCounterId();

    /**
     * Get offline store id
     *
     * @return int|null
     */
    public function getOfflineStoreId();

    /**
     * Get auth token
     *
     * @return string|null
     */
    public function getAuthToken();

    /**
     * Get customer email action
     *
     * @return int|null
     */
    public function getEmailAction();

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
     * Set ID
     *
     * @param int $id
     * @return UserReservationInterface
     */
    public function setId($id);

    /**
     * Set name
     *
     * @param string $name
     * @return UserReservationInterface
     */
    public function setName($name);

    /**
     * Set email
     *
     * @param string $email
     * @return UserReservationInterface
     */
    public function setEmail($email);

    /**
     * Set time slot
     *
     * @param string $timeSlot
     * @return UserReservationInterface
     */
    public function setTimeSlot($timeSlot);

    /**
     * Set date
     *
     * @param string $date
     * @return UserReservationInterface
     */
    public function setDate($date);

    /**
     * Set phone
     *
     * @param int $phone
     * @return UserReservationInterface
     */
    public function setPhone($phone);

    /**
     * Set status
     *
     * @param int $status
     * @return UserReservationInterface
     */
    public function setStatus($status);

    /**
     * Set agreement
     *
     * @param int|bool $agreement
     * @return UserReservationInterface
     */
    public function setAgreement($agreement);

    /**
     * Set event id
     *
     * @param int|null $eventId
     * @return UserReservationInterface
     */
    public function setEventId($eventId);

    /**
     * Set counter id
     *
     * @param int|null $counterId
     * @return UserReservationInterface
     */
    public function setCounterId($counterId);

    /**
     * Set offline store id
     *
     * @param int|null $offlineStoreId
     * @return UserReservationInterface
     */
    public function setOfflineStoreId($offlineStoreId);

    /**
     * Set auth token
     *
     * @param string $authToken
     * @return UserReservationInterface
     */
    public function setAuthToken($authToken);

    /**
     * Set customer email action
     *
     * @param int|null $emailAction
     * @return UserReservationInterface
     */
    public function setEmailAction($emailAction);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return UserReservationInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return UserReservationInterface
     */
    public function setUpdateTime($updateTime);
}
