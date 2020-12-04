<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/10/20
 * Time: 12:30 PM
 */
namespace Eguana\EventReservation\Api;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Api\Data\EventSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared CRUD
 *
 * Interface EventRepositoryInterface
 */
interface EventRepositoryInterface
{
    /**
     * Save event
     *
     * @param EventInterface $event
     * @return EventInterface
     */
    public function save(EventInterface $event);

    /**
     * Retrieve event
     *
     * @param int $eventId
     * @return EventInterface
     */
    public function getById($eventId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return EventSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete event
     *
     * @param EventInterface $event
     * @return bool true on success
     */
    public function delete(EventInterface $event);

    /**
     * Delete event by ID
     *
     * @param int $eventId
     * @return bool true on success
     */
    public function deleteById($eventId);
}
