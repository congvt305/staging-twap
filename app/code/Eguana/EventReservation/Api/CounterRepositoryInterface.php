<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 21/10/20
 * Time: 7:30 PM
 */
namespace Eguana\EventReservation\Api;

use Eguana\EventReservation\Api\Data\CounterInterface;
use Eguana\EventReservation\Api\Data\CounterSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared CRUD
 *
 * Interface CounterRepositoryInterface
 */
interface CounterRepositoryInterface
{
    /**
     * Save counter
     *
     * @param CounterInterface $counter
     * @return CounterInterface
     */
    public function save(CounterInterface $counter);

    /**
     * Retrieve counter
     *
     * @param int $counterId
     * @return CounterInterface
     */
    public function getById($counterId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CounterSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete counter
     *
     * @param CounterInterface $counter
     * @return bool true on success
     */
    public function delete(CounterInterface $counter);

    /**
     * Delete counter by ID
     *
     * @param int $counterId
     * @return bool true on success
     */
    public function deleteById($counterId);
}
