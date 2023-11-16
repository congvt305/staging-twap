<?php
declare(strict_types=1);

namespace CJ\CatalogProduct\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * Class GetBundleMinQty
 */
class GetBundleMinQty
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private GetProductSalableQtyInterface $getProductSalableQty;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
    }

    /**
     * @param $product
     * @param $stockId
     * @param $selectedOptions
     * @return float|int
     */
    public function execute($product, $stockId, $selectedOptions = [])
    {
        $minQtyList = [];
        $qtyLeft = 0;

        if (empty($selectedOptions)) {
            return $qtyLeft;
        }
        $selectedSelectionIds = array_values($selectedOptions);

        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );
        foreach ($selectionCollection as $productSelection) {
            if (!in_array($productSelection->getSelectionId(), $selectedSelectionIds)) {
                continue;
            }

            try {
                $qtyLeft = $this->getProductSalableQty->execute($productSelection->getSku(), (int)$stockId);
                if (0 >= $qtyLeft) {
                    return 0;
                }
            } catch (LocalizedException $e) {
                return 0;
            }

            $minQty = $qtyLeft / (float)$productSelection->getSelectionQty();
            $minQtyList[] = floor($minQty);
        }

        return !empty($minQtyList) ? min($minQtyList) : $qtyLeft;
    }
}
