<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/26/19
 * Time: 5:33 PM
 */
namespace Eguana\StoreLocator\Model;

use Eguana\StoreLocator\Api\Data\StoreInfoSearchResultInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory as StoreInfoCollection;
use Magento\Framework\Api\SearchResults;

/**
 * StoreInfoSearchResult
 *
 * Class StoreInfoSearchResult
 *  Eguana\StoreLocator\Model
 */
class StoreInfoSearchResult extends SearchResults implements StoreInfoSearchResultInterface
{

}
