<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 8:45 AM
 */

namespace Eguana\GWLogistics\Api\Data;


interface ReverseStatusNotificationInterface
{
    const ENTITY_ID = "entity_id";
    const RMA_ID = "rma_id";
    const MERCHANT_ID = "merchant_id";
    const RTN_MERCHANT_TRADE_NO = "rtn_merchant_trade_no";
    const RTN_CODE = "rtn_code";
    const RTN_MSG = "rtn_msg";
    const ALL_PAY_LOGISTICS_ID = "all_pay_logistics_id";
    const GOODS_AMOUNT = "goods_amount";
    const UPDATE_STATUS_DATE = "update_status_date";

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getRmaId();

    /**
     * @return string|null
     */
    public function getMerchantId();

    /**
     * @return string|null
     */
    public function getRtnMerchantTradeNo();

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
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setId($id);

    /**
     * @param int $rmaId
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setRmaId($rmaId);

    /**
     * @param string $merchantId
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setMerchantId($merchantId);

    /**
     * @param string $rtnMerchantTradeNo
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setRtnMerchantTradeNo($rtnMerchantTradeNo);

    /**
     * @param string $rtnCode
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setRtnCode($rtnCode);

    /**
     * @param string $rtnMsg
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setRtnMsg($rtnMsg);

    /**
     * @param string $allPayLogisticsId
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setAllPayLogisticsId($allPayLogisticsId);

    /**
     * @param int $goodsAmount
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setGoodsAmount($goodsAmount);

    /**
     * @param string $updateStatusDate
     * @return \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface
     */
    public function setUpdateStatusDate($updateStatusDate);

}
