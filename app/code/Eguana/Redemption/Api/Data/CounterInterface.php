<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/10/20
 * Time: 10:40 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Api\Data;

/**
 * Interface CounterInterface
 * @api
 */
interface CounterInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const REDEMPTION_ID = 'redemption_id';
    const REDEEM_DATE = 'redeem_date';
    const CUSTOMER_NAME = 'customer_name';
    const EMAIL = 'email';
    const TELEPHONE = 'telephone';
    const COUNTER_ID = 'counter_id';
    const LINE_ID = 'line_id';
    const REGISTRATION_DATE = 'registration_date';
    const STATUS = 'status';
    const TOKEN = 'token';
    const UTM_SOURCE = 'utm_source';
    const UTM_MEDIUM = 'utm_medium';
    const UTM_CONTENT = 'utm_content';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME = 'update_time';
    /**#@-*/

    /**
     * Get Entity Id
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return RedemptionInterface
     */
    public function setEntityId($entityId);

    /**
     * Get Redemption Id
     *
     * @return int|null
     */
    public function getRedemptionId();

    /**
     * Set Redemption Id
     *
     * @param int $redemptionId
     * @return RedemptionInterface
     */
    public function setRedemptionId($redemptionId);

    /**
     * Get Redeem Date
     *
     * @return string|null
     */
    public function getRedeemDate() : string;

    /**
     * Set Redeem Date
     *
     * @param string $redeemDate
     * @return RedemptionInterface
     */
    public function setRedeemDate($redeemDate);

    /**
     * Get Customer Name
     *
     * @return string|null
     */
    public function getCustomerName() : string;

    /**
     * Set Customer Name
     *
     * @param string $customerName
     * @return RedemptionInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail() : string;

    /**
     * Set email
     *
     * @param string $email
     * @return RedemptionInterface
     */
    public function setEmail($email);

    /**
     * Get Telephone
     *
     * @return mixed|null
     */
    public function getTelephone();

    /**
     * Set Telephone
     *
     * @param string $telephone
     * @return RedemptionInterface
     */
    public function setTelephone($telephone);

    /**
     * Get Counter Id
     *
     * @return string
     */
    public function getCounterId() : string;

    /**
     * Set Counter Id
     *
     * @param int $counterId
     * @return RedemptionInterface
     */
    public function setCounterId($counterId);

    /**
     * Get Line Id
     *
     * @return string
     */
    public function getLineId();

    /**
     * Set Line Id
     *
     * @param int $lineId
     * @return RedemptionInterface
     */
    public function setLineId($lineId);

    /**
     * Get Registration Date
     *
     * @return string|null
     */
    public function getRegistrationDate() : string;

    /**
     * Set Registration Date
     *
     * @param string $registrationDate
     * @return RedemptionInterface
     */
    public function setRegistrationDate($registrationDate);

    /**
     * Get Is Status
     *
     * @return string
     */
    public function getStatus() : string;

    /**
     * Set Is Status
     *
     * @param int|bool $status
     * @return RedemptionInterface
     */
    public function setStatus($status);

    /**
     * Get Token
     *
     * @return string
     */
    public function getToken();

    /**
     * Set Token
     *
     * @param $token
     * @return RedemptionInterface
     */
    public function setToken($token);

    /**
     * Get UTM source
     *
     * @return string|null
     */
    public function getUtmSource();
    /**
     * Set UTM source
     *
     * @param string $utmSource
     * @return RedemptionInterface
     */
    public function setUtmSource($utmSource);

    /**
     * Get UTM Medium
     *
     * @return string
     */
    public function getUtmMedium();

    /**
     * Set UTM Medium
     *
     * @param string $utmMedium
     * @return RedemptionInterface
     */
    public function setUtmMedium($utmMedium);

    /**
     * Get UTM Content
     *
     * @return string|null
     */
    public function getUtmContent();

    /**
     * Set UTM Content
     *
     * @param string $utmContent
     * @return RedemptionInterface
     */
    public function setUtmContent($utmContent);

    /**
     * Get Creation Time
     *
     * @return string|null
     */
    public function getCreationTime() : string;

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return RedemptionInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Get Update Time
     *
     * @return string|null
     */
    public function getUpdateTime() : string;

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return RedemptionInterface
     */
    public function setUpdateTime($updateTime);
}
