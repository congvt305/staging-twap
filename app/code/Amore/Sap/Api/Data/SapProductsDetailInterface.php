<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오후 12:25
 */

namespace Amore\Sap\Api\Data;

interface SapProductsDetailInterface
{
    const SKU = 'sku';

    const NAME = 'name';

    const SIZE = 'size';

    const WEIGHT = 'weight';

    const DESCRIPTION = 'description';

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
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getSize();

    /**
     * @param string $size
     */
    public function setSize($size);

    /**
     * @return string
     */
    public function getWeight();

    /**
     * @param string $weight
     */
    public function setWeight($weight);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     */
    public function setDescription($description);

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
