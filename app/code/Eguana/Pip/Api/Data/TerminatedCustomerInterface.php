<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 5:55 PM
 */
namespace Eguana\Pip\Api\Data;

/**
 * Interface TerminatedCustomerInterface
 * @api
 */
interface TerminatedCustomerInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const INTEGRATION_NUMBER = 'integration_number';
    const IP_ADDRESS = 'ip_address';
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
     * @return mixed
     */
    public function setEntityId($entityId);

    /**
     * Get Customer Id
     *
     * @return mixed
     */
    public function getCustomerId();

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * Get Integration Number
     *
     * @return mixed
     */
    public function getIntegrationNumber();

    /**
     * Set Integration Number
     *
     * @param int $integrationNumber
     * @return mixed
     */
    public function setIntegrationNumber($integrationNumber);

    /**
     * Get IP Address
     *
     * @return mixed
     */
    public function getIpAddress();

    /**
     * Set IP Address
     *
     * @param int $ipAddress
     * @return mixed
     */
    public function setIpAddress($ipAddress);

    /**
     * Get Creation Time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return mixed
     */
    public function setCreationTime($creationTime);

    /**
     * Get Update Time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return mixed
     */
    public function setUpdateTime($updateTime);
}
