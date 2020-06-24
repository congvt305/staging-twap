<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-02
 * Time: 오전 9:19
 */

namespace Amore\Sap\Api;

/**
 * Interface SapProductDataUpdateInterface
 * @api
 */
interface SapProductManagementInterface
{
    /**
     * @param \Amore\Sap\Api\Data\SapInventoryStockInterface $stockData
     * @return \Amore\Sap\Api\Data\SapInventoryStockInterface
     * @throws \UnexpectedValueException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function inventoryStockUpdate(\Amore\Sap\Api\Data\SapInventoryStockInterface $stockData);

    /**
     * @param \Amore\Sap\Api\Data\SapProductsDetailInterface $productData
     * @return \Amore\Sap\Api\Data\SapProductsDetailInterface
     * @throws \UnexpectedValueException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function productDetailUpdate($productData);

    /**
     * @param \Amore\Sap\Api\Data\SapProductsPriceInterface $priceData
     * @return \Amore\Sap\Api\Data\SapProductsPriceInterface
     * @throws \UnexpectedValueException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function productPriceUpdate($priceData);
}
