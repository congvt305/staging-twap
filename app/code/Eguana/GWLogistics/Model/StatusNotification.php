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
        // TODO: Implement getLogisticsType() method.
    }

    /**
     * @return string|null
     */
    public function getLogisticsSubType()
    {
        // TODO: Implement getLogisticsSubType() method.
    }

    /**
     * @return string|null
     */
    public function getReceiverName()
    {
        // TODO: Implement getReceiverName() method.
    }

    /**
     * @return string|null
     */
    public function getReceiverPhone()
    {
        // TODO: Implement getReceiverPhone() method.
    }

    /**
     * @return string|null
     */
    public function getReceiverCellPhone()
    {
        // TODO: Implement getReceiverCellPhone() method.
    }

    /**
     * @return string|null
     */
    public function getReceiverEmail()
    {
        // TODO: Implement getReceiverEmail() method.
    }

    /**
     * @return string|null
     */
    public function getReceiverAddress()
    {
        // TODO: Implement getReceiverAddress() method.
    }

    /**
     * @return int|null
     */
    public function getOrderId()
    {
        // TODO: Implement getOrderId() method.
    }

    /**
     * @return string|null
     */
    public function getMerchantId()
    {
        // TODO: Implement getMerchantId() method.
    }

    /**
     * @return string|null
     */
    public function getMerchantTradeNo()
    {
        // TODO: Implement getMerchantTradeNo() method.
    }

    /**
     * @return string|null
     */
    public function getRtnCode()
    {
        // TODO: Implement getRtnCode() method.
    }

    /**
     * @return string|null
     */
    public function getRtnMsg()
    {
        // TODO: Implement getRtnMsg() method.
    }

    /**
     * @return string|null
     */
    public function getAllPayLogisticsId()
    {
        // TODO: Implement getAllPayLogisticsId() method.
    }

    /**
     * @return int|null
     */
    public function getGoodsAmount()
    {
        // TODO: Implement getGoodsAmount() method.
    }

    /**
     * @return string|null
     */
    public function getUpdateStatusDate()
    {
        // TODO: Implement getUpdateStatusDate() method.
    }

    /**
     * @param int $orderId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setOrderId($orderId)
    {
        // TODO: Implement setOrderId() method.
    }

    /**
     * @param string $merchantId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setMerchantId($merchantId)
    {
        // TODO: Implement setMerchantId() method.
    }

    /**
     * @param string $merchantTradeNo
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setMerchantTradeNo($merchantTradeNo)
    {
        // TODO: Implement setMerchantTradeNo() method.
    }

    /**
     * @param string $rtnCode
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setRtnCode($rtnCode)
    {
        // TODO: Implement setRtnCode() method.
    }

    /**
     * @param string $rtnMsg
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setRtnMsg($rtnMsg)
    {
        // TODO: Implement setRtnMsg() method.
    }

    /**
     * @param string $allPayLogisticsId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setAllPayLogisticsId($allPayLogisticsId)
    {
        // TODO: Implement setAllPayLogisticsId() method.
    }

    /**
     * @param int $goodsAmount
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setGoodsAmount($goodsAmount)
    {
        // TODO: Implement setGoodsAmount() method.
    }

    /**
     * @param string $updateStatusDate
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setUpdateStatusDate($updateStatusDate)
    {
        // TODO: Implement setUpdateStatusDate() method.
    }

    /**
     * @param $LogisticsType
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setLogisticsType($LogisticsType)
    {
        // TODO: Implement setLogisticsType() method.
    }

    /**
     * @param $logisticsSubType
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setLogisticsSubType($logisticsSubType)
    {
        // TODO: Implement setLogisticsSubType() method.
    }

    /**
     * @param $receiverName
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverName($receiverName)
    {
        // TODO: Implement setReceiverName() method.
    }

    /**
     * @param $receiverPhone
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverPhone($receiverPhone)
    {
        // TODO: Implement setReceiverPhone() method.
    }

    /**
     * @param $receiverCellPhone
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverCellPhone($receiverCellPhone)
    {
        // TODO: Implement setReceiverCellPhone() method.
    }

    /**
     * @param $receiverEmail
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverEmail($receiverEmail)
    {
        // TODO: Implement setReceiverEmail() method.
    }

    /**
     * @param $receiverAddress
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverAddress($receiverAddress)
    {
        // TODO: Implement setReceiverAddress() method.
    }
}
