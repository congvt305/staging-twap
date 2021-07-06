<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
namespace Eguana\RedInvoice\Model;

use Eguana\RedInvoice\Api\Data\RedInvoiceInterface;
use Eguana\RedInvoice\Model\ResourceModel\RedInvoice as RedInvoiceResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * RedInvoice Model to Set RedInvoiceInterface Getter Setter values and initialize Resource model in model
 *
 * Class RedInvoice
 */
class RedInvoice extends AbstractModel implements RedInvoiceInterface, IdentityInterface
{
    /**
     * RedInvoice cache tag
     */
    const CACHE_TAG = 'eguana_red_invoice_data';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Constructor to initialize ResourceModel
     */
    public function _construct()
    {
        $this->_init(RedInvoiceResourceModel::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId(), self::CACHE_TAG . '_' . $this->getEntityId()];
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
     * @return RedInvoice
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Order Id
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set Order Id
     *
     * @param int $orderId
     * @return RedInvoice
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get Is Apply
     *
     * @return string|null
     */
    public function getIsApply()
    {
        return $this->getData(self::IS_APPLY);
    }

    /**
     * Set Is Apply
     *
     * @param string $isApply
     * @return RedInvoice
     */
    public function setIsApply($isApply)
    {
        return $this->setData(self::IS_APPLY, $isApply);
    }

    /**
     * Get Company Name
     *
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->getData(self::COMPANY_NAME);
    }

    /**
     * Set Company Name
     *
     * @param string $companyName
     * @return RedInvoice
     */
    public function setCompanyName($companyName)
    {
        return $this->setData(self::COMPANY_NAME, $companyName);
    }

    /**
     * Get Tax Code
     *
     * @return string|null
     */
    public function getTaxCode()
    {
        return $this->getData(self::TAX_CODE);
    }

    /**
     * Set Tax Code
     *
     * @param string $taxCode
     * @return RedInvoice
     */
    public function setTaxCode($taxCode)
    {
        return $this->setData(self::TAX_CODE, $taxCode);
    }

    /**
     * Get State
     *
     * @return string|null
     */
    public function getState()
    {
        return $this->getData(self::STATE);
    }

    /**
     * Set State
     *
     * @param string $state
     * @return RedInvoice
     */
    public function setState($state)
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * Get City
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * Set City
     *
     * @param string $city
     * @return RedInvoice
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Get Road Name
     *
     * @return string|null
     */
    public function getRoadName()
    {
        return $this->getData(self::ROAD_NAME);
    }

    /**
     * Set Road Name
     *
     * @param string $roadName
     * @return RedInvoice
     */
    public function setRoadName($roadName)
    {
        return $this->setData(self::ROAD_NAME, $roadName);
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
     * @return RedInvoice
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
     * @return RedInvoice
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }
}
