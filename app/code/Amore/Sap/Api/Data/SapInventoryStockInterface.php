<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오전 11:58
 */

namespace Amore\Sap\Api\Data;

interface SapInventoryStockInterface
{
    /**
     * @return string
     */
    public function getMatnr();

    /**
     * @param string $matnr
     */
    public function setMatnr($matnr);

    /**
     * @return string
     */
    public function getLabst();

    /**
     * @param string $labst
     */
    public function setLabst($labst);

    /**
     * @return string
     */
    public function getMallId();

    /**
     * @param string $mallId
     */
    public function setMallId($mallId);

}
