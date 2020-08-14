<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 7:41 AM
 */

namespace Eguana\GWLogistics\Api\Data;


interface QuoteCvsLocationInterface
{
    const LOCATION_ID = "location_id";
    const QUOTE_ADDRESS_ID = "quote_address_id";
    const MERCHANT_TRADE_NO = "merchant_trade_no";
    const LOGISTICS_SUB_TYPE = "logistics_sub_type";
    const CVS_STORE_ID = "cvs_store_id";
    const CVS_STORE_NAME = "cvs_store_name";
    const CVS_ADDRESS = "cvs_address";
    const CVS_TELEPHONE ="cvs_telephone";
    const CVS_OUTSIDE = "cvs_outside";
    const EXTRA_DATA = "extra_data";
    const IS_SELECTED = "is_selected";

    /**
     * @return mixed
     */
    public function getQuoteAddressId();

    /**
     * @return string
     */
    public function getMerchantTradeNo();

    /**
     * @return string
     */
    public function getLogisticsSubType();

    /**
     * @return string
     */
    public function getCvsStoreId();

    /**
     * @return string
     */
    public function getCvsStoreName();

    /**
     * @return string
     */
    public function getCvsAddress();

    /**
     * @return null|string
     */
    public function getCvsTelephone();

    /**
     * @return null|string
     */
    public function getCvsOutside();

    /**
     * @return null|string
     */
    public function getExtraData();

    /**
     * @return int
     */
    public function getIsSelected(): int;
}
