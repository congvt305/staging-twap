<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 6:03
 */

namespace Amore\Sap\Model\SapProduct;

use Amore\Sap\Api\Data\SapInventoryStockInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SapInventoryStock extends AbstractExtensibleModel implements SapInventoryStockInterface
{
    public function getMatnr()
    {
        return $this->getData('matnr');
    }

    public function setMatnr($matnr)
    {
        return $this->setData('matnr', $matnr);
    }

    public function getLabst()
    {
        return $this->getData('labst');
    }

    public function setLabst($labst)
    {
        return $this->setData('labst', $labst);
    }

    public function getMallId()
    {
        return $this->getData('mallId');
    }

    public function setMallId($mallId)
    {
        return $this->setData('mallId', $mallId);
    }

    public function getSource()
    {
        return $this->getData('source');
    }

    public function setSource($source)
    {
        return $this->setData('source', $source);
    }
}
