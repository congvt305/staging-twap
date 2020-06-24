<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오후 12:12
 */

namespace Amore\Sap\Api\Data;

interface SapOrderStatusInterface
{
    const SOURCE = 'source';
    const ORDER_NO = 'odrno';
    const ORDER_STATUS = 'odrstat';
    const TRACKING_NO = 'ztrackId';
    const SAP_ORDER_CREATION_FAIL_CODE = 'ugcod';
    const SAP_ORDER_CREATION_FAIL_REASON = 'ugtxt';
    const MALL_ID = 'mallId';

    /**
     * @return string
     */
    public function getSource();

    /**
     * @return $this
     * @param string $source
     */
    public function setSource($source);

    /**
     * @return string
     */
    public function getOdrno();

    /**
     * @return $this
     * @param string $odrno
     */
    public function setOdrno($odrno);

    /**
     * @return string
     */
    public function getOdrstat();

    /**
     * @return $this
     * @param string $odrstat
     */
    public function setOdrstat($odrstat);

    /**
     * @return string
     */
    public function getZtrackId();

    /**
     * @return $this
     * @param string $ztrackId
     */
    public function setZtrackId($ztrackId);

    /**
     * @return string
     */
    public function getUgcod();

    /**
     * @return $this
     * @param string $ugcod
     */
    public function setUgcod($ugcod);

    /**
     * @return string
     */
    public function getUgtxt();

    /**
     * @return $this
     * @param string $ugtxt
     */
    public function setUgtxt($ugtxt);

    /**
     * @return string
     */
    public function getMallId();

    /**
     * @return $this
     * @param string $mallId
     */
    public function setMallId($mallId);

}
