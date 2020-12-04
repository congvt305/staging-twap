<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 4:00 PM
 */
namespace Eguana\EventReservation\Api;

use Eguana\EventReservation\Api\Data\UserReservationInterface;
use Eguana\EventReservation\Api\Data\UserReservationSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared CRUD
 *
 * Interface UserReservationRepositoryInterface
 */
interface UserReservationRepositoryInterface
{
    /**
     * Save UserReservation
     *
     * @param UserReservationInterface $userReservation
     * @return UserReservationInterface
     */
    public function save(UserReservationInterface $userReservation);

    /**
     * Retrieve UserReservation
     *
     * @param int $userReservationId
     * @return UserReservationInterface
     */
    public function getById($userReservationId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return UserReservationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete UserReservation
     *
     * @param UserReservationInterface $userReservation
     * @return bool true on success
     */
    public function delete(UserReservationInterface $userReservation);

    /**
     * Delete User Reservation by ID
     *
     * @param int $userReservationId
     * @return bool true on success
     */
    public function deleteById($userReservationId);
}
