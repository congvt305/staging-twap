<?php
declare(strict_types=1);

namespace Eguana\CustomCatalog\Helper;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @param \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $salableQuantityDataBySku
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $salableQuantityDataBySku,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->getSalableQuantityDataBySku = $salableQuantityDataBySku;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @throws LocalizedException
     */
    public function getSimpleProductStockStatus($product)
    {
        $result = true;
        $sku = $product->getSku();
        if (!$product->getTypeId() === 'simple') {
            throw new LocalizedException(
                __('This product is not a simple product. Product sku: %1', $sku)
            );
        }

        $salable = $this->getSalableQuantityDataBySku->execute($sku);
        $salableQuantity = 0;
        foreach ($salable as $stock) {
            $salableQuantity += $stock['qty'];
        }
        if ($salableQuantity <= 0) {
            $result = false;
        }

        return $result;
    }
     /**
     * @param float $price
     * @param float $finalPrice
     * @return int|false
     */
    public function getDiscountPrice($price, $finalPrice)
    {
        $discount =  $price - $finalPrice ;
        if ($discount > 0 && $price > 0 )
        {
            return round($discount/$price * 100);
        }
        return false;
    }

    /**
     * Get total original price for bundle with dynamic
     *
     * @param $item
     * @return int
     */
    public function getTotalOriginalPriceForBundleDynamic($item) {
        $total = 0;
        foreach ($item->getChildren() as $itemChild) {
            $total += $itemChild->getProduct()->getPrice() * $itemChild->getQty();
        }
        return $total;
    }

    public function getDiscountPriceForSapt($price, $finalPrice)
    {
        $discount =  $price - $finalPrice ;
        if ($discount > 0 && $price > 0 ) {
            return floor($discount/$price * 100);
        }
        return false;
    }
}
