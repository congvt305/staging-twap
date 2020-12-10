<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 4:40 PM
 */
namespace Eguana\CustomerBulletin\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for ticket search results.
 * Interface TicketSearchResultsInterface
 */
interface TicketSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Tickets list.
     *
     * @return TicketInterface[]
     */
    public function getItems();

    /**
     * Set Tickets list.
     *
     * @param TicketInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
