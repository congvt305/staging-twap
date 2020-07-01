<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 2:13 PM
 */

namespace Eguana\GWLogistics\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface QuoteCvsLocationSearchResultInterface extends SearchResultsInterface
{

    /**
     * @return QuoteCvsLocationInterface[]
     */
    public function getItems();

    /**
     * @param QuoteCvsLocationInterface[] $items
     * @return void
     */
    public function setItems(array $items);

}
