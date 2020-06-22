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
     * @return $this
     */
    public function setMatnr($matnr);

    /**
     * @return string
     */
    public function getLabst();

    /**
     * @param string $labst
     * @return $this
     */
    public function setLabst($labst);

    /**
     * @return string
     */
    public function getMallId();

    /**
     * @param string $mallId
     * @return $this
     */
    public function setMallId($mallId);

}
