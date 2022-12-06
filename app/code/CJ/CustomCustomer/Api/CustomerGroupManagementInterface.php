<?php

namespace CJ\CustomCustomer\Api;

/**
 * Update customer group of a customer with latest value from POS
 * @api
 */
interface CustomerGroupManagementInterface
{
    /**
     * @param Data\CustomerDataInterface $gradeData
     * @return \CJ\CustomCustomer\Api\Data\UpdateCustomerGroupResponseInterface
     */
    public function setGroup(\CJ\CustomCustomer\Api\Data\CustomerDataInterface $gradeData);
}
