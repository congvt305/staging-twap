<?php
/**
 * Created by Eguana.
 * User: raheel
 * Date: 8/4/21
 * Time: 4:05 PM
 */
namespace Amore\Sap\Model\SapProduct;

/**
 * Response handler class for api to sync inventory stock
 *
 * Class SyncStockResponseStockData
 */
class SyncStockResponseStockData implements \Amore\Sap\Api\Data\SyncStockResponseStockDataInterface
{
    /**
     * @var
     */
    protected $matnr;

    /**
     * @var
     */
    protected $labst;

    /**
     * Get matnr
     *
     * @return string
     */
    public function getMatnr()
    {
        return $this->matnr;
    }

    /**
     * Set matnr
     *
     * @param string $matnr
     * @return mixed|string
     */
    public function setMatnr($matnr)
    {
        $this->matnr = $matnr;
    }

    /**
     * Get labst
     *
     * @return string
     */
    public function getLabst()
    {
        return $this->labst;
    }

    /**
     * Set labst
     *
     * @param string $labst
     * @return mixed|string
     */
    public function setLabst($labst)
    {
        return $this->labst = $labst;
    }
}
