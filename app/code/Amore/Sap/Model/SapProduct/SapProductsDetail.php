<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 6:04
 */

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Api\Data\SapProductsDetailInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SapProductsDetail extends AbstractExtensibleModel implements SapProductsDetailInterface
{
    public function getMatnr()
    {
        return $this->getData(self::MATNR);
    }

    public function setMatnr($matnr)
    {
        return $this->setData(self::MATNR, $matnr);
    }

    public function getVkorg()
    {
        return $this->getData(self::VKORG);
    }

    public function setVkorg($vkorg)
    {
        return $this->setData(self::VKORG, $vkorg);
    }

    public function getBismt()
    {
        return $this->getData(self::BISMT);
    }

    public function setBismt($bismt)
    {
        return $this->setData(self::BISMT, $bismt);
    }

    public function getBrgew()
    {
        return $this->getData(self::BRGEW);
    }

    public function setBrgew($brgew)
    {
        return $this->setData(self::BRGEW, $brgew);
    }

    public function getGewei()
    {
        return $this->getData(self::GEWEI);
    }

    public function setGewei($gewei)
    {
        return $this->setData(self::GEWEI, $gewei);
    }

    public function getBrand()
    {
        return $this->getData(self::BRAND);
    }

    public function setBrand($brand)
    {
        return $this->setData(self::BRAND, $brand);
    }

    public function getBctxtKo()
    {
        return $this->getData(self::BCTXT_KO);
    }

    public function setBctxtKo($bctxtKo)
    {
        return $this->setData(self::BCTXT_KO, $bctxtKo);
    }

    public function getMeins()
    {
        return $this->getData(self::MEINS);
    }

    public function setMeins($meins)
    {
        return $this->setData(self::MEINS, $meins);
    }

    public function getMstav()
    {
        return $this->getData(self::MSTAV);
    }

    public function setMstav($mstav)
    {
        return $this->setData(self::MSTAV, $mstav);
    }

    public function getSpart()
    {
        return $this->getData(self::SPART);
    }

    public function setSpart($spart)
    {
        return $this->setData(self::SPART);
    }

    public function getMaxlz()
    {
        return $this->getData(self::MAXLZ);
    }

    public function setMaxlz($maxlz)
    {
        return $this->setData(self::MAXLZ, $maxlz);
    }

    public function getBreit()
    {
        return $this->getData(self::BREIT);
    }

    public function setBreit($breit)
    {
        return $this->setData(self::BREIT, $breit);
    }

    public function getHoehe()
    {
        return $this->getData(self::HOEHE);
    }

    public function setHoehe($hoehe)
    {
        return $this->setData(self::HOEHE, $hoehe);
    }

    public function getLaeng()
    {
        return $this->getData(self::LAENG);
    }

    public function setLaeng($laeng)
    {
        return $this->setData(self::LAENG, $laeng);
    }

    public function getKondm()
    {
        return $this->getData(self::KONDM);
    }

    public function setKondm($kondm)
    {
        return $this->setData(self::KONDM, $kondm);
    }

    public function getMvgr1()
    {
        return $this->getData(self::MVGR1);
    }

    public function setMvgr1($mvgr1)
    {
        return $this->setData(self::MVGR1, $mvgr1);
    }

    public function getMvgr2()
    {
        return $this->getData(self::MVGR2);
    }

    public function setMvgr2($mvgr2)
    {
        return $this->setData(self::MVGR2 ,$mvgr2);
    }

    public function getProdh()
    {
        return $this->getData(self::PRODH);
    }

    public function setProdh($prodh)
    {
        return $this->setData(self::PRODH, $prodh);
    }

    public function getVmsta()
    {
        return $this->getData(self::VMSTA);
    }

    public function setVmsta($vmsta)
    {
        return $this->setData(self::VMSTA, $vmsta);
    }

    public function getMatnr2()
    {
        return $this->getData(self::MATNR2);
    }

    public function setMatnr2($matnr2)
    {
        return $this->setData(self::MATNR2, $matnr2);
    }

    public function getSetid()
    {
        return $this->getData(self::SETID);
    }

    public function setSetid($setid)
    {
        return $this->setData(self::SETID, $setid);
    }

    public function getBline()
    {
        return $this->getData(self::BLINE);
    }

    public function setBline($bline)
    {
        return $this->setData(self::BLINE, $bline);
    }

    public function getCsmtp()
    {
        return $this->getData(self::CSMTP);
    }

    public function setCsmtp($csmtp)
    {
        return $this->setData(self::CSMTP, $csmtp);
    }

    public function getSetdi()
    {
        return $this->getData(self::SETDI);
    }

    public function setSetdi($setdi)
    {
        return $this->setData(self::SETDI, $setdi);
    }

    public function getMatshinsun()
    {
        return $this->getData(self::MATSHINSUN);
    }

    public function setMatshinsun($matshinsun)
    {
        return $this->setData(self::MATSHINSUN, $matshinsun);
    }

    public function getMatvessel()
    {
        return $this->getData(self::MATVESSEL);
    }

    public function setMatvessel($matvessel)
    {
        return $this->setData(self::MATVESSEL, $matvessel);
    }

    public function getPrdvl()
    {
        return $this->getData(self::PRDVL);
    }

    public function setPrdvl($prdvl)
    {
        return $this->setData(self::PRDVL, $prdvl);
    }

    public function getVlunt()
    {
        return $this->getData(self::VLUNT);
    }

    public function setVlunt($vlunt)
    {
        return $this->setData(self::VLUNT, $vlunt);
    }

    public function getCpiap()
    {
        return $this->getData(self::CPIAP);
    }

    public function setCpiap($cpiap)
    {
        return $this->setData(self::CPIAP, $cpiap);
    }

    public function getPrdtp()
    {
        return $this->getData(self::PRDTP);
    }

    public function setPrdtp($prdtp)
    {
        return $this->setData(self::PRDTP, $prdtp);
    }

    public function getRpfut()
    {
        return $this->getData(self::RPFUT);
    }

    public function setRpfut($rpfut)
    {
        return $this->setData(self::RPFUT, $rpfut);
    }

    public function getMaktxEn()
    {
        return $this->getData(self::MAKTX_EN);
    }

    public function setMaktxEn($maktxEn)
    {
        return $this->setData(self::MAKTX_EN, $maktxEn);
    }

    public function getMaktxZh()
    {
        return $this->getData(self::MAKTX_ZH);
    }

    public function setMaktxZh($maktxZh)
    {
        return $this->setData(self::MAKTX_ZH, $maktxZh);
    }

    public function getBctxtEn()
    {
        return $this->getData(self::BCTXT_EN);
    }

    public function setBctxtEn($bctxtEn)
    {
        return $this->setData(self::BCTXT_EN, $bctxtEn);
    }

    public function getBctxtZh()
    {
        return $this->getData(self::BCTXT_ZH);
    }

    public function setBctxtZh($bctxtZh)
    {
        return $this->setData(self::BCTXT_ZH, $bctxtZh);
    }

    public function getRefill()
    {
        return $this->getData(self::REFILL);
    }

    public function setRefill($refill)
    {
        return $this->setData(self::REFILL, $refill);
    }

    public function getMatcol()
    {
        return $this->getData(self::MATCOL);
    }

    public function setMatcol($matcol)
    {
        return $this->setData(self::MATCOL, $matcol);
    }

    public function getDesc()
    {
        return $this->getData(self::DESC);
    }

    public function setDesc($desc)
    {
        return $this->setData(self::DESC, $desc);
    }

    public function getPrecau()
    {
        return $this->getData(self::PRECAU);
    }

    public function setPrecau($precau)
    {
        return $this->setData(self::PRECAU, $precau);
    }
}
