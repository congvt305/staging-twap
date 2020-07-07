<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 3:30 PM
 */
namespace Eguana\EventManager\Api;

use Eguana\EventManager\Api\Data\EventManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared inter
 * interface EventManagerRepositoryInterface
 */
interface EventManagerRepositoryInterface
{
    /**
     * Save event.
     *
     * @param EventManagerInterface $event
     * @return mixed
     */
    public function save(EventManagerInterface $event);

    /**
     * Retrieve Event.
     *
     * @param $eventId
     * @return mixed
     */
    public function getById($eventId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete event.
     *
     * @param EventManagerInterface $event
     * @return mixed
     */
    public function delete(EventManagerInterface $event);

    /**
     * Delete event by ID.
     *
     * @param $eventId
     * @return mixed
     */
    public function deleteById($eventId);
}
