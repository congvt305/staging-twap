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
        $this->getData('matnr');
    }

    public function setMatnr($matnr)
    {
        $this->setData('matnr', $matnr);
    }

    public function getLabst()
    {
        $this->getData('labst');
    }

    public function setLabst($labst)
    {
        $this->setData('labst', $labst);
    }

    public function getMallId()
    {
        $this->getData('mallId');
    }

    public function setMallId($mallId)
    {
        $this->setData('mallId', $mallId);
    }
}
