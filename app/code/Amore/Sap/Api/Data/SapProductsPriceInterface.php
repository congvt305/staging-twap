<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오후 12:11
 */

namespace Amore\Sap\Api\Data;

interface SapProductsPriceInterface
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
    public function getPltyp();

    /**
     * @param string $pltyp
     * @return $this
     */
    public function setPltyp($pltyp);

    /**
     * @return string
     */
    public function getWaerk();

    /**
     * @param string $waerk
     * @return $this
     */
    public function setWaerk($waerk);

    /**
     * @return float
     */
    public function getKbetrInv();

    /**
     * @param float $kbetrInv
     * @return $this
     */
    public function setKbetrInv($kbetrInv);

}
