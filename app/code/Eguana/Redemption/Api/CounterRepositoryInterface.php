<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/10/20
 * Time: 11:40 PM
 */
namespace Eguana\Redemption\Api;

use Eguana\Redemption\Api\Data\CounterInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CounterRepositoryInterface
 * @api
 */
interface CounterRepositoryInterface
{
    /**
     * Save Counter
     *
     * @param $counter
     * @return CounterInterface
     */
    public function save($counter);

    /**
     * Retrieve Counter
     *
     * @param int $counterId
     * @return CounterInterface
     */
    public function getById($counterId);

    /**
     * Retrieve Counter matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Counter
     *
     * @param $counter
     * @return bool true on success
     */
    public function delete($counter);

    /**
     * Delete Counter by Id
     *
     * @param int $counterId
     * @return bool true on success
     */
    public function deleteById($counterId);
}
