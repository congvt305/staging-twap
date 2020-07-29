<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 4:16 PM
 */

namespace Eguana\GWLogistics\Api\Data;


use Magento\Framework\Api\SearchResultsInterface;

interface StatusNotificationSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return $this
     */
    public function setItems(array $items);

}
