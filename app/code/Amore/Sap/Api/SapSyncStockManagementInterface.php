<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/4/21
 * Time: 2:00 PM
 */
namespace Amore\Sap\Api;

/**
 * Interface SapSyncStockManagementInterface
 * @api
 * Interface to manage sync stock
 */
interface SapSyncStockManagementInterface
{
    /**
     * To update inventory stocks synchronously (multiple stocks)
     *
     * @param string $source
     * @param string $mallId
     * @param \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[] $stockData
     * @return \Amore\Sap\Api\Data\SyncStockResponseInterface
     */
    public function inventorySyncStockUpdate($source, $mallId, $stockData);
}
