<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 6:20 PM
 */
namespace Eguana\Pip\Model;

use Eguana\Pip\Api\Data\TerminatedCustomerInterface;
use Eguana\Pip\Model\ResourceModel\TerminatedCustomer as TerminatedCustomerResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * TerminatedCustomer Model to Set TerminatedCustomerInterface
 * Getter Setter values and initialize Resource model in model
 *
 * Class TerminatedCustomer
 */
class TerminatedCustomer extends AbstractModel implements TerminatedCustomerInterface, IdentityInterface
{
    /**
     * TerminatedCustomer cache tag
     */
    const CACHE_TAG = 'eguana_pip_terminated_customer';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Constructor to initialize ResourceModel
     */
    public function _construct()
    {
        $this->_init(TerminatedCustomerResourceModel::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get Entity Id
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return TerminatedCustomer
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return TerminatedCustomer
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Integration Number
     *
     * @return int|null
     */
    public function getIntegrationNumber()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Integration Number
     *
     * @param int $integrationNumber
     * @return TerminatedCustomer
     */
    public function setIntegrationNumber($integrationNumber)
    {
        return $this->setData(self::INTEGRATION_NUMBER, $integrationNumber);
    }

    /**
     * Get IP Address
     *
     * @return int|null
     */
    public function getIpAddress()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set IP Address
     *
     * @param int $ipAddress
     * @return TerminatedCustomer
     */
    public function setIpAddress($ipAddress)
    {
        return $this->setData(self::IP_ADDRESS, $ipAddress);
    }

    /**
     * Get Creation Time
     *
     * @return string|null
     */
    public function getCreationTime() : string
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return TerminatedCustomer
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Get Update Time
     *
     * @return string|null
     */
    public function getUpdateTime() : string
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return TerminatedCustomer
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }
}
