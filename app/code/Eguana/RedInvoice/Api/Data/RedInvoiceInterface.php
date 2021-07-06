<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
declare(strict_types=1);

namespace Eguana\RedInvoice\Api\Data;

/**
 * Interface RedInvoiceInterface
 * @api
 */
interface RedInvoiceInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'id';
    const ORDER_ID = 'order_id';
    const IS_APPLY = 'is_apply';
    const COMPANY_NAME = 'company_name';
    const TAX_CODE = 'tax_code';
    const STATE = 'state';
    const CITY = 'city';
    const ROAD_NAME = 'road_name';
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
     * @return RedInvoiceInterface
     */
    public function setEntityId($entityId);

    /**
     * Get Order Id
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set Order Id
     *
     * @param int $orderId
     * @return RedInvoiceInterface
     */
    public function setOrderId($orderId);

    /**
     * Get Is Apply
     *
     * @return string|null
     */
    public function getIsApply();

    /**
     * Set Is Apply
     *
     * @param int $isApply
     * @return RedInvoiceInterface
     */
    public function setIsApply($isApply);

    /**
     * Get Company Name
     *
     * @return int|null
     */
    public function getCompanyName();

    /**
     * Set Company Name
     *
     * @param int $companyName
     * @return RedInvoiceInterface
     */
    public function setCompanyName($companyName);

    /**

     * Get Tax Code
     *
     * @return string|null
     */
    public function getTaxCode();

    /**
     * Set Tax Code
     *
     * @param string $taxCode
     * @return RedInvoiceInterface
     */
    public function setTaxCode($taxCode);

    /**
     * Get state
     *
     * @return string|null
     */
    public function getState();

    /**
     * Set state
     *
     * @param string $state
     * @return RedInvoiceInterface
     */
    public function setState($state);

    /**
     * Get City
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set City
     *
     * @param string $city
     * @return RedInvoiceInterface
     */
    public function setCity($city);

    /**
     * Get Road Name
     *
     * @return mixed|null
     */
    public function getRoadName();

    /**
     * Set Road Name
     *
     * @param string $roadName
     * @return RedInvoiceInterface
     */
    public function setRoadName($roadName);

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
     * @return RedInvoiceInterface
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
     * @return RedInvoiceInterface
     */
    public function setUpdateTime($updateTime);
}
