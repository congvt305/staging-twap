<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 7:51 AM
 */

namespace Eguana\GWLogistics\Model;


use Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface;
use Magento\Framework\Model\AbstractModel;

class ReverseStatusNotification extends AbstractModel implements ReverseStatusNotificationInterface
{
    protected function _construct()
    {
       $this->_init(\Eguana\GWLogistics\Model\ResourceModel\ReverseStatusNotification::class);
    }

    /**
     * @return int|null
     */
    public function getRmaId()
    {
        return $this->getData(self::RMA_ID);
    }

    /**
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->getData(self::MERCHANT_ID);
    }

    /**
     * @return string|null
     */
    public function getRtnMerchantTradeNo()
    {
        return $this->getData(self::RTN_MERCHANT_TRADE_NO);
    }

    /**
     * @return string|null
     */
    public function getRtnCode()
    {
        return $this->getData(self::RTN_CODE);
    }

    /**
     * @return string|null
     */
    public function getRtnMsg()
    {
        return $this->getData(self::RTN_MSG);
    }

    /**
     * @return string|null
     */
    public function getAllPayLogisticsId()
    {
        return $this->getData(self::ALL_PAY_LOGISTICS_ID);
    }

    /**
     * @return int|null
     */
    public function getGoodsAmount()
    {
        return $this->getData(self::GOODS_AMOUNT);
    }

    /**
     * @return string|null
     */
    public function getUpdateStatusDate()
    {
        return $this->getData(self::UPDATE_STATUS_DATE);
    }

    /**
     * @param int $rmaId
     * @return ReverseStatusNotificationInterface
     */
    public function setRmaId($rmaId)
    {
        return $this->setData(self::RMA_ID, $rmaId);
    }

    /**
     * @param string $merchantId
     * @return ReverseStatusNotificationInterface
     */
    public function setMerchantId($merchantId)
    {
        return $this->setData(self::MERCHANT_ID, $merchantId);
    }

    /**
     * @param string $rtnMerchantTradeNo
     * @return ReverseStatusNotificationInterface
     */
    public function setRtnMerchantTradeNo($rtnMerchantTradeNo)
    {
        return $this->setData(self::RTN_MERCHANT_TRADE_NO, $rtnMerchantTradeNo);
    }

    /**
     * @param string $rtnCode
     * @return ReverseStatusNotificationInterface
     */
    public function setRtnCode($rtnCode)
    {
        return $this->setData(self::RTN_CODE, $rtnCode);
    }

    /**
     * @param string $rtnMsg
     * @return ReverseStatusNotificationInterface
     */
    public function setRtnMsg($rtnMsg)
    {
        return $this->setData(self::RTN_MSG, $rtnMsg);
    }

    /**
     * @param string $allPayLogisticsId
     * @return ReverseStatusNotificationInterface
     */
    public function setAllPayLogisticsId($allPayLogisticsId)
    {
        return $this->setData(self::ALL_PAY_LOGISTICS_ID, $allPayLogisticsId);
    }

    /**
     * @param int $goodsAmount
     * @return ReverseStatusNotificationInterface
     */
    public function setGoodsAmount($goodsAmount)
    {
        return $this->setData(self::GOODS_AMOUNT, $goodsAmount);
    }

    /**
     * @param string $updateStatusDate
     * @return ReverseStatusNotificationInterface
     */
    public function setUpdateStatusDate($updateStatusDate)
    {
        return $this->setData(self::UPDATE_STATUS_DATE, $updateStatusDate);
    }
}
