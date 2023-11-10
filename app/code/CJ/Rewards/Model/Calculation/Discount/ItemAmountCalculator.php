<?php
declare(strict_types=1);

namespace CJ\Rewards\Model\Calculation\Discount;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Tax\Model\Config as TaxConfig;

class ItemAmountCalculator extends \Amasty\Rewards\Model\Calculation\Discount\ItemAmountCalculator
{
    /**
     * @var TaxConfig
     */
    private $taxConfig;

    public function __construct(TaxConfig $taxConfig)
    {
        $this->taxConfig = $taxConfig;
        parent::__construct($taxConfig);
    }

    /**
     * @param QuoteItem|OrderItem $item
     * @return float
     */
    public function calculateItemAmount($item): float
    {
        $itemPrice = $item->getBasePriceInclTax();
        if (!$this->taxConfig->discountTax()) {
            $itemPrice = $item->getBasePrice();
        }
        $totalPriceItem = $itemPrice * $item->getQty();
        // Correct childItem price for bundle dynamic to avoid case apply point to discount all price remains which will be calculated wrong
        if ($parentItem = $item->getParentItem()) {
            if ($parentItem->getProductType() == 'bundle'
                && $parentItem->getProduct()->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
            ) {
                $totalPriceItem *= $parentItem->getQty();
            }
        }
        $amount = $totalPriceItem - $item->getBaseDiscountAmount();

        return (float)max(0, $amount);
    }
}
