<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 05:34 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\UserReservationSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with UserReservation search results.
 *
 * Class UserReservationSearchResults
 */
class UserReservationSearchResults extends SearchResults implements UserReservationSearchResultsInterface
{
}
