<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/10/20
 * Time: 11:40 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Model;

use Eguana\Redemption\Api\Data\CounterSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Counter search results.
 *
 * Class CounterSearchResults
 */
class CounterSearchResults extends SearchResults implements CounterSearchResultsInterface
{
}
