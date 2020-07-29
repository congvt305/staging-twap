<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 7:47 AM
 */

namespace Eguana\GWLogistics\Model;


use Magento\Framework\Model\AbstractModel;

class StatusNotification extends AbstractModel implements \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
{
    protected function _construct()
    {
        $this->_init(\Eguana\GWLogistics\Model\ResourceModel\StatusNotification::class);
    }

    /**
     * @return string|null
     */
    public function getLogisticsType()
    {
        return $this->getData(self::LOGISTICS_TYPE);
    }

    /**
     * @return string|null
     */
    public function getLogisticsSubType()
    {
        return $this->getData(self::LOGISTICS_SUB_TYPE);
    }

    /**
     * @return string|null
     */
    public function getReceiverName()
    {
        return $this->getData(self::RECEIVER_NAME);
    }

    /**
     * @return string|null
     */
    public function getReceiverPhone()
    {
        return $this->getData(self::RECEIVER_PHONE);
    }

    /**
     * @return string|null
     */
    public function getReceiverCellPhone()
    {
        return $this->getData(self::RECEIVER_CELL_PHONE);
    }

    /**
     * @return string|null
     */
    public function getReceiverEmail()
    {
        return $this->getData(self::RECEIVER_EMAIL);
    }

    /**
     * @return string|null
     */
    public function getReceiverAddress()
    {
        return $this->getData(self::RECEIVER_ADDRESS);
    }

    /**
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
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
    public function getMerchantTradeNo()
    {
        return $this->getData(self::RTN_CODE);
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
     * @param int $orderId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @param string $merchantId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setMerchantId($merchantId)
    {
        return $this->setData(self::MERCHANT_ID, $merchantId);
    }

    /**
     * @param string $merchantTradeNo
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setMerchantTradeNo($merchantTradeNo)
    {
        return $this->setData(self::MERCHANT_TRADE_NO, $merchantTradeNo);
    }

    /**
     * @param string $rtnCode
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setRtnCode($rtnCode)
    {
        return $this->setData(self::RTN_CODE, $rtnCode);
    }

    /**
     * @param string $rtnMsg
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setRtnMsg($rtnMsg)
    {
        return $this->setData(self::RTN_MSG, $rtnMsg);
    }

    /**
     * @param string $allPayLogisticsId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setAllPayLogisticsId($allPayLogisticsId)
    {
        return $this->setData(self::ALL_PAY_LOGISTICS_ID, $allPayLogisticsId);
    }

    /**
     * @param int $goodsAmount
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setGoodsAmount($goodsAmount)
    {
        return $this->setData(self::GOODS_AMOUNT, $goodsAmount);
    }

    /**
     * @param string $updateStatusDate
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setUpdateStatusDate($updateStatusDate)
    {
        return $this->setData(self::UPDATE_STATUS_DATE, $updateStatusDate);
    }

    /**
     * @param $logisticsType
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setLogisticsType($logisticsType)
    {
        return $this->setData(self::LOGISTICS_TYPE, $logisticsType);
    }

    /**
     * @param $logisticsSubType
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setLogisticsSubType($logisticsSubType)
    {
        return $this->setData(self::LOGISTICS_SUB_TYPE, $logisticsSubType);
    }

    /**
     * @param $receiverName
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverName($receiverName)
    {
        return $this->setData(self::RECEIVER_NAME, $receiverName);
    }

    /**
     * @param $receiverPhone
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverPhone($receiverPhone)
    {
        return $this->setData(self::RECEIVER_PHONE, $receiverPhone);
    }

    /**
     * @param $receiverCellPhone
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverCellPhone($receiverCellPhone)
    {
        return $this->setData(self::RECEIVER_CELL_PHONE, $receiverCellPhone);
    }

    /**
     * @param $receiverEmail
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverEmail($receiverEmail)
    {
        return $this->setData(self::RECEIVER_EMAIL, $receiverEmail);
    }

    /**
     * @param $receiverAddress
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverAddress($receiverAddress)
    {
        return $this->setData(self::RECEIVER_ADDRESS, $receiverAddress);
    }
}
