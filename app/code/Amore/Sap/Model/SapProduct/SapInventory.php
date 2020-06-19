<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-17
 * Time: 오후 5:16
 */

namespace Amore\Sap\Model\SapProduct;

use Magento\Framework\Model\AbstractExtensibleModel;

class SapInventory extends AbstractExtensibleModel implements \Amore\Sap\Api\Data\SapInventoryStock
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

    public function getSource()
    {
        $this->getData('source');
    }

    public function setSource($source)
    {
        $this->setData('source', $source);
    }
}
