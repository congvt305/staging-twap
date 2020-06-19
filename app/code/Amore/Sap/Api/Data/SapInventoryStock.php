<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-17
 * Time: 오후 5:04
 */

namespace Amore\Sap\Api\Data;

interface SapInventoryStock
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

    /**
     * @return string
     */
    public function getSource();

    /**
     * @param string $source
     */
    public function setSource($source);

}
