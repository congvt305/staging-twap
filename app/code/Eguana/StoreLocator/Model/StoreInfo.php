<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */
namespace Eguana\StoreLocator\Model;

use Eguana\StoreLocator\Api\Data\StoreInfoInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo as ResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Model
 *
 * Class StoreInfo
 *  Eguana\StoreLocator\Model
 */
class StoreInfo extends AbstractExtensibleModel implements StoreInfoInterface, IdentityInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'stores_info';

    const CACHE_TAG = 'stores_info';

    /**
     * @var string
     */
    protected $_cacheTag = 'stores_info';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Function is not using anywhere in this module but may be used in another module
     * @return array|string[]
     */
    public function getCustomAttributesCodes()
    {
        return [
            'entity_id',
            'title',
            'area',
            'address',
            'telephone',
            'available_for_events',
            'available_for_redemption',
            'location',
            'created_at',
            'email',
            'store_type'
        ];
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * setter
     * @param $entityId
     * @return StoreInfo
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * setter
     * @param $title
     * @return StoreInfo
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * setter
     * @param $area
     * @return StoreInfo
     */
    public function setArea($area)
    {
        return $this->setData(self::AREA, $title);
    }

    /**
     * setter
     * @param $address
     * @return StoreInfo
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * setter
     * @param $telephone
     * @return StoreInfo
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * @param $availableForEvents
     * @return StoreInfo
     */
    public function setAvailableForEvents($availableForEvents)
    {
        return $this->setData(self::AVAILABLE_FOR_EVENTS, $availableForEvents);
    }

    /**
     * @param $availableForRedemption
     * @return StoreInfo
     */
    public function setAvailableForRedemption($availableForRedemption)
    {
        return $this->setData(self::AVAILABLE_FOR_REDEMPTION, $availableForRedemption);
    }

    /**
     * setter
     * @param $location
     * @return StoreInfo
     */
    public function setLocation($location)
    {
        return $this->setData(self::LOCATION, $location);
    }

    /**
     * setter
     * @param $createdAt
     * @return StoreInfo
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * get Store timing for opening
     * @param $type
     * @return StoreInfo
     */
    public function setTiming($timing)
    {
        return $this->setData(self::TIMING, $timing);
    }

    /**
     * get Store Type
     * @param $storeType
     * @return StoreInfo
     */
    public function setStoreType($storeType)
    {
        return $this->setData(self::STORE_TYPE, $timing);
    }

    /**
     * setter
     * @param $email
     * @return StoreInfo
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * setter
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * getter
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * getter
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * getter
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * getter
     */
    public function getAvailableForEvents()
    {
        return $this->getData(self::AVAILABLE_FOR_EVENTS);
    }

    /**
     * getter
     */
    public function getAvailableForRedemption()
    {
        return $this->getData(self::AVAILABLE_FOR_REDEMPTION);
    }

    /**
     * getter
     */
    public function getLocation()
    {
        return $this->getData(self::LOCATION);
    }

    /**
     * getter
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * getter
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * getter
     */
    public function getTiming()
    {
        return $this->getData(self::TIMING);
    }

    /**
     * getter
     */
    public function getArea()
    {
        return $this->getData(self::AREA);
    }

    /**
     * getter
     */
    public function getStoreType()
    {
        return $this->getData(self::STORE_TYPE);
    }

    /**
     * Function save databind
     * @param $storeInfoData
     */
    public function adminSaveDataBind($storeInfoData)
    {
        if (isset($storeInfoData['entity_id'])) {
            $this->load($storeInfoData['entity_id']);
        }
        $this->setData('title', $storeInfoData['title']);
        $this->setData('area', $storeInfoData['area']);
        $this->setData('store_type', $storeInfoData['store_type']);
        $this->setData('address', $storeInfoData['address']);
        $this->setData('telephone', $storeInfoData['telephone']);
        $this->setData('available_for_events', $storeInfoData['available_for_events']);
        $this->setData('available_for_redemption', $storeInfoData['available_for_redemption']);
        $this->setData('location', $storeInfoData['location']);
        $this->setData('stores', $storeInfoData['store_id']);
        $this->setData('email', $storeInfoData['email']);
        $this->setData('timing', $storeInfoData['timing']);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }
}
