<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오후 12:25
 */

namespace Amore\Sap\Api\Data;

interface SapProductsDetailInterface
{
    const MATNR = 'matnr';
    const VKORG = 'vkorg';
    const BISMT = 'bismt';
    const BRGEW = 'brgew';
    const GEWEI = 'gewei';
    const BRAND = 'brand';
    const BCTXT_KO = 'bctxtKo';
    const MEINS = 'meins';
    const MSTAV = 'mstav';
    const SPART = 'spart';
    const MAXLZ = 'maxlz';
    const BREIT = 'breit';
    const HOEHE = 'hoehe';
    const LAENG = 'laeng';
    const KONDM = 'kondm';
    const MVGR1 = 'mvgr1';
    const MVGR2 = 'mvgr2';
    const PRODH = 'prodh';
    const VMSTA = 'mvsta';
    const MATNR2 = 'matnr2';
    const SETID = 'setid';
    const BLINE = 'bline';
    const CSMTP = 'csmtp';
    const SETDI = 'setdi';
    const MATSHINSUN = 'matshinsun';
    const MATVESSEL = 'matvessel';
    const PRDVL = 'prdvl';
    const VLUNT = 'vlunt';
    const CPIAP = 'cpiap';
    const PRDTP = 'prdtp';
    const RPFUT = 'rpfut';
    const MAKTX_EN = 'maktxEn';
    const MAKTX_ZH = 'maktxZh';
    const BCTXT_EN = 'bctxtEn';
    const BCTXT_ZH = 'bctxtZh';
    const REFILL = 'refill';
    const MATCOL = 'matcol';
    const DESC = 'desc';
    const PRECAU = 'precau';

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
    public function getVkorg();

    /**
     * @param string $vkorg
     * @return $this
     */
    public function setVkorg($vkorg);

    /**
     * @return string
     */
    public function getBismt();

    /**
     * @param string $bismt
     * @return $this
     */
    public function setBismt($bismt);

    /**
     * @return string
     */
    public function getBrgew();

    /**
     * @param string $brgew
     * @return $this
     */
    public function setBrgew($brgew);

    /**
     * @return string
     */
    public function getGewei();

    /**
     * @param string $gewei
     * @return $this
     */
    public function setGewei($gewei);

    /**
     * @return string
     */
    public function getBrand();

    /**
     * @param string $brand
     * @return $this
     */
    public function setBrand($brand);

    /**
     * @return string
     */
    public function getBctxtKo();

    /**
     * @param string $bctxtKo
     * @return $this
     */
    public function setBctxtKo($bctxtKo);

    /**
     * @return string
     */
    public function getMeins();

    /**
     * @param string $meins
     * @return $this
     */
    public function setMeins($meins);

    /**
     * @return string
     */
    public function getMstav();

    /**
     * @param string $mstav
     * @return $this
     */
    public function setMstav($mstav);

    /**
     * @return string
     */
    public function getSpart();

    /**
     * @param string $spart
     * @return $this
     */
    public function setSpart($spart);

    /**
     * @return string
     */
    public function getMaxlz();

    /**
     * @param string $maxlz
     * @return $this
     */
    public function setMaxlz($maxlz);

    /**
     * @return string
     */
    public function getBreit();

    /**
     * @param string $breit
     * @return $this
     */
    public function setBreit($breit);

    /**
     * @return string
     */
    public function getHoehe();

    /**
     * @param string $hoehe
     * @return $this
     */
    public function setHoehe($hoehe);

    /**
     * @return string
     */
    public function getLaeng();

    /**
     * @param string $laeng
     * @return $this
     */
    public function setLaeng($laeng);

    /**
     * @return string
     */
    public function getKondm();

    /**
     * @param string $kondm
     * @return $this
     */
    public function setKondm($kondm);

    /**
     * @return string
     */
    public function getMvgr1();

    /**
     * @param string $mvgr1
     * @return $this
     */
    public function setMvgr1($mvgr1);

    /**
     * @return string
     */
    public function getMvgr2();

    /**
     * @param string $mvgr2
     * @return $this
     */
    public function setMvgr2($mvgr2);

    /**
     * @return string
     */
    public function getProdh();

    /**
     * @param string $prodh
     * @return $this
     */
    public function setProdh($prodh);

    /**
     * @return string
     */
    public function getVmsta();

    /**
     * @param string $vmsta
     * @return $this
     */
    public function setVmsta($vmsta);

    /**
     * @return string
     */
    public function getMatnr2();

    /**
     * @param string $matnr2
     * @return $this
     */
    public function setMatnr2($matnr2);

    /**
     * @return string
     */
    public function getSetid();

    /**
     * @param string $setid
     * @return $this
     */
    public function setSetid($setid);

    /**
     * @return string
     */
    public function getBline();

    /**
     * @param string $bline
     * @return $this
     */
    public function setBline($bline);

    /**
     * @return string
     */
    public function getCsmtp();

    /**
     * @param string $csmtp
     * @return $this
     */
    public function setCsmtp($csmtp);

    /**
     * @return string
     */
    public function getSetdi();

    /**
     * @param string $setdi
     * @return $this
     */
    public function setSetdi($setdi);

    /**
     * @return string
     */
    public function getMatshinsun();

    /**
     * @param string $matshinsun
     * @return $this
     */
    public function setMatshinsun($matshinsun);

    /**
     * @return string
     */
    public function getMatvessel();

    /**
     * @param string $matvessel
     * @return $this
     */
    public function setMatvessel($matvessel);

    /**
     * @return string
     */
    public function getPrdvl();

    /**
     * @param string $prdvl
     * @return $this
     */
    public function setPrdvl($prdvl);

    /**
     * @return string
     */
    public function getVlunt();

    /**
     * @param string $vlunt
     * @return $this
     */
    public function setVlunt($vlunt);

    /**
     * @return string
     */
    public function getCpiap();

    /**
     * @param string $cpiap
     * @return $this
     */
    public function setCpiap($cpiap);

    /**
     * @return string
     */
    public function getPrdtp();

    /**
     * @param string $prdtp
     * @return $this
     */
    public function setPrdtp($prdtp);

    /**
     * @return string
     */
    public function getRpfut();

    /**
     * @param string $rpfut
     * @return $this
     */
    public function setRpfut($rpfut);

    /**
     * @return string
     */
    public function getMaktxEn();

    /**
     * @param string $maktxEn
     * @return $this
     */
    public function setMaktxEn($maktxEn);

    /**
     * @return string
     */
    public function getMaktxZh();

    /**
     * @param string $maktxZh
     * @return $this
     */
    public function setMaktxZh($maktxZh);

    /**
     * @return string
     */
    public function getBctxtEn();

    /**
     * @param string $bctxtEn
     * @return $this
     */
    public function setBctxtEn($bctxtEn);

    /**
     * @return string
     */
    public function getBctxtZh();

    /**
     * @param string $bctxtZh
     * @return $this
     */
    public function setBctxtZh($bctxtZh);

    /**
     * @return string
     */
    public function getRefill();

    /**
     * @param string $refill
     * @return $this
     */
    public function setRefill($refill);

    /**
     * @return string
     */
    public function getMatcol();

    /**
     * @param string $matcol
     * @return $this
     */
    public function setMatcol($matcol);

    /**
     * @return string
     */
    public function getDesc();

    /**
     * @param string $desc
     * @return $this
     */
    public function setDesc($desc);

    /**
     * @return string
     */
    public function getPrecau();

    /**
     * @param string $precau
     * @return $this
     */
    public function setPrecau($precau);
}
