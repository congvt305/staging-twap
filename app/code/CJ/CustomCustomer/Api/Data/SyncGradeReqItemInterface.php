<?php

namespace CJ\CustomCustomer\Api\Data;

/**
 * Interface SyncGradeReqItemInterface
 */
interface SyncGradeReqItemInterface
{
    const GRADE_DATA = 'grade_data';

    /**
     * @return \CJ\CustomCustomer\Api\Data\CustomerDataInterface
     */
    public function getGradeData(): \CJ\CustomCustomer\Api\Data\CustomerDataInterface;

    /**
     * @param \CJ\CustomCustomer\Api\Data\CustomerDataInterface $customerData
     * @return $this
     */
    public function setGradeData(\CJ\CustomCustomer\Api\Data\CustomerDataInterface $customerData);
}
