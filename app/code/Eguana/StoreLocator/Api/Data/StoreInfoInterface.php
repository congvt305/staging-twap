<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */
namespace Eguana\StoreLocator\Api\Data;

/**
 * Interface StoreInfoInterface
 *  Eguana\StoreLocator\Api\Data
 */
interface StoreInfoInterface
{
    /*
    * constants
    */
    const ENTITY_ID = 'entity_id';
    const TITLE = 'title';
    const ADDRESS = 'address';
    const TELEPHONE = 'telephone';
    const AVAILABLE_FOR_EVENTS = 'available_for_events';
    const AVAILABLE_FOR_REDEMPTION = 'available_for_redemption';
    const LOCATION = 'location';
    const CREATED_AT = 'created_at';
    const EMAIL = 'email';
    const TIMING = 'timing';
    const STORE_TYPE = 'store_type';
    const AREA = 'area';

    /*
    *  Getters
    */
    public function getEntityId();
    public function getTitle();
    public function getAddress();
    public function getTelephone();
    public function getAvailableForEvents();
    public function getAvailableForRedemption();
    public function getLocation();
    public function getCreatedAt();
    public function getEmail();
    public function getTiming();
    public function getStoreType();
    public function getArea();

    /*
    *  Setters
    */
    public function setEntityId($entityId);
    public function setTitle($title);
    public function setAddress($address);
    public function setTelephone($telephone);
    public function setAvailableForEvents($availableForEvents);
    public function setAvailableForRedemption($availableForRedemption);
    public function setLocation($location);
    public function setCreatedAt($createdAt);
    public function setEmail($email);
    public function setTiming($timing);
    public function setStoreType($storeType);
    public function setArea($area);
}
