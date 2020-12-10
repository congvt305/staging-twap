<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 19/10/20
 * Time: 12:34 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\EventSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Event search results.
 *
 * Class EventSearchResults
 */
class EventSearchResults extends SearchResults implements EventSearchResultsInterface
{
}
