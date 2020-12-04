<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 21/10/20
 * Time: 11:12 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\CounterSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Counter search results.
 *
 * Class CounterSearchResults
 */
class CounterSearchResults extends SearchResults implements CounterSearchResultsInterface
{
}
