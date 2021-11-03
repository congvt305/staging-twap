<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 30/9/20
 * Time: 7:57 PM
 */
namespace Eguana\CustomCatalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DisableAddToCart
 *
 * Disable add to cart when any of bundle child is out of stock
 */
class DisableAddToCart implements ArgumentInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * DisableAddToCart constructor.
     * @param \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param  LoggerInterface $logger
     */
    public function __construct(
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }

    /**
     * Disable add to cart when any of bundle child is out of stock
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function getBundleItemsStockStatus($product)
    {
        try {
            /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $optionIds = $typeInstance->getOptionsIds($product);
            $selectionCollection = $typeInstance->getSelectionsCollection($optionIds, $product);
            foreach ($selectionCollection as $item) {
                $sku = $item->getSku();
                $salable = $this->getSalableQuantityDataBySku->execute($sku);
                $salableQuantity = 0;
                foreach ($salable as $stock) {
                    $salableQuantity += $stock['qty'];
                }
                if ($salableQuantity <= 0) {
                    return true;
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return false;
    }
}
