<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오후 12:11
 */

namespace Amore\Sap\Api\Data;

interface SapProductsPriceInterface
{
    const SKU = 'sku';

    const PRICE = 'price';

    const SPECIAL_PRICE = 'special_price';

    const WEBSITE_ID = 'website_id';

    const CURRENCY_CODE = 'currency_code';

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $sku
     */
    public function setSku($sku);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     */
    public function setPrice($price);

    /**
     * @return float
     */
    public function getSpecialPrice();

    /**
     * @param float $specialPrice
     */
    public function setSpecialPrice($specialPrice);

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @param int $websiteId
     */
    public function setWebsiteId($websiteId);

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode);
}
