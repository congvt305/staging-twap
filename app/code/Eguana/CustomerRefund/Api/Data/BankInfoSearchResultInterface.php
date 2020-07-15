<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/12/20
 * Time: 3:01 PM
 */

namespace Eguana\CustomerRefund\Api\Data;


interface BankInfoSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface[]
     */
    public function getItems();

    /**
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}
