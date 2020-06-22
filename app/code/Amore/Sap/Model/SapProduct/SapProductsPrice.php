<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 6:04
 */

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Api\Data\SapProductsPriceInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SapProductsPrice extends AbstractExtensibleModel implements SapProductsPriceInterface
{
    public function getMatnr()
    {
        return $this->getData('matnr');
    }

    public function setMatnr($matnr)
    {
        return $this->setData('matnr', $matnr);
    }

    public function getPltyp()
    {
        return $this->getData('pltyp');
    }

    public function setPltyp($pltyp)
    {
        return $this->setData('pltyp', $pltyp);
    }

    public function getWaerk()
    {
        return $this->getData('waerk');
    }

    public function setWaerk($waerk)
    {
        return $this->setData('waerk', $waerk);
    }

    public function getKbetrInv()
    {
        return $this->getData('kbetrInv');
    }

    public function setKbetrInv($kbetrInv)
    {
        return $this->setData('kbetrInv', $kbetrInv);
    }
}
