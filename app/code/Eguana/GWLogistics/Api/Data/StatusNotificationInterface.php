<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 8:03 AM
 */

namespace Eguana\GWLogistics\Api\Data;

interface StatusNotificationInterface
{
    const ENTITY_ID = "entity_id";
    const ORDER_ID = "order_id";
    const MERCHANT_ID = "merchant_id";
    const MERCHANT_TRADE_NO = "merchant_trade_no";
    const RTN_CODE = "rtn_code";
    const RTN_MSG = "rtn_msg";
    const ALL_PAY_LOGISTICS_ID = "all_pay_logistics_id";
    const GOODS_AMOUNT = "goods_amount";
    const UPDATE_STATUS_DATE = "update_status_date";

    const LOGISTICS_TYPE = "logistics_type";
    const LOGISTICS_SUB_TYPE = "logistics_sub_type";
    const RECEIVER_NAME = "receiver_name";
    const RECEIVER_PHONE = "receiver_phone";
    const RECEIVER_CELL_PHONE = "receiver_cell_phone";
    const RECEIVER_EMAIL = "receiver_email";
    const RECEIVER_ADDRESS = "receiver_address";

    /**
     * @return string|null
     */
    public function getLogisticsType();

    /**
     * @return string|null
     */
    public function getLogisticsSubType();

    /**
     * @return string|null
     */
    public function getReceiverName();

    /**
     * @return string|null
     */
    public function getReceiverPhone();

    /**
     * @return string|null
     */
    public function getReceiverCellPhone();

    /**
     * @return string|null
     */
    public function getReceiverEmail();

    /**
     * @return string|null
     */
    public function getReceiverAddress();

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @return string|null
     */
    public function getMerchantId();

    /**
     * @return string|null
     */
    public function getMerchantTradeNo();

    /**
     * @return string|null
     */
    public function getRtnCode();

    /**
     * @return string|null
     */
    public function getRtnMsg();

    /**
     * @return string|null
     */
    public function getAllPayLogisticsId();

    /**
     * @return int|null
     */
    public function getGoodsAmount();

    /**
     * @return string|null
     */
    public function getUpdateStatusDate();

    /**
     * @param int $id
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setId($id);

    /**
     * @param int $orderId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setOrderId($orderId);

    /**
     * @param string $merchantId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setMerchantId($merchantId);

    /**
     * @param string $merchantTradeNo
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setMerchantTradeNo($merchantTradeNo);

    /**
     * @param string $rtnCode
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setRtnCode($rtnCode);

    /**
     * @param string $rtnMsg
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setRtnMsg($rtnMsg);

    /**
     * @param string $allPayLogisticsId
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setAllPayLogisticsId($allPayLogisticsId);

    /**
     * @param int $goodsAmount
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setGoodsAmount($goodsAmount);

    /**
     * @param string $updateStatusDate
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setUpdateStatusDate($updateStatusDate);

    /**
     * @param $logisticsType
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setLogisticsType($logisticsType);

    /**
     * @param $logisticsSubType
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setLogisticsSubType($logisticsSubType);

    /**
     * @param $receiverName
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverName($receiverName);

    /**
     * @param $receiverPhone
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverPhone($receiverPhone);

    /**
     * @param $receiverCellPhone
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverCellPhone($receiverCellPhone);

    /**
     * @param $receiverEmail
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverEmail($receiverEmail);

    /**
     * @param $receiverAddress
     * @return \Eguana\GWLogistics\Api\Data\StatusNotificationInterface
     */
    public function setReceiverAddress($receiverAddress);

}
