<?php

namespace CJ\CustomCustomer\Api;

/**
 * Update customer group of a customer with latest value from POS
 * @api
 */
interface CustomerGroupManagementInterface
{
    /**
     * @param \CJ\CustomCustomer\Api\Data\CustomerDataInterface
     * @return \CJ\CustomCustomer\Api\Data\SyncGradeResponseInterface
     */
    public function setGroup(\CJ\CustomCustomer\Api\Data\CustomerDataInterface $gradeData);
}
