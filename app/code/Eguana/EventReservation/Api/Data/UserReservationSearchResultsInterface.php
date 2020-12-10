<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 4:12 PM
 */
namespace Eguana\EventReservation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Eguana\EventReservation\Api\Data\UserReservationInterface;

/**
 * Interface for event_user_reserve search results.
 *
 * Interface UserReservationSearchResultsInterface
 */
interface UserReservationSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get user reservations list.
     *
     * @return ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * Set user reservations list.
     *
     * @param UserReservationInterface[] $items
     * @return UserReservationSearchResultsInterface
     */
    public function setItems(array $items);
}
