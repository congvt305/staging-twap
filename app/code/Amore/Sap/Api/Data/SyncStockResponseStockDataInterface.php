<?php
/**
 * Created by Eguana.
 * User: raheel
 * Date: 8/4/21
 * Time: 4:00 PM
 */
namespace Amore\Sap\Api\Data;

/**
 * Interface for stockData of api update stock data synchronously
 *
 * Interface SyncStockResponseStockDataInterface
 */
interface SyncStockResponseStockDataInterface
{
    /**
     * Get matnr (sku)
     *
     * @return string
     */
    public function getMatnr();

    /**
     * Set matnr (sku)
     *
     * @param string $matnr
     * @return mixed
     */
    public function setMatnr($matnr);

    /**
     * Get labst (available stock)
     *
     * @return string
     */
    public function getLabst();

    /**
     * Set labst (available stock)
     *
     * @param string $labst
     * @return mixed
     */
    public function setLabst($labst);
}
