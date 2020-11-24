<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 5:52 PM
 */
namespace Eguana\Pip\Api;

use Eguana\Pip\Api\Data\TerminatedCustomerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface TerminatedCustomerRepositoryInterface
 * @api
 */
interface TerminatedCustomerRepositoryInterface
{
    /**
     * Save Terminated Customer
     *
     * @param $terminatedCustomer
     * @return TerminatedCustomerInterface
     */
    public function save($terminatedCustomer);

    /**
     * Retrieve Terminated Customer
     *
     * @param int $entityId
     * @return TerminatedCustomerInterface
     */
    public function getById($entityId);

    /**
     * Retrieve Terminated Customer matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Terminated Customer
     *
     * @param $terminatedCustomer
     * @return bool true on success
     */
    public function delete($terminatedCustomer);

    /**
     * Delete Terminated Customer by Id
     *
     * @param int $entityId
     * @return bool true on success
     */
    public function deleteById($entityId);
}
