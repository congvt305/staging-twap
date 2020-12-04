<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 13/10/20
 * Time: 2:34 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Model;

use Eguana\Redemption\Api\Data\RedemptionSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Redemption search results.
 *
 * Class RedemptionSearchResults
 */
class RedemptionSearchResults extends SearchResults implements RedemptionSearchResultsInterface
{
}
