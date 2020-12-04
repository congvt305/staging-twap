<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 7:22 PM
 */
namespace Eguana\CustomerBulletin\Api;

use Eguana\CustomerBulletin\Api\Data\TicketInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared inter
 * interface TicketRepositoryInterface
 */
interface TicketRepositoryInterface
{
    /**
     * Save ticket.
     *
     * @param TicketInterface $ticket
     * @return TicketInterface
     */
    public function save(TicketInterface $ticket);

    /**
     * Retrieve Tickets.
     *
     * @param int $ticketId
     * @return TicketInterface
     */
    public function getById($ticketId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete ticket.
     *
     * @param TicketInterface $ticket
     * @return bool true on success
     */
    public function delete(TicketInterface $ticket);

    /**
     * Delete ticket by ID.
     *
     * @param $ticketId
     * @return bool true on success
     */
    public function deleteById($ticketId);
}
