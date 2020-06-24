<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-02
 * Time: 오전 11:16
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Api\Data\SapOrderStatusInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SapOrderStatus extends AbstractExtensibleModel implements SapOrderStatusInterface
{

    public function getSource()
    {
        return $this->getData(self::SOURCE);
    }

    public function setSource($source)
    {
        return $this->setData(self::SOURCE, $source);
    }

    public function getOdrno()
    {
        return $this->getData(self::ORDER_NO);
    }

    public function setOdrno($odrno)
    {
        return $this->setData(self::ORDER_NO, $odrno);
    }

    public function getOdrstat()
    {
        return $this->getData(self::ORDER_STATUS);
    }

    public function setOdrstat($odrstat)
    {
        return $this->setData(self::ORDER_STATUS, $odrstat);
    }

    public function getZtrackId()
    {
        return $this->getData(self::TRACKING_NO);
    }

    public function setZtrackId($ztrackId)
    {
        return $this->setData(self::TRACKING_NO, $ztrackId);
    }

    public function getUgcod()
    {
        return $this->getData(self::SAP_ORDER_CREATION_FAIL_CODE);
    }

    public function setUgcod($ugcod)
    {
        return $this->setData(self::SAP_ORDER_CREATION_FAIL_CODE, $ugcod);
    }

    public function getUgtxt()
    {
        return $this->getData(self::SAP_ORDER_CREATION_FAIL_REASON);
    }

    public function setUgtxt($ugtxt)
    {
        return $this->setData(self::SAP_ORDER_CREATION_FAIL_REASON, $ugtxt);
    }

    public function getMallId()
    {
        return $this->getData(self::MALL_ID);
    }

    public function setMallId($mallId)
    {
        return $this->setData(self::MALL_ID, $mallId);
    }
}
