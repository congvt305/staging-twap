<?php

namespace CJ\Sms\Api\Data;

/**
 * interface EventManagerInterface
 * @api
 */
interface SmsHistoryInterface
{
    const ENTITY_ID = 'entity_id';

    const TELEPHONE = 'telephone';

    const STORE_ID = 'store_id';

    const LIMIT_NUMBER = 'limit_number';

    const CREATED_AT = 'created_at';


    /**
     * Set entity id
     *
     * @param int $entity_id
     * @return $this
     */
    public function setEntityId($entity_id);

    /**
     * Get entity id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Set store id
     *
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * Set limit number
     *
     * @param string $limitNumber
     * @return $this
     */

    public function setLimitNumber($limitNumber);

    /**
     * Get limit number
     *
     * @return string
     */
    public function getLimitNumber();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * get created at
     *
     * @return string
     */
    public function getCreatedAt();

}
