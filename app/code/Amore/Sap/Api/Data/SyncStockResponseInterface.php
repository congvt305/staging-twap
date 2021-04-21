<?php
/**
 * Created by Eguana.
 * User: raheel
 * Date: 8/4/21
 * Time: 4:00 PM
 */
namespace Amore\Sap\Api\Data;

/**
 * Interface for api to update stock data synchronously
 *
 * Interface SyncStockResponseInterface
 */
interface SyncStockResponseInterface
{
    /**
     * Get code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     * @return mixed
     */
    public function setCode($code);

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set message
     *
     * @param string $message
     * @return mixed
     */
    public function setMessage($message);

    /**
     * Get stockData
     *
     * @return \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[]
     */
    public function getData();

    /**
     * Set data
     *
     * @param \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface[] $data
     * @return mixed
     */
    public function setData($data);
}
