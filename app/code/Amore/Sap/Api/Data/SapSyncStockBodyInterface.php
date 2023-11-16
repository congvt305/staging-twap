<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오전 11:58
 */

namespace Amore\Sap\Api\Data;

interface SapSyncStockBodyInterface
{
    /**
     * @return \Amore\Sap\Api\Data\SapInventoryStockInterface
     */
    public function getStockData();

    /**
     * @param \Amore\Sap\Api\Data\SapInventoryStockInterface $stockData
     * @return $this
     */
    public function setStockData($stockData);
}
