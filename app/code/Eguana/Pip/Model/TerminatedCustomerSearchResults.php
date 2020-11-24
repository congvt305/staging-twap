<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 6:20 PM
 */
namespace Eguana\Pip\Model;

use Eguana\Pip\Api\Data\TerminatedCustomerSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Terminated Customer search results.
 *
 * Class TerminatedCustomerSearchResults
 */
class TerminatedCustomerSearchResults extends SearchResults implements TerminatedCustomerSearchResultsInterface
{
}
