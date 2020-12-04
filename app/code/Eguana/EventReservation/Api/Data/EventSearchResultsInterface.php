<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/10/20
 * Time: 7:36 PM
 */
namespace Eguana\EventReservation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Eguana\EventReservation\Api\Data\EventInterface;

/**
 * Interface for event_reservation search results.
 *
 * Interface EventSearchResultsInterface
 */
interface EventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get events list.
     *
     * @return ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * Set events list.
     *
     * @param EventInterface[] $items
     * @return EventSearchResultsInterface
     */
    public function setItems(array $items);
}
