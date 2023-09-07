<?php
declare(strict_types=1);

namespace CJ\Middleware\Model\Product;

class CalculatePrice
{
    /**
     * @var \CJ\Middleware\Model\Data
     */
    private $orderData;

    /**
     * @param \CJ\Middleware\Model\Data $orderData
     */
    public function __construct(
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->orderData = $orderData;
    }
    /**
     * @param $orderItem
     * @param $spendingRate
     * @return mixed|void
     */
    public function calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat) {

        if ($isEnableRewardsPoint) {
            $rewardPoint = $orderItem->getData('am_spent_reward_points');
            $mileageAmountItem = $this->orderData->roundingPrice($rewardPoint / $spendingRate);
        } else {
            $mileageAmountItem = 0;
        }
        $itemSubtotal = $this->orderData->roundingPrice($orderItem->getPrice() * $orderItem->getQtyOrdered(), $isDecimalFormat);
        $itemTotalDiscount = $this->orderData->roundingPrice($orderItem->getDiscountAmount(), $isDecimalFormat) - $mileageAmountItem;
        $itemSaleAmount = $itemSubtotal - $itemTotalDiscount;
        $itemTaxAmount = $this->orderData->roundingPrice($orderItem->getTaxAmount(), $isDecimalFormat);

        $itemNetwr = $itemSubtotal - $itemTotalDiscount - $this->orderData->roundingPrice($mileageAmountItem, $isDecimalFormat) - $itemTaxAmount;
        $orderItem->setData('mileage_amount', $mileageAmountItem);
        $orderItem->setData('normal_sales_amount', $itemSubtotal);
        $orderItem->setData('discount_amount', $itemTotalDiscount);
        $orderItem->setData('sales_amount', $itemSaleAmount);
        $orderItem->setData('net_amount', $itemNetwr);
        return $orderItem;
    }
}
