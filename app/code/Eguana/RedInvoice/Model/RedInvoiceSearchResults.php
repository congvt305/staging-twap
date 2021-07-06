<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
declare(strict_types=1);

namespace Eguana\RedInvoice\Model;

use Eguana\RedInvoice\Api\Data\RedInvoiceSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Counter search results.
 *
 * Class RedInvoiceSearchResults
 */
class RedInvoiceSearchResults extends SearchResults implements RedInvoiceSearchResultsInterface
{
}
