<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 21/10/20
 * Time: 11:03 PM
 */
namespace Eguana\EventReservation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Eguana\EventReservation\Api\Data\CounterInterface;

/**
 * Interface for event_reservation_counter search results.
 *
 * Interface CounterSearchResultsInterface
 */
interface CounterSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get counters list.
     *
     * @return ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * Set counters list.
     *
     * @param CounterInterface[] $items
     * @return CounterSearchResultsInterface
     */
    public function setItems(array $items);
}
